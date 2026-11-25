<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RegionDashboardController extends Controller
{
    public function index(Request $request)
    {
        $branchId    = $request->input('branch_id');
        $agencyId    = $request->input('agency_id');
        $periodePs   = $request->input('periode_ps', now()->format('Y-m'));
        $salesSearch = trim((string) $request->input('sales_search', ''));
        $salesSort   = $request->input('sales_sort', 'total_desc');

        // 1. Ambil daftar Branches secara aman
        $branches = collect();
        if (Schema::hasTable('branches')) {
            $branchQuery = DB::table('branches');
            if (Schema::hasColumn('branches', 'is_active')) {
                $branchQuery->where('is_active', 1);
            }
            $branches = $branchQuery->orderBy('name')->get(['id', 'name']);
        }

        // 2. Ambil daftar Agencies secara aman
        $agencies = collect();
        if (Schema::hasTable('agencies')) {
            $agencyQuery = DB::table('agencies');
            if (Schema::hasColumn('agencies', 'is_active')) {
                $agencyQuery->where('is_active', 1);
            }
            $agencies = $agencyQuery->orderBy('name')->get(['id', 'name']);
        }

        // Default struktur data jika tabel customers belum ada / kosong
        $summary = [
            'total_pelanggan' => 0,
            'qc_selesai'      => 0,
            'qc_in_progress'  => 0,
            'belum_dihubungi' => 0,
        ];
        $branchProgress     = collect();
        $billingSummary     = [
            'paham'              => ['count' => 0, 'percentage' => 0],
            'perlu_diedukasi'    => ['count' => 0, 'percentage' => 0],
            'belum_dikonfirmasi' => ['count' => 0, 'percentage' => 0],
        ];
        $agencyPerformances = collect();
        $salesPerformances  = collect();

        if (!Schema::hasTable('customers')) {
            return view('dashboards.region', compact(
                'branches',
                'agencies',
                'summary',
                'branchProgress',
                'billingSummary',
                'agencyPerformances',
                'salesPerformances'
            ));
        }

        $hasQcTable    = Schema::hasTable('qc_records');
        $hasSalesTable = Schema::hasTable('sales');

        // 3. Ambil data customers sesuai filter utama
        $customerQuery = DB::table('customers');

        if ($hasQcTable) {
            $customerQuery->leftJoin('qc_records', 'qc_records.customer_id', '=', 'customers.id');
        }

        if ($branchId && Schema::hasColumn('customers', 'branch_id')) {
            $customerQuery->where('customers.branch_id', $branchId);
        }

        if ($agencyId && Schema::hasColumn('customers', 'agency_id')) {
            $customerQuery->where('customers.agency_id', $agencyId);
        }

        if ($periodePs && Schema::hasColumn('customers', 'tanggal_ps') && preg_match('/^\d{4}-\d{2}$/', $periodePs)) {
            [$year, $month] = explode('-', $periodePs);
            $customerQuery->whereYear('customers.tanggal_ps', $year)
                          ->whereMonth('customers.tanggal_ps', $month);
        }

        $selectColumns = ['customers.id'];
        if (Schema::hasColumn('customers', 'branch_id')) {
            $selectColumns[] = 'customers.branch_id';
        }
        if (Schema::hasColumn('customers', 'agency_id')) {
            $selectColumns[] = 'customers.agency_id';
        }
        if (Schema::hasColumn('customers', 'kode_sf')) {
            $selectColumns[] = 'customers.kode_sf';
        }
        if (Schema::hasColumn('customers', 'nama_sf')) {
            $selectColumns[] = 'customers.nama_sf';
        }

        if ($hasQcTable) {
            $selectColumns[] = 'qc_records.id as qc_id';
            if (Schema::hasColumn('qc_records', 'qc_status')) {
                $selectColumns[] = 'qc_records.qc_status';
            }
            if (Schema::hasColumn('qc_records', 'pemahaman_billing')) {
                $selectColumns[] = 'qc_records.pemahaman_billing';
            }
        }

        $records = $customerQuery->get($selectColumns);

        // Helper klasifikasi status QC
        $isComplete = function ($row): bool {
            $status = strtolower(trim((string) ($row->qc_status ?? '')));
            return in_array($status, ['complete', 'selesai', 'done'], true);
        };

        $isInProgress = function ($row): bool {
            $status = strtolower(trim((string) ($row->qc_status ?? '')));
            return in_array($status, ['in progress', 'in_progress', 'proses'], true);
        };

        $isPaham = function ($row): bool {
            $val = strtolower(trim((string) ($row->pemahaman_billing ?? '')));
            return $val === 'paham';
        };

        $isPerluEdukasi = function ($row): bool {
            $val = strtolower(trim((string) ($row->pemahaman_billing ?? '')));
            return in_array($val, ['perlu diedukasi', 'perlu edukasi'], true);
        };

        // 4. Hitung 4 KPI Utama
        $totalPelanggan = $records->count();
        $qcSelesai      = $records->filter($isComplete)->count();
        $qcInProgress   = $records->filter($isInProgress)->count();
        $belumDihubungi = max(0, $totalPelanggan - ($qcSelesai + $qcInProgress));

        $summary = [
            'total_pelanggan' => $totalPelanggan,
            'qc_selesai'      => $qcSelesai,
            'qc_in_progress'  => $qcInProgress,
            'belum_dihubungi' => $belumDihubungi,
        ];

        // 5. % QC Selesai per Branch
        $branchMap = $branches->pluck('name', 'id');
        $filteredBranches = $branchId
            ? $branches->where('id', (int) $branchId)
            : $branches;

        $branchProgress = $filteredBranches->map(function ($branch) use ($records, $isComplete) {
            $branchRows = $records->where('branch_id', $branch->id);
            $total      = $branchRows->count();
            $selesai    = $branchRows->filter($isComplete)->count();
            $pct        = $total > 0 ? round(($selesai / $total) * 100, 1) : 0;

            return [
                'branch_name' => $branch->name,
                'total'       => $total,
                'qc_selesai'  => $selesai,
                'percentage'  => $pct,
            ];
        })->values();

        // 6. Pemahaman Billing
        $pahamCount   = $records->filter($isPaham)->count();
        $edukasiCount = $records->filter($isPerluEdukasi)->count();
        $belumKonfirm = max(0, $totalPelanggan - ($pahamCount + $edukasiCount));

        $billingSummary = [
            'paham' => [
                'count'      => $pahamCount,
                'percentage' => $totalPelanggan > 0 ? round(($pahamCount / $totalPelanggan) * 100, 1) : 0,
            ],
            'perlu_diedukasi' => [
                'count'      => $edukasiCount,
                'percentage' => $totalPelanggan > 0 ? round(($edukasiCount / $totalPelanggan) * 100, 1) : 0,
            ],
            'belum_dikonfirmasi' => [
                'count'      => $belumKonfirm,
                'percentage' => $totalPelanggan > 0 ? round(($belumKonfirm / $totalPelanggan) * 100, 1) : 0,
            ],
        ];

        // 7. Performa Agency
        $agencyMap = $agencies->pluck('name', 'id');
        $agencyPerformances = $records
            ->filter(fn ($r) => !empty($r->agency_id))
            ->groupBy(fn ($r) => ($r->agency_id ?? 0) . '-' . ($r->branch_id ?? 0))
            ->map(function ($group) use ($agencyMap, $branchMap, $isComplete, $isPerluEdukasi) {
                $first   = $group->first();
                $total   = $group->count();
                $selesai = $group->filter($isComplete)->count();
                $edukasi = $group->filter($isPerluEdukasi)->count();

                return (object) [
                    'agency_name'    => $agencyMap[$first->agency_id] ?? 'Agency #' . $first->agency_id,
                    'branch_name'    => $branchMap[$first->branch_id] ?? '-',
                    'total'          => $total,
                    'qc_selesai_pct' => $total > 0 ? round(($selesai / $total) * 100, 1) : 0,
                    'perlu_edukasi'  => $edukasi,
                ];
            })
            ->sortByDesc('total')
            ->values();

        // 8. Performa Sales
        $salesMaster = collect();
        if ($hasSalesTable) {
            $salesMasterQuery = DB::table('sales');
            if ($branchId && Schema::hasColumn('sales', 'branch_id')) {
                $salesMasterQuery->where('branch_id', $branchId);
            }
            $salesMaster = $salesMasterQuery->get();
        }

        // Gabungkan dari master sales (jika ada) maupun dari transaksi customers
        $salesList = collect();

        if ($salesMaster->isNotEmpty()) {
            $salesList = $salesMaster->map(function ($sf) use ($records, $branchMap, $isComplete, $isPaham, $isPerluEdukasi) {
                $sfRecords = $records->filter(fn ($r) => (string) ($r->kode_sf ?? '') === (string) ($sf->kode_sf ?? ''));
                $total     = $sfRecords->count();
                $selesai   = $sfRecords->filter($isComplete)->count();

                return (object) [
                    'kode_sf'        => $sf->kode_sf ?? '-',
                    'nama_sf'        => $sf->nama_sf ?? '-',
                    'branch_name'    => $branchMap[$sf->branch_id ?? 0] ?? '-',
                    'total'          => $total,
                    'qc_selesai_pct' => $total > 0 ? round(($selesai / $total) * 100, 1) : 0,
                    'paham_billing'  => $sfRecords->filter($isPaham)->count(),
                    'perlu_edukasi'  => $sfRecords->filter($isPerluEdukasi)->count(),
                ];
            });
        } else {
            $salesList = $records
                ->filter(fn ($r) => !empty($r->kode_sf) || !empty($r->nama_sf))
                ->groupBy(fn ($r) => (string) ($r->kode_sf ?? $r->nama_sf))
                ->map(function ($group) use ($branchMap, $isComplete, $isPaham, $isPerluEdukasi) {
                    $first   = $group->first();
                    $total   = $group->count();
                    $selesai = $group->filter($isComplete)->count();

                    return (object) [
                        'kode_sf'        => $first->kode_sf ?? '-',
                        'nama_sf'        => $first->nama_sf ?? '-',
                        'branch_name'    => $branchMap[$first->branch_id ?? 0] ?? '-',
                        'total'          => $total,
                        'qc_selesai_pct' => $total > 0 ? round(($selesai / $total) * 100, 1) : 0,
                        'paham_billing'  => $group->filter($isPaham)->count(),
                        'perlu_edukasi'  => $group->filter($isPerluEdukasi)->count(),
                    ];
                })
                ->values();
        }

        if ($salesSearch !== '') {
            $keyword = strtolower($salesSearch);
            $salesList = $salesList->filter(function ($item) use ($keyword) {
                return str_contains(strtolower((string) $item->kode_sf), $keyword)
                    || str_contains(strtolower((string) $item->nama_sf), $keyword);
            });
        }

        $salesPerformances = match ($salesSort) {
            'qc_pct_desc'  => $salesList->sortByDesc('qc_selesai_pct')->values(),
            'edukasi_desc' => $salesList->sortByDesc('perlu_edukasi')->values(),
            default        => $salesList->sortByDesc('total')->values(),
        };

        return view('dashboards.region', compact(
            'branches',
            'agencies',
            'summary',
            'branchProgress',
            'billingSummary',
            'agencyPerformances',
            'salesPerformances'
        ));
    }
}