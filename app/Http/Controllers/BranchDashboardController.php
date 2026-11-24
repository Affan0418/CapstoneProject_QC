<?php

namespace App\Http\Controllers;

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

        /*
         * null berarti data belum dihubungkan.
         * Jangan diganti menjadi 0 karena 0 berarti hasil query memang nol.
         *
         * Nanti isi melalui service monitoring dengan scope branch_id
         * dari user yang login, bukan dari input browser.
         */
        $metrics = [
            [
                'label' => 'Pelanggan PS',
                'value' => null,
                'description' => 'Pelanggan baru pada periode terpilih',
                'tone' => 'blue',
            ],
            [
                'label' => 'QC Complete',
                'value' => null,
                'description' => 'Pemeriksaan sudah diselesaikan',
                'tone' => 'green',
            ],
            [
                'label' => 'Belum QC',
                'value' => null,
                'description' => 'Pelanggan belum mulai diperiksa',
                'tone' => 'amber',
            ],
            [
                'label' => 'Connected',
                'value' => null,
                'description' => 'Pelanggan berhasil dihubungi',
                'tone' => 'blue',
            ],
            [
                'label' => 'Not Connected',
                'value' => null,
                'description' => 'Pelanggan belum berhasil dihubungi',
                'tone' => 'red',
            ],
            [
                'label' => 'Penyelesaian QC',
                'value' => null,
                'description' => 'QC Complete dibanding total pelanggan PS',
                'tone' => 'navy',
            ],
        ];

        return view('dashboards.branch', [
            'branchName' => $user->branch?->name ?? 'Branch tidak ditemukan',
            'metrics' => $metrics,
        ]);
    }
}