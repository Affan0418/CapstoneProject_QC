@extends('layouts.branch')

@section('title', 'Dashboard Branch')
@section('breadcrumb', 'Dashboard')

@section('content')
@php
/*
* Variabel mengikuti kontrak dari kode backend
* yang sebelumnya Anda kirim.
*/
$qcSummary = $qcProgress ?? null;

$hasQcSummary = is_array($qcSummary)
&& isset(
$qcSummary['total'],
$qcSummary['complete'],
$qcSummary['rate']
)
&& is_numeric($qcSummary['total'])
&& is_numeric($qcSummary['complete'])
&& is_numeric($qcSummary['rate']);

$qcRate = $hasQcSummary
? max(0, min(100, (float) $qcSummary['rate']))
: null;

$agencyRows = $agencyStats ?? null;
$customerRows = $recentCustomers ?? null;
$salesRows = $salesPerformance ?? null;

$quickActions = [
[
'title' => 'Tambah Pelanggan',
'description' => 'Input pelanggan baru secara manual.',
'route' => 'branch.customers.create',
'primary' => true,
'icon' => 'customer',
],
[
'title' => 'Import Excel',
'description' => 'Unggah data pelanggan dari file Excel.',
'route' => 'branch.customers.import',
'primary' => false,
'icon' => 'import',
],
[
'title' => 'Export Excel',
'description' => 'Unduh data sesuai kewenangan cabang.',
'route' => 'branch.customers.export',
'primary' => false,
'icon' => 'export',
],
[
'title' => 'Data Pelanggan',
'description' => 'Cari dan kelola pelanggan cabang Anda.',
'route' => 'branch.customers.index',
'primary' => false,
'icon' => 'table',
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
        {{ $branchName ?? 'Cabang belum tersedia' }}
    </div>
</section>

{{-- NOTIFIKASI --}}
@if(session('success'))
<div class="notice notice-info" role="status">
    <span class="notice-title">Berhasil</span>
    <span>{{ session('success') }}</span>
</div>
@endif

@if(!$hasQcSummary)
<div class="notice notice-info" role="status">
    <span class="notice-title">Informasi dashboard</span>
    <span>
        Ringkasan progres QC belum tersedia dari backend.
        Tanda — menunjukkan nilai yang belum tersedia, bukan jumlah nol.
    </span>
</div>
@endif

{{-- RINGKASAN KPI --}}
<section aria-labelledby="summary-title">
    <div class="section-heading">
        <div>
            <h2 id="summary-title">Ringkasan cabang</h2>

            <p class="muted small">
                Ringkasan monitoring pelanggan dan pemeriksaan QC.
            </p>
        </div>

        <span class="badge badge-neutral">
            {{ $branchName ?? 'Dashboard cabang' }}
        </span>
    </div>

    <div class="kpi-grid">
        @foreach(($metrics ?? []) as $metric)
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
        </article>
        @endforeach
    </div>
</section>

{{-- PENGELOLAAN PELANGGAN --}}
<section class="panel" aria-labelledby="actions-title">
    <div class="panel-heading">
        <div>
            <h2 id="actions-title" class="dashboard-section-title">
                <span
                    class="dashboard-section-icon dashboard-section-icon--customers"
                    aria-hidden="true">
                    <svg
                        width="18"
                        height="18"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.7"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        focusable="false">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                    </svg>
                </span>

                <span>Pengelolaan pelanggan</span>
            </h2>

            <p class="muted small">
                Akses utama untuk pekerjaan administrasi cabang.
            </p>
        </div>
    </div>

    <div class="action-grid">
        @foreach($quickActions as $action)
        <article class="action-card qc-action-card">
            <span
                class="qc-action-icon {{ $action['primary'] ? 'qc-action-icon--primary' : '' }}"
                aria-hidden="true">
                <svg
                    width="21"
                    height="21"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.7"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    focusable="false">
                    @switch($action['icon'])
                    @case('customer')
                    <circle cx="9" cy="7" r="4" />
                    <path d="M2 21v-2a6 6 0 0 1 6-6h2a6 6 0 0 1 5 2.7" />
                    <path d="M19 14v8M15 18h8" />
                    @break

                    @case('import')
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                    <path d="M14 2v6h6M12 11v7m-3-3 3 3 3-3" />
                    @break

                    @case('export')
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                    <path d="M14 2v6h6M12 18v-7m-3 3 3-3 3 3" />
                    @break

                    @default
                    <rect x="3" y="3" width="18" height="18" rx="3" />
                    <path d="M3 9h18M9 9v12M3 15h18" />
                    @endswitch
                </svg>
            </span>

            <h3>{{ $action['title'] }}</h3>

            <p class="muted small">
                {{ $action['description'] }}
            </p>

            @if(\Illuminate\Support\Facades\Route::has($action['route']))
            <a
                href="{{ route($action['route']) }}"
                class="btn {{ $action['primary'] ? 'btn-primary' : 'btn-outline' }}">
                <span>Buka {{ $action['title'] }}</span>
                <span class="qc-action-arrow" aria-hidden="true">→</span>
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

{{-- PROGRES QC DAN AGENCY --}}
<div class="dashboard-columns">
    <section class="panel" aria-labelledby="progress-title">
        <div class="panel-heading">
            <div>
                <h2 id="progress-title" class="dashboard-section-title">
                    <span
                        class="dashboard-section-icon dashboard-section-icon--qc"
                        aria-hidden="true">
                        <svg
                            width="18"
                            height="18"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.7"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            focusable="false">
                            <rect x="8" y="2" width="8" height="4" rx="1.5" />
                            <path d="M8 4H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-2" />
                            <path d="m8 14 3 3 5-6" />
                        </svg>
                    </span>

                    <span>Progres pemeriksaan QC</span>
                </h2>

                <p class="muted small">
                    Ringkasan penyelesaian pemeriksaan pelanggan.
                </p>
            </div>
        </div>

        @if($hasQcSummary && $qcSummary['total'] > 0)
        <div class="qc-progress-summary">
            <div class="qc-progress-summary__top">
                <div>
                    <span class="qc-progress-summary__label">
                        Pemeriksaan selesai
                    </span>

                    <p>
                        {{ $qcSummary['complete'] }}
                        dari {{ $qcSummary['total'] }} pelanggan
                    </p>
                </div>

                <strong class="qc-progress-summary__value">
                    {{ $qcRate }}<span>%</span>
                </strong>
            </div>

            <progress
                class="qc-progress-meter"
                value="{{ $qcRate }}"
                max="100"
                aria-label="Persentase pemeriksaan QC selesai">
                {{ $qcRate }}%
            </progress>
        </div>

        <div class="table-responsive">
            <table>
                <caption class="sr-only">
                    Ringkasan status pemeriksaan QC cabang.
                </caption>

                <tbody>
                    <tr>
                        <th scope="row">QC Complete</th>
                        <td>{{ $qcSummary['complete'] }}</td>
                    </tr>
                    <tr>
                        <th scope="row">Sedang diproses</th>
                        <td>{{ $qcSummary['in_progress'] ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th scope="row">Belum QC</th>
                        <td>{{ $qcSummary['belum_qc'] ?? '—' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        @else
        <div class="empty-state">
            <div class="empty-symbol" aria-hidden="true">QC</div>

            <h3>
                {{ $hasQcSummary
                        ? 'Belum ada data pelanggan'
                        : 'Ringkasan QC belum tersedia' }}
            </h3>

            <p>
                {{ $hasQcSummary
                        ? 'Progres pemeriksaan akan ditampilkan setelah data pelanggan tersedia.'
                        : 'Panel ini menunggu data ringkasan QC dari backend.' }}
            </p>
        </div>
        @endif
    </section>

    <section class="panel" aria-labelledby="agency-title">
        <div class="panel-heading">
            <div>
                <h2 id="agency-title" class="dashboard-section-title">
                    <span
                        class="dashboard-section-icon dashboard-section-icon--agency"
                        aria-hidden="true">
                        <svg
                            width="18"
                            height="18"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.7"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            focusable="false">
                            <rect x="9" y="3" width="6" height="5" rx="1.2" />
                            <path d="M12 8v5M5 16v-3h14v3" />
                            <rect x="2" y="16" width="6" height="5" rx="1.2" />
                            <rect x="16" y="16" width="6" height="5" rx="1.2" />
                        </svg>
                    </span>

                    <span>Monitoring Agency</span>
                </h2>

                <p class="muted small">
                    Performa Agency yang menangani pelanggan cabang.
                </p>
            </div>
        </div>

        @if($agencyRows !== null)
        <div class="table-responsive">
            <table>
                <caption class="sr-only">
                    Ringkasan pemeriksaan QC per Agency.
                </caption>

                <thead>
                    <tr>
                        <th scope="col">Nama Agency</th>
                        <th scope="col">Pelanggan PS</th>
                        <th scope="col">QC Complete</th>
                        <th scope="col">Penyelesaian</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($agencyRows as $agency)
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
                    @empty
                    <tr>
                        <td colspan="4">
                            <div class="empty-state compact">
                                <h3>Belum ada data Agency</h3>
                                <p>
                                    Belum ada ringkasan Agency
                                    untuk ditampilkan.
                                </p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @else
        <div class="empty-state">
            <div class="empty-symbol" aria-hidden="true">AG</div>
            <h3>Monitoring Agency belum tersedia</h3>
            <p>
                Panel ini menunggu data ringkasan Agency dari backend.
            </p>
        </div>
        @endif
    </section>
</div>

{{-- PELANGGAN TERBARU --}}
<section class="panel" aria-labelledby="customers-title">
    <div class="panel-heading">
        <div>
            <h2 id="customers-title" class="dashboard-section-title">
                <span
                    class="dashboard-section-icon dashboard-section-icon--recent"
                    aria-hidden="true">
                    <svg
                        width="18"
                        height="18"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.7"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        focusable="false">
                        <circle cx="9" cy="7" r="4" />
                        <path d="M2 21v-2a6 6 0 0 1 6-6h2a6 6 0 0 1 5 2.7" />
                        <path d="M19 14v8M15 18h8" />
                    </svg>
                </span>

                <span>Pelanggan terbaru</span>
            </h2>

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
                @forelse(($customerRows ?? []) as $customer)
                @php
                /*
                * Aturan status dipertahankan dari
                * kode backend yang sebelumnya dikirim.
                */
                $qcStatus = $customer->qcRecord?->qc_status;
                $callStatus = $customer->qcRecord?->final_call_status;

                if ($qcStatus === 'complete') {
                $statusLabel = 'QC Complete';
                $statusClass = 'qc-status--complete';
                } elseif ($callStatus && $callStatus !== 'belum_dihubungi') {
                $statusLabel = 'In Progress';
                $statusClass = 'qc-status--progress';
                } else {
                $statusLabel = 'Belum QC';
                $statusClass = 'qc-status--pending';
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
                        {{ $customer->tanggal_ps
                                ? $customer->tanggal_ps->format('d M Y')
                                : '—' }}
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
                        <span class="badge qc-status {{ $statusClass }}">
                            <span
                                class="qc-status__dot"
                                aria-hidden="true"></span>

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
                            <h3>
                                {{ $customerRows === null
                                        ? 'Daftar pelanggan belum tersedia'
                                        : 'Belum ada data pelanggan' }}
                            </h3>

                            <p>
                                {{ $customerRows === null
                                        ? 'Tabel ini menunggu data pelanggan terbaru dari backend.'
                                        : 'Belum ada pelanggan untuk ditampilkan pada daftar ini.' }}
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
            <h2 id="sales-title" class="dashboard-section-title">
                <span
                    class="dashboard-section-icon dashboard-section-icon--sales"
                    aria-hidden="true">
                    <svg
                        width="18"
                        height="18"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.7"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        focusable="false">
                        <path d="M3 3v18h18" />
                        <path d="M8 17v-5M13 17V8M18 17V5" />
                    </svg>
                </span>

                <span>Performa Sales</span>
            </h2>

            <p class="muted small">
                Perbandingan progres QC berdasarkan Sales dalam cabang.
            </p>
        </div>
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
                @forelse(($salesRows ?? []) as $sale)
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
                            <h3>
                                {{ $salesRows === null
                                        ? 'Performa Sales belum tersedia'
                                        : 'Belum ada data Sales untuk ditampilkan' }}
                            </h3>

                            <p>
                                {{ $salesRows === null
                                        ? 'Tabel ini menunggu ringkasan performa Sales dari backend.'
                                        : 'Ringkasan performa akan mengikuti data Sales pada pelanggan cabang.' }}
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