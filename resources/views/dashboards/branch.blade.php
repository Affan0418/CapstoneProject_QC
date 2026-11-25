@extends('layouts.branch')

@section('title', 'Dashboard Branch')
@section('breadcrumb', 'Dashboard')

@section('content')
@php
$quickActions = [
[
'title' => 'Tambah Pelanggan',
'description' => 'Input pelanggan baru secara manual.',
'route' => 'branch.customers.create',
'primary' => true,
],
[
'title' => 'Import Excel',
'description' => 'Unggah data pelanggan dari file Excel.',
'route' => 'branch.customers.import',
'primary' => false,
],
[
'title' => 'Export Excel',
'description' => 'Unduh data sesuai kewenangan cabang.',
'route' => 'branch.customers.export',
'primary' => false,
],
[
'title' => 'Data Pelanggan',
'description' => 'Cari dan kelola pelanggan cabang Anda.',
'route' => 'branch.customers.index',
'primary' => false,
],
];
@endphp

<section class="page-heading dashboard-welcome">
    <div class="welcome-copy">
        <span class="eyebrow">DASHBOARD BRANCH</span>

        <h1>Selamat datang, {{ auth()->user()->name }}</h1>

        <p class="muted">
            Kelola pelanggan dan pantau progres QC cabang Anda.
        </p>
    </div>

    <div class="branch-chip">
        <span class="status-dot" aria-hidden="true"></span>
        {{ $branchName }}
    </div>
</section>

<div class="notice notice-info" role="status">
    <span class="notice-title">Informasi dashboard</span>
    <span>
        Form input pelanggan sudah tersedia. Ringkasan statistik,
        monitoring, dan tabel dashboard belum terhubung ke data.
        Tanda — berarti nilai belum tersedia, bukan jumlah nol.
    </span>
</div>

<section aria-labelledby="summary-title">
    <div class="section-heading">
        <div>
            <h2 id="summary-title">Ringkasan cabang</h2>
            <p class="muted small">
                Area ringkasan monitoring pelanggan dan pemeriksaan QC.
            </p>
        </div>

        <span class="badge badge-neutral">Menunggu integrasi data</span>
    </div>

    <div class="kpi-grid">
        @foreach($metrics as $metric)
        <article class="kpi-card">
            <div class="kpi-label">
                <span class="metric-marker marker-{{ $metric['tone'] }}"
                    aria-hidden="true"></span>

                {{ $metric['label'] }}
            </div>

            <div class="kpi-value">
                {{ $metric['value'] ?? '—' }}
            </div>

            <p class="muted small">{{ $metric['description'] }}</p>
        </article>
        @endforeach
    </div>
</section>

<section class="panel" aria-labelledby="actions-title">
    <div class="panel-heading">
        <div>
            <h2 id="actions-title">Pengelolaan pelanggan</h2>
            <p class="muted small">
                Akses utama untuk pekerjaan administrasi cabang.
            </p>
        </div>
    </div>

    <div class="action-grid">
        @foreach($quickActions as $action)
        <article class="action-card">
            <h3>{{ $action['title'] }}</h3>
            <p class="muted small">{{ $action['description'] }}</p>

            @if(\Illuminate\Support\Facades\Route::has($action['route']))
            <a
                href="{{ route($action['route']) }}"
                class="btn {{ $action['primary'] ? 'btn-primary' : 'btn-outline' }}">
                Buka {{ $action['title'] }}
            </a>
            @else
            <button
                type="button"
                class="btn btn-outline"
                disabled>
                Belum terhubung
            </button>
            @endif
        </article>
        @endforeach
    </div>
</section>

<div class="dashboard-columns">
    <section class="panel" aria-labelledby="progress-title">
        <div class="panel-heading">
            <div>
                <h2 id="progress-title">Progres pemeriksaan QC</h2>
                <p class="muted small">
                    Ringkasan penyelesaian pemeriksaan pelanggan.
                </p>
            </div>
        </div>

        <div class="empty-state">
            <div class="empty-symbol" aria-hidden="true">QC</div>
            <h3>Monitoring belum dihubungkan</h3>
            <p>
                Grafik dan persentase akan ditampilkan setelah
                data pelanggan serta hasil QC tersedia.
            </p>
        </div>
    </section>

    <section class="panel" aria-labelledby="agency-title">
        <div class="panel-heading">
            <div>
                <h2 id="agency-title">Monitoring Agency</h2>
                <p class="muted small">
                    Performa Agency yang menangani pelanggan cabang.
                </p>
            </div>
        </div>

        <div class="empty-state">
            <div class="empty-symbol" aria-hidden="true">AG</div>
            <h3>Belum ada data untuk ditampilkan</h3>
            <p>
                Ringkasan Agency akan mengikuti pemetaan
                dan data pelanggan Branch Anda.
            </p>
        </div>
    </section>
</div>

<section class="panel" aria-labelledby="customers-title">
    <div class="panel-heading">
        <div>
            <h2 id="customers-title">Pelanggan terbaru</h2>
            <p class="muted small">
                Area integrasi daftar pelanggan yang baru ditambahkan.
            </p>
        </div>

        @if(\Illuminate\Support\Facades\Route::has('branch.customers.index'))
        <a
            href="{{ route('branch.customers.index') }}"
            class="btn btn-outline">
            Lihat seluruh pelanggan
        </a>
        @endif
    </div>

    <div class="table-responsive">
        <table>
            <caption class="sr-only">
                Daftar pelanggan terbaru pada cabang pengguna.
            </caption>

            <thead>
                <tr>
                    <th scope="col">Pelanggan / Service ID</th>
                    <th scope="col">Tanggal PS</th>
                    <th scope="col">Agency</th>
                    <th scope="col">Sales</th>
                    <th scope="col">Status QC</th>
                    <th scope="col">Aksi</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td colspan="6">
                        <div class="empty-state compact">
                            <h3>Daftar pelanggan belum dihubungkan</h3>
                            <p>
                                Data dari modul input dan import Excel
                                nantinya ditampilkan di bagian ini.
                            </p>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</section>

<section class="panel" aria-labelledby="sales-title">
    <div class="panel-heading">
        <div>
            <h2 id="sales-title">Performa Sales</h2>
            <p class="muted small">
                Perbandingan progres QC berdasarkan Sales dalam cabang.
            </p>
        </div>

        <span class="badge badge-neutral">Menunggu integrasi data</span>
    </div>

    <div class="table-responsive">
        <table>
            <caption class="sr-only">
                Ringkasan performa QC Sales pada cabang pengguna.
            </caption>

            <thead>
                <tr>
                    <th scope="col">Kode Sales</th>
                    <th scope="col">Nama Sales</th>
                    <th scope="col">Pelanggan PS</th>
                    <th scope="col">QC Complete</th>
                    <th scope="col">Penyelesaian</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td colspan="5">
                        <div class="empty-state compact">
                            <h3>Performa Sales belum tersedia</h3>
                            <p>
                                Perhitungan akan menggunakan pelanggan
                                dan hasil QC sebenarnya.
                            </p>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</section>
@endsection