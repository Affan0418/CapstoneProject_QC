@extends('layouts.branch')

@section('title', 'Dashboard Branch')
@section('breadcrumb', 'Dashboard')

@section('content')
@php
$hasData = ($qcProgress['total'] ?? 0) > 0;

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

{{-- HEADER --}}
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

{{-- NOTIFIKASI --}}
@if(session('success'))
<div class="notice notice-info" role="status">
    <span class="notice-title">Berhasil</span>
    <span>{{ session('success') }}</span>
</div>
@endif

@if(!$hasData)
<div class="notice notice-info" role="status">
    <span class="notice-title">Belum ada data pelanggan</span>
    <span>
        Silakan tambahkan data pelanggan melalui menu Tambah Pelanggan
        untuk mulai memantau statistik QC cabang Anda.
    </span>
</div>
@endif

{{-- RINGKASAN KPI --}}
<section aria-labelledby="summary-title">
    <div class="section-heading">
        <div>
            <h2 id="summary-title">Ringkasan cabang</h2>
            <p class="muted small">
                Area ringkasan monitoring pelanggan dan pemeriksaan QC.
            </p>
        </div>
    </div>

    <div class="kpi-grid">
        @foreach($metrics as $metric)
        <article class="kpi-card">
            <div class="kpi-label">
                <span
                    class="metric-marker marker-{{ $metric['tone'] }}"
                    aria-hidden="true"></span>

                {{ $metric['label'] }}
            </div>

            <div class="kpi-value">
                {{ $metric['value'] ?? '—' }}
            </div>

            <p class="muted small">
                {{ $metric['description'] }}
            </p>

            <span class="badge badge-neutral">
                {{ $hasData ? 'Data terintegrasi' : 'Menunggu data pelanggan' }}
            </span>
        </article>
        @endforeach
    </div>
</section>

{{-- AKSI PELANGGAN --}}
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
            <p class="muted small">
                {{ $action['description'] }}
            </p>

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

{{-- PROGRES QC DAN MONITORING AGENCY --}}
<div class="dashboard-columns">
    <section class="panel" aria-labelledby="progress-title">
        <div class="panel-heading">
            <div>
                <h2 id="progress-title">Progres pemeriksaan QC</h2>
                <p class="muted small">
                    Ringkasan penyelesaian pemeriksaan pelanggan.
                </p>
            </div>

            @if($hasData)
            <span class="badge badge-neutral">
                {{ $qcProgress['rate'] }}% Selesai
            </span>
            @endif
        </div>

        @if($hasData)
        <div class="table-responsive">
            <table>
                <caption class="sr-only">
                    Ringkasan progres pemeriksaan QC cabang.
                </caption>

                <tbody>
                    <tr>
                        <th scope="row">Tingkat penyelesaian QC</th>
                        <td>
                            <strong>
                                {{ $qcProgress['complete'] }}
                                / {{ $qcProgress['total'] }} Pelanggan
                            </strong>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">QC Complete</th>
                        <td>{{ $qcProgress['complete'] }}</td>
                    </tr>
                    <tr>
                        <th scope="row">Sedang Diproses (In Progress)</th>
                        <td>{{ $qcProgress['in_progress'] }}</td>
                    </tr>
                    <tr>
                        <th scope="row">Belum QC</th>
                        <td>{{ $qcProgress['belum_qc'] }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        @else
        <div class="empty-state">
            <div class="empty-symbol" aria-hidden="true">QC</div>
            <h3>Belum ada data pemeriksaan</h3>
            <p>
                Ringkasan pemeriksaan akan ditampilkan
                setelah data pelanggan tersedia.
            </p>
        </div>
        @endif
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

        @if($agencyStats->isNotEmpty())
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th scope="col">Nama Agency</th>
                        <th scope="col">Pelanggan PS</th>
                        <th scope="col">QC Complete</th>
                        <th scope="col">Penyelesaian</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($agencyStats as $agency)
                    @php
                    $agencyRate = $agency->total_pelanggan > 0
                    ? round(
                    ($agency->qc_complete / $agency->total_pelanggan) * 100,
                    1
                    )
                    : 0;
                    @endphp

                    <tr>
                        <td><strong>{{ $agency->name }}</strong></td>
                        <td>{{ $agency->total_pelanggan }}</td>
                        <td>{{ $agency->qc_complete }}</td>
                        <td>{{ $agencyRate }}%</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="empty-state">
            <div class="empty-symbol" aria-hidden="true">AG</div>
            <h3>Belum ada data untuk ditampilkan</h3>
            <p>
                Ringkasan Agency akan mengikuti pemetaan
                dan data pelanggan Branch Anda.
            </p>
        </div>
        @endif
    </section>
</div>

{{-- PELANGGAN TERBARU --}}
<section class="panel" aria-labelledby="customers-title">
    <div class="panel-heading">
        <div>
            <h2 id="customers-title">Pelanggan terbaru</h2>
            <p class="muted small">
                Daftar pelanggan yang baru ditambahkan pada cabang Anda.
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
                @forelse($recentCustomers as $customer)
                @php
                $qcStatus = $customer->qcRecord?->qc_status;
                $callStatus = $customer->qcRecord?->final_call_status;

                if ($qcStatus === 'complete') {
                $statusLabel = 'QC Complete';
                } elseif ($callStatus && $callStatus !== 'belum_dihubungi') {
                $statusLabel = 'In Progress';
                } else {
                $statusLabel = 'Belum QC';
                }
                @endphp

                <tr>
                    <td>
                        <strong>{{ $customer->nama_pelanggan }}</strong><br>
                        <span class="muted small">
                            ID: {{ $customer->service_id }}
                        </span>
                    </td>

                    <td>
                        {{ $customer->tanggal_ps ? $customer->tanggal_ps->format('d M Y') : '—' }}
                    </td>

                    <td>{{ $customer->agency?->name ?? '—' }}</td>

                    <td>
                        @if($customer->nama_sf)
                        {{ $customer->nama_sf }}
                        <span class="muted small">
                            ({{ $customer->kode_sf }})
                        </span>
                        @else
                        {{ $customer->kode_sf ?? '—' }}
                        @endif
                    </td>

                    <td>
                        <span class="badge badge-neutral">
                            {{ $statusLabel }}
                        </span>
                    </td>

                    <td>
                        @if(\Illuminate\Support\Facades\Route::has('branch.customers.show'))
                        <a
                            href="{{ route('branch.customers.show', $customer) }}"
                            class="btn btn-outline">
                            Detail
                        </a>
                        @else
                        <span class="muted small">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state compact">
                            <h3>Belum ada data pelanggan</h3>
                            <p>
                                Data pelanggan akan ditampilkan
                                setelah pelanggan ditambahkan.
                            </p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

{{-- PERFORMA SALES --}}
<section class="panel" aria-labelledby="sales-title">
    <div class="panel-heading">
        <div>
            <h2 id="sales-title">Performa Sales</h2>
            <p class="muted small">
                Perbandingan progres QC berdasarkan Sales dalam cabang.
            </p>
        </div>

        <span class="badge badge-neutral">
            {{ $salesPerformance->isNotEmpty()
                    ? $salesPerformance->count() . ' Sales ditampilkan'
                    : 'Belum ada data Sales' }}
        </span>
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
                @forelse($salesPerformance as $sale)
                @php
                $salesRate = $sale->total_pelanggan > 0
                ? round(
                ($sale->qc_complete / $sale->total_pelanggan) * 100,
                1
                )
                : 0;
                @endphp

                <tr>
                    <td><strong>{{ $sale->kode_sf }}</strong></td>
                    <td>{{ $sale->nama_sf }}</td>
                    <td>{{ $sale->total_pelanggan }}</td>
                    <td>{{ $sale->qc_complete }}</td>
                    <td>{{ $salesRate }}%</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">
                        <div class="empty-state compact">
                            <h3>Performa Sales belum tersedia</h3>
                            <p>
                                Ringkasan akan ditampilkan setelah
                                data pelanggan dengan informasi Sales tersedia.
                            </p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection