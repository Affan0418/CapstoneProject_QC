<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RegionReportController extends Controller
{
    public function index(Request $request)
    {
        $branchId  = $request->input('branch_id');
        $agencyId  = $request->input('agency_id');
        $qcStatus  = $request->input('qc_status', '');
        $billing   = $request->input('pemahaman_billing', '');
        $periodePs = $request->input('periode_ps', now()->format('Y-m'));

        // 1. Dropdown Master Branch & Agency
        $branches = collect();
        if (Schema::hasTable('branches')) {
            $bQuery = DB::table('branches');
            if (Schema::hasColumn('branches', 'is_active')) {
                $bQuery->where('is_active', 1);
            }
            $branches = $bQuery->orderBy('name')->get(['id', 'name']);
        }

        $agencies = collect();
        if (Schema::hasTable('agencies')) {
            $aQuery = DB::table('agencies');
            if (Schema::hasColumn('agencies', 'is_active')) {
                $aQuery->where('is_active', 1);
            }
            $agencies = $aQuery->orderBy('name')->get(['id', 'name']);
        }

        // 2. Bangun Query Dasar Laporan
        $baseQuery = $this->buildFilteredQuery($request);

        // 3. Hitung 4 Ringkasan KPI dari seluruh data terfilter
        $allRecords = $baseQuery ? (clone $baseQuery)->get() : collect();

        $totalData    = $allRecords->count();
        $totalSelesai = $allRecords->filter(fn ($r) => in_array(strtolower(trim((string) ($r->qc_status ?? ''))), ['complete', 'selesai', 'done'], true))->count();
        $totalPaham   = $allRecords->filter(fn ($r) => strtolower(trim((string) ($r->pemahaman_billing ?? ''))) === 'paham')->count();
        $totalEdukasi = $allRecords->filter(fn ($r) => in_array(strtolower(trim((string) ($r->pemahaman_billing ?? ''))), ['perlu diedukasi', 'perlu edukasi'], true))->count();

        $reportSummary = [
            'total_data'      => $totalData,
            'total_selesai'   => $totalSelesai,
            'pct_selesai'     => $totalData > 0 ? round(($totalSelesai / $totalData) * 100, 1) : 0,
            'total_paham'     => $totalPaham,
            'pct_paham'       => $totalData > 0 ? round(($totalPaham / $totalData) * 100, 1) : 0,
            'total_edukasi'   => $totalEdukasi,
        ];

        // 4. Paginasi Tabel Laporan (15 baris per halaman)
        $reportRows = $baseQuery
            ? $baseQuery->orderByDesc('customers.id')->paginate(15)->withQueryString()
            : collect();

        return view('region.reports.index', compact(
            'branches',
            'agencies',
            'reportSummary',
            'reportRows'
        ));
    }

    public function exportCsv(Request $request)
    {
        $query   = $this->buildFilteredQuery($request);
        $records = $query ? $query->orderByDesc('customers.id')->get() : collect();

        $periodePs = $request->input('periode_ps', now()->format('Y-m'));
        $filename  = "Laporan_QC_Region_Sumbagsel_{$periodePs}.csv";

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($records) {
            $file = fopen('php://output', 'w');
            // Tambahkan BOM UTF-8 agar rapi saat dibuka di Microsoft Excel
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, [
                'No',
                'ID Pelanggan / Nomor Internet',
                'Nama Pelanggan',
                'No. Telepon',
                'Branch',
                'Agency (SPV)',
                'Kode SF',
                'Nama SF',
                'Tanggal PS',
                'Status QC',
                'Pemahaman Billing',
                'Catatan QC',
            ], ';');

            foreach ($records as $index => $row) {
                fputcsv($file, [
                    $index + 1,
                    $row->internet_number ?? $row->customer_code ?? '-',
                    $row->customer_name ?? '-',
                    $row->customer_phone ?? '-',
                    $row->branch_name ?? '-',
                    $row->agency_name ?? '-',
                    $row->kode_sf ?? '-',
                    $row->nama_sf ?? '-',
                    $row->tanggal_ps ?? '-',
                    $row->qc_status ?? 'Belum Dihubungi',
                    $row->pemahaman_billing ?? 'Belum Dikonfirmasi',
                    $row->notes ?? $row->catatan ?? '-',
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function buildFilteredQuery(Request $request)
    {
        if (!Schema::hasTable('customers')) {
            return null;
        }

        $branchId  = $request->input('branch_id');
        $agencyId  = $request->input('agency_id');
        $qcStatus  = $request->input('qc_status', '');
        $billing   = $request->input('pemahaman_billing', '');
        $periodePs = $request->input('periode_ps', now()->format('Y-m'));

        $query = DB::table('customers');

        if (Schema::hasTable('branches') && Schema::hasColumn('customers', 'branch_id')) {
            $query->leftJoin('branches', 'branches.id', '=', 'customers.branch_id');
        }
        if (Schema::hasTable('agencies') && Schema::hasColumn('customers', 'agency_id')) {
            $query->leftJoin('agencies', 'agencies.id', '=', 'customers.agency_id');
        }
        if (Schema::hasTable('qc_records')) {
            $query->leftJoin('qc_records', 'qc_records.customer_id', '=', 'customers.id');
        }

        // Filter Branch & Agency
        if ($branchId && Schema::hasColumn('customers', 'branch_id')) {
            $query->where('customers.branch_id', $branchId);
        }
        if ($agencyId && Schema::hasColumn('customers', 'agency_id')) {
            $query->where('customers.agency_id', $agencyId);
        }

        // Filter Periode PS
        if ($periodePs && Schema::hasColumn('customers', 'tanggal_ps') && preg_match('/^\d{4}-\d{2}$/', $periodePs)) {
            [$year, $month] = explode('-', $periodePs);
            $query->whereYear('customers.tanggal_ps', $year)
                  ->whereMonth('customers.tanggal_ps', $month);
        }

        // Filter Status QC
        if ($qcStatus !== '' && Schema::hasTable('qc_records') && Schema::hasColumn('qc_records', 'qc_status')) {
            if ($qcStatus === 'selesai') {
                $query->whereIn(DB::raw('LOWER(qc_records.qc_status)'), ['complete', 'selesai', 'done']);
            } elseif ($qcStatus === 'progress') {
                $query->whereIn(DB::raw('LOWER(qc_records.qc_status)'), ['in progress', 'in_progress', 'proses']);
            } elseif ($qcStatus === 'belum') {
                $query->where(function ($q) {
                    $q->whereNull('qc_records.qc_status')
                      ->orWhereNotIn(DB::raw('LOWER(qc_records.qc_status)'), ['complete', 'selesai', 'done', 'in progress', 'in_progress', 'proses']);
                });
            }
        }

        // Filter Pemahaman Billing
        if ($billing !== '' && Schema::hasTable('qc_records') && Schema::hasColumn('qc_records', 'pemahaman_billing')) {
            if ($billing === 'paham') {
                $query->whereRaw('LOWER(qc_records.pemahaman_billing) = ?', ['paham']);
            } elseif ($billing === 'edukasi') {
                $query->whereIn(DB::raw('LOWER(qc_records.pemahaman_billing)'), ['perlu diedukasi', 'perlu edukasi']);
            }
        }

        // Susun kolom select secara defensif sesuai skema kolom yang tersedia
        $selects = ['customers.id'];
        foreach (['name as customer_name', 'phone as customer_phone', 'internet_number', 'customer_code', 'kode_sf', 'nama_sf', 'tanggal_ps'] as $colSpec) {
            $rawCol = explode(' ', $colSpec)[0];
            if (Schema::hasColumn('customers', $rawCol)) {
                $selects[] = "customers.{$colSpec}";
            }
        }

        if (Schema::hasTable('branches')) {
            $selects[] = 'branches.name as branch_name';
        }
        if (Schema::hasTable('agencies')) {
            $selects[] = 'agencies.name as agency_name';
        }
        if (Schema::hasTable('qc_records')) {
            foreach (['qc_status', 'pemahaman_billing', 'notes', 'catatan'] as $qCol) {
                if (Schema::hasColumn('qc_records', $qCol)) {
                    $selects[] = "qc_records.{$qCol}";
                }
            }
        }

        return $query->select($selects);
    }
}