<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\Customer;
use App\Models\QcRecord;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class BranchDashboardController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User|null $user */
        $user = $request->user();

        abort_unless(
            $user && $user->role === User::ROLE_BRANCH,
            403,
            'Anda tidak memiliki akses ke dashboard Branch.'
        );

        abort_unless(
            $user->branch_id !== null,
            403,
            'Akun Anda belum dipetakan ke Branch. Hubungi pengelola akun.'
        );

        $branchId = $user->branch_id;

        // 1. Hitung Metrik KPI Utama (Scoped ke branch_id user)
        $totalPelanggan = Customer::where('branch_id', $branchId)->count();

        $qcComplete = QcRecord::whereHas('customer', function ($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        })->where('qc_status', 'complete')->count();

        // Pelanggan yang belum punya qc_record ATAU belum selesai dan status call masih belum_dihubungi
        $belumQc = Customer::where('branch_id', $branchId)
            ->where(function ($query) {
                $query->doesntHave('qcRecord')
                    ->orWhereHas('qcRecord', function ($q) {
                        $q->where('qc_status', '!=', 'complete')
                            ->where('final_call_status', 'belum_dihubungi');
                    });
            })
            ->count();

        $connected = QcRecord::whereHas('customer', function ($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        })->where('final_call_status', 'terhubung')->count();

        $notConnected = QcRecord::whereHas('customer', function ($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        })->where('final_call_status', 'gagal_dihubungi')->count();

        $inProgressQc = max(0, $totalPelanggan - $qcComplete - $belumQc);

        $completionRate = $totalPelanggan > 0
            ? round(($qcComplete / $totalPelanggan) * 100, 1)
            : 0;

        $metrics = [
            [
                'label' => 'Pelanggan PS',
                'value' => $totalPelanggan,
                'description' => 'Pelanggan baru pada periode terpilih',
                'tone' => 'blue',
            ],
            [
                'label' => 'QC Complete',
                'value' => $qcComplete,
                'description' => 'Pemeriksaan sudah diselesaikan',
                'tone' => 'green',
            ],
            [
                'label' => 'Belum QC',
                'value' => $belumQc,
                'description' => 'Pelanggan belum mulai diperiksa',
                'tone' => 'amber',
            ],
            [
                'label' => 'Connected',
                'value' => $connected,
                'description' => 'Pelanggan berhasil dihubungi',
                'tone' => 'blue',
            ],
            [
                'label' => 'Not Connected',
                'value' => $notConnected,
                'description' => 'Pelanggan belum berhasil dihubungi',
                'tone' => 'red',
            ],
            [
                'label' => 'Penyelesaian QC',
                'value' => $totalPelanggan > 0 ? $completionRate . '%' : '0%',
                'description' => 'QC Complete dibanding total pelanggan PS',
                'tone' => 'navy',
            ],
        ];

        // 2. Data Progres Pemeriksaan QC
        $qcProgress = [
            'total' => $totalPelanggan,
            'complete' => $qcComplete,
            'in_progress' => $inProgressQc,
            'belum_qc' => $belumQc,
            'rate' => $completionRate,
        ];

        // 3. Monitoring Agency (Agency yang memiliki pelanggan di branch ini)
        $agencyStats = Agency::whereHas('customers', function ($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        })
            ->withCount([
                'customers as total_pelanggan' => fn ($q) => $q->where('branch_id', $branchId),
                'customers as qc_complete' => fn ($q) => $q->where('branch_id', $branchId)
                    ->whereHas('qcRecord', fn ($qc) => $qc->where('qc_status', 'complete')),
            ])
            ->orderByDesc('total_pelanggan')
            ->get();

        // 4. Pelanggan Terbaru (10 data terakhir di branch ini)
        $recentCustomers = Customer::with(['agency', 'qcRecord'])
            ->where('branch_id', $branchId)
            ->latest()
            ->limit(10)
            ->get();

        // 5. Performa Sales (Semua sales di branch ini beserta capaian QC)
        $salesPerformance = Sale::where('branch_id', $branchId)
            ->withCount([
                'customers as total_pelanggan' => fn ($q) => $q->where('branch_id', $branchId),
                'customers as qc_complete' => fn ($q) => $q->where('branch_id', $branchId)
                    ->whereHas('qcRecord', fn ($qc) => $qc->where('qc_status', 'complete')),
            ])
            ->orderByDesc('total_pelanggan')
            ->get();

        return view('dashboards.branch', [
            'branchName' => $user->branch?->name ?? 'Branch tidak ditemukan',
            'metrics' => $metrics,
            'qcProgress' => $qcProgress,
            'agencyStats' => $agencyStats,
            'recentCustomers' => $recentCustomers,
            'salesPerformance' => $salesPerformance,
        ]);
    }
}