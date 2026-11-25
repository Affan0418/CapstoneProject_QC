<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RegionAgencyController extends Controller
{
    public function index(Request $request)
    {
        $branchId  = $request->input('branch_id');
        $status    = $request->input('status', '');
        $periodePs = $request->input('periode_ps', now()->format('Y-m'));

        // 1. Ambil daftar Branch aktif untuk dropdown filter
        $branches = collect();
        if (Schema::hasTable('branches')) {
            $branchQuery = DB::table('branches');
            if (Schema::hasColumn('branches', 'is_active')) {
                $branchQuery->where('is_active', 1);
            }
            $branches = $branchQuery->orderBy('name')->get(['id', 'name']);
        }

        // 2. Ambil daftar Master Agency (SPV)
        $agencies = collect();
        if (Schema::hasTable('agencies')) {
            $agencyQuery = DB::table('agencies');
            if ($status === 'active' && Schema::hasColumn('agencies', 'is_active')) {
                $agencyQuery->where('is_active', 1);
            } elseif ($status === 'inactive' && Schema::hasColumn('agencies', 'is_active')) {
                $agencyQuery->where('is_active', 0);
            }
            $agencies = $agencyQuery->orderBy('name')->get();
        }

        // 3. Ambil data Pelanggan & QC sesuai filter Branch dan Periode PS
        $records = collect();
        if (Schema::hasTable('customers')) {
            $customerQuery = DB::table('customers');

            if (Schema::hasTable('qc_records')) {
                $customerQuery->leftJoin('qc_records', 'qc_records.customer_id', '=', 'customers.id');
            }

            if ($branchId && Schema::hasColumn('customers', 'branch_id')) {
                $customerQuery->where('customers.branch_id', $branchId);
            }

            if ($periodePs && Schema::hasColumn('customers', 'tanggal_ps') && preg_match('/^\d{4}-\d{2}$/', $periodePs)) {
                [$year, $month] = explode('-', $periodePs);
                $customerQuery->whereYear('customers.tanggal_ps', $year)
                              ->whereMonth('customers.tanggal_ps', $month);
            }

            $selectCols = ['customers.id'];
            if (Schema::hasColumn('customers', 'branch_id')) {
                $selectCols[] = 'customers.branch_id';
            }
            if (Schema::hasColumn('customers', 'agency_id')) {
                $selectCols[] = 'customers.agency_id';
            }

            if (Schema::hasTable('qc_records')) {
                if (Schema::hasColumn('qc_records', 'qc_status')) {
                    $selectCols[] = 'qc_records.qc_status';
                }
                if (Schema::hasColumn('qc_records', 'pemahaman_billing')) {
                    $selectCols[] = 'qc_records.pemahaman_billing';
                }
            }

            $records = $customerQuery->get($selectCols);
        }

        $branchMap = $branches->pluck('name', 'id');

        // Helper klasifikasi status QC & Pemahaman Billing
        $isComplete = function ($row): bool {
            $val = strtolower(trim((string) ($row->qc_status ?? '')));
            return in_array($val, ['complete', 'selesai', 'done'], true);
        };

        $isProgress = function ($row): bool {
            $val = strtolower(trim((string) ($row->qc_status ?? '')));
            return in_array($val, ['in progress', 'in_progress', 'proses'], true);
        };

        $isPaham = function ($row): bool {
            $val = strtolower(trim((string) ($row->pemahaman_billing ?? '')));
            return $val === 'paham';
        };

        $isEdukasi = function ($row): bool {
            $val = strtolower(trim((string) ($row->pemahaman_billing ?? '')));
            return in_array($val, ['perlu diedukasi', 'perlu edukasi'], true);
        };

        // 4. Susun rincian kinerja per Agency
        $agencyList = $agencies->map(function ($ag) use ($records, $branchMap, $isComplete, $isProgress, $isPaham, $isEdukasi) {
            $agRows   = $records->where('agency_id', $ag->id);
            $total    = $agRows->count();
            $selesai  = $agRows->filter($isComplete)->count();
            $progress = $agRows->filter($isProgress)->count();
            $belum    = max(0, $total - ($selesai + $progress));
            $paham    = $agRows->filter($isPaham)->count();
            $edukasi  = $agRows->filter($isEdukasi)->count();

            $branchNames = $agRows->pluck('branch_id')
                ->filter()
                ->unique()
                ->map(fn ($bId) => $branchMap[$bId] ?? null)
                ->filter()
                ->implode(', ');

            return (object) [
                'id'              => $ag->id,
                'name'            => $ag->name ?? '-',
                'pic_name'        => $ag->pic_name ?? '-',
                'phone'           => $ag->phone ?? '-',
                'is_active'       => (bool) ($ag->is_active ?? true),
                'branch_names'    => $branchNames !== '' ? $branchNames : 'Belum ada data PS',
                'total_pelanggan' => $total,
                'qc_selesai'      => $selesai,
                'qc_selesai_pct'  => $total > 0 ? round(($selesai / $total) * 100, 1) : 0,
                'qc_in_progress'  => $progress,
                'belum_dihubungi' => $belum,
                'paham_billing'   => $paham,
                'perlu_edukasi'   => $edukasi,
            ];
        });

        // Jika filter branch dipilih, saring agency yang memiliki pelanggan di branch tersebut
        if ($branchId) {
            $agencyList = $agencyList->filter(fn ($item) => $item->total_pelanggan > 0)->values();
        }

        // 5. Hitung 4 Kartu Ringkasan KPI Agency
        $topAgency = $agencyList->sortByDesc('qc_selesai_pct')->first();

        $agencySummary = [
            'total_agency'    => $agencyList->count(),
            'agency_aktif'    => $agencyList->where('is_active', true)->count(),
            'avg_qc_pct'      => $agencyList->count() > 0 ? round($agencyList->avg('qc_selesai_pct'), 1) : 0,
            'top_agency_name' => ($topAgency && $topAgency->total_pelanggan > 0) ? $topAgency->name : '-',
            'top_agency_pct'  => $topAgency ? $topAgency->qc_selesai_pct : 0,
            'total_edukasi'   => $agencyList->sum('perlu_edukasi'),
        ];

        return view('region.agencies.index', compact(
            'branches',
            'agencyList',
            'agencySummary'
        ));
    }
}