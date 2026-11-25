@extends('layouts.region')

@section('title', 'Dashboard Monitoring Region')

@section('content')
@php
    $summary = $summary ?? [
        'total_pelanggan' => 0,
        'qc_selesai'      => 0,
        'qc_in_progress'  => 0,
        'belum_dihubungi' => 0,
    ];
    $branches           = $branches ?? collect();
    $agencies           = $agencies ?? collect();
    $branchProgress     = $branchProgress ?? collect();
    $billingSummary     = $billingSummary ?? [
        'paham'              => ['count' => 0, 'percentage' => 0],
        'perlu_diedukasi'    => ['count' => 0, 'percentage' => 0],
        'belum_dikonfirmasi' => ['count' => 0, 'percentage' => 0],
    ];
    $agencyPerformances = $agencyPerformances ?? collect();
    $salesPerformances  = $salesPerformances ?? collect();

    $activeBranchId   = request('branch_id');
    $activeAgencyId   = request('agency_id');
    $activeBranchName = $branches->firstWhere('id', (int) $activeBranchId)?->name ?? 'Semua Wilayah (Sumbagsel)';
    $activeAgencyName = $agencies->firstWhere('id', (int) $activeAgencyId)?->name ?? 'Semua Agency';

    $rawPeriode = request('periode_ps', now()->format('Y-m'));
    if (!preg_match('/^\d{4}-\d{2}$/', $rawPeriode)) {
        $rawPeriode = now()->format('Y-m');
    }
    [$selectedYear, $selectedMonth] = explode('-', $rawPeriode);
    $monthNamesId = [
        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
        '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
        '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
    ];
    $formattedPeriodeLabel = ($monthNamesId[$selectedMonth] ?? 'Bulan') . ' ' . $selectedYear;

    $totalPelanggan = max(0, (int) $summary['total_pelanggan']);
    $pctSelesai     = $totalPelanggan > 0 ? round(($summary['qc_selesai'] / $totalPelanggan) * 100, 1) : 0;
    $pctProgress    = $totalPelanggan > 0 ? round(($summary['qc_in_progress'] / $totalPelanggan) * 100, 1) : 0;
    $pctBelum       = $totalPelanggan > 0 ? round(($summary['belum_dihubungi'] / $totalPelanggan) * 100, 1) : 0;
@endphp

<!-- Loading Bar Top -->
<div class="top-loader" id="topLoader"></div>

<!-- Hero Welcome Banner -->
<section class="hero-banner">
    <div class="hero-content">
        <div class="hero-badge-row">
            <span class="live-dot"></span>
            <span>Pemantauan Aktif • {{ $activeBranchName }}</span>
        </div>
        <h1>Selamat Datang, {{ explode(' ', auth()->user()->name ?? 'Region')[0] }}!</h1>
        <p>Ringkasan interaktif kualitas layanan dan progres Quality Control <strong>Telkomsel Region Sumbagsel</strong>.</p>
    </div>
    <div class="hero-graphic-card" aria-hidden="true">
        <div class="bar b1"></div>
        <div class="bar b2"></div>
        <div class="bar b3"></div>
        <div class="shield-badge">✓</div>
    </div>
</section>

<!-- Filter Bar dengan Custom Dropdown & Custom Calendar Month Picker -->
<section class="card filter-card filter-elevated">
    <form method="GET" action="{{ url()->current() }}" class="filter-grid" id="mainFilterForm">
        <!-- 1. Custom Dropdown Area / Branch -->
        <div class="filter-group">
            <label>Area / Branch</label>
            <input type="hidden" name="branch_id" id="branch_id" value="{{ $activeBranchId }}">
            <div class="custom-select-wrapper" data-target-input="branch_id">
                <button type="button" class="custom-select-trigger" aria-expanded="false">
                    <span class="trigger-left">
                        <span class="trigger-icon icon-soft-red">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
                            </svg>
                        </span>
                        <span class="trigger-text">{{ $activeBranchId ? $activeBranchName : 'Semua Branch (Sumbagsel)' }}</span>
                    </span>
                    <svg class="chevron-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <polyline points="6 9 12 15 18 9"/>
                    </svg>
                </button>

                <div class="custom-select-panel">
                    <div class="dropdown-search-box">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                        <input type="text" class="dropdown-search-input" placeholder="Cari branch...">
                    </div>
                    <ul class="custom-select-options">
                        <li class="custom-option {{ empty($activeBranchId) ? 'selected' : '' }}" data-value="">
                            <span>Semua Branch (Sumbagsel)</span>
                            @if(empty($activeBranchId)) <span class="check-mark">✓</span> @endif
                        </li>
                        @foreach ($branches as $branch)
                            @php $isSel = (string) $activeBranchId === (string) $branch->id; @endphp
                            <li class="custom-option {{ $isSel ? 'selected' : '' }}" data-value="{{ $branch->id }}">
                                <span>{{ $branch->name }}</span>
                                @if($isSel) <span class="check-mark">✓</span> @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <!-- 2. Custom Dropdown Agency (SPV) -->
        <div class="filter-group">
            <label>Agency (SPV)</label>
            <input type="hidden" name="agency_id" id="agency_id" value="{{ $activeAgencyId }}">
            <div class="custom-select-wrapper" data-target-input="agency_id">
                <button type="button" class="custom-select-trigger" aria-expanded="false">
                    <span class="trigger-left">
                        <span class="trigger-icon icon-soft-blue">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                            </svg>
                        </span>
                        <span class="trigger-text">{{ $activeAgencyId ? $activeAgencyName : 'Semua Agency' }}</span>
                    </span>
                    <svg class="chevron-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <polyline points="6 9 12 15 18 9"/>
                    </svg>
                </button>

                <div class="custom-select-panel">
                    <div class="dropdown-search-box">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                        <input type="text" class="dropdown-search-input" placeholder="Cari agency (22 SPV)...">
                    </div>
                    <ul class="custom-select-options">
                        <li class="custom-option {{ empty($activeAgencyId) ? 'selected' : '' }}" data-value="">
                            <span>Semua Agency</span>
                            @if(empty($activeAgencyId)) <span class="check-mark">✓</span> @endif
                        </li>
                        @foreach ($agencies as $agency)
                            @php $isSel = (string) $activeAgencyId === (string) $agency->id; @endphp
                            <li class="custom-option {{ $isSel ? 'selected' : '' }}" data-value="{{ $agency->id }}">
                                <span>{{ $agency->name }}</span>
                                @if($isSel) <span class="check-mark">✓</span> @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <!-- 3. Custom Calendar Month Picker (Periode PS) -->
        <div class="filter-group">
            <label>Periode PS (Bulan)</label>
            <input type="hidden" name="periode_ps" id="periode_ps" value="{{ $rawPeriode }}">
            <div class="custom-month-wrapper" id="monthPickerWrapper" data-initial-year="{{ $selectedYear }}" data-initial-month="{{ $selectedMonth }}">
                <button type="button" class="custom-select-trigger" id="monthPickerTrigger" aria-expanded="false">
                    <span class="trigger-left">
                        <span class="trigger-icon icon-soft-amber">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                            </svg>
                        </span>
                        <span class="trigger-text" id="monthPickerLabel">{{ $formattedPeriodeLabel }}</span>
                    </span>
                    <svg class="chevron-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <polyline points="6 9 12 15 18 9"/>
                    </svg>
                </button>

                <div class="month-picker-panel" id="monthPickerPanel">
                    <div class="month-picker-header">
                        <button type="button" class="year-nav-btn" id="prevYearBtn" title="Tahun Sebelumnya">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                                <polyline points="15 18 9 12 15 6"/>
                            </svg>
                        </button>
                        <span class="year-display" id="pickerYearDisplay">{{ $selectedYear }}</span>
                        <button type="button" class="year-nav-btn" id="nextYearBtn" title="Tahun Berikutnya">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                                <polyline points="9 18 15 12 9 6"/>
                            </svg>
                        </button>
                    </div>

                    <div class="month-grid" id="monthGrid">
                        @php
                            $shortMonths = [
                                '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr',
                                '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Agu',
                                '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des',
                            ];
                        @endphp
                        @foreach ($shortMonths as $mNum => $mShort)
                            <button type="button"
                                    class="month-cell {{ $selectedMonth === $mNum ? 'active' : '' }}"
                                    data-month="{{ $mNum }}"
                                    data-fullname="{{ $monthNamesId[$mNum] }}">
                                {{ $mShort }}
                            </button>
                        @endforeach
                    </div>

                    <div class="month-picker-footer">
                        <button type="button" class="btn-this-month" id="selectCurrentMonthBtn"
                                data-now-ym="{{ now()->format('Y-m') }}"
                                data-now-year="{{ now()->format('Y') }}"
                                data-now-month="{{ now()->format('m') }}">
                            Bulan Ini ({{ $monthNamesId[now()->format('m')] }} {{ now()->format('Y') }})
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="filter-actions">
            @if(request()->hasAny(['branch_id', 'agency_id', 'periode_ps', 'sales_search']))
                <a href="{{ url()->current() }}" class="btn btn-outline btn-reset-pill">
                    <span>Reset Filter</span>
                </a>
            @else
                <span class="filter-hint-badge">Filter Otomatis</span>
            @endif
        </div>
    </form>
</section>

<!-- Info Bar saat Kartu KPI Diklik -->
<div class="kpi-focus-banner" id="kpiFocusBanner" hidden>
    <div class="kpi-focus-text">
        <span class="focus-dot"></span>
        <span id="kpiFocusMessage">Menyoroti metrik pilihan</span>
    </div>
    <button type="button" class="btn-clear-focus" id="clearKpiFocusBtn">Tutup Sorotan ✕</button>
</div>

<!-- 4 KPI Cards Interaktif -->
<!-- 4 KPI Cards Interaktif dengan Radial Ink Wave Fill -->
<section class="kpi-grid">
    <!-- Total Pelanggan -->
    <div class="card kpi-card interactive-card spotlight-card"
         data-kpi="total"
         data-target-section="analyticsSection"
         data-message="Menampilkan seluruh data pelanggan ({{ number_format($summary['total_pelanggan'], 0, ',', '.') }} pelanggan) pada {{ $activeBranchName }}."
         role="button"
         tabindex="0">
        <span class="kpi-ink-wave" aria-hidden="true"></span>
        <div class="kpi-card-inner">
            <div class="kpi-top-row">
                <div class="kpi-header">
                    <div class="kpi-icon icon-red">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                    </div>
                    <span class="kpi-label">Total Pelanggan</span>
                </div>
                <span class="kpi-pill pill-red">100%</span>
            </div>
            <div class="kpi-value count-up" data-target="{{ (int) $summary['total_pelanggan'] }}">
                {{ number_format($summary['total_pelanggan'], 0, ',', '.') }}
            </div>
            <div class="kpi-sub">Terinput pada periode terpilih</div>
            <div class="kpi-mini-progress">
                <div class="kpi-mini-fill bg-red" style="width: {{ $totalPelanggan > 0 ? 100 : 0 }}%;"></div>
            </div>
        </div>
        <svg class="sparkline spark-red" viewBox="0 0 200 32" preserveAspectRatio="none">
            <path d="M0,26 L35,21 L70,23 L105,14 L140,17 L175,8 L200,11 L200,32 L0,32 Z" fill="rgba(225, 29, 46, 0.10)"/>
            <path d="M0,26 L35,21 L70,23 L105,14 L140,17 L175,8 L200,11" fill="none" stroke="#E11D2E" stroke-width="2.2"/>
        </svg>
    </div>

    <!-- QC Selesai -->
    <div class="card kpi-card interactive-card spotlight-card"
         data-kpi="selesai"
         data-target-section="agencySection"
         data-message="Fokus QC Selesai: {{ number_format($summary['qc_selesai'], 0, ',', '.') }} pelanggan ({{ $pctSelesai }}% dari total). Lihat rincian capaian per Branch & Agency di bawah."
         role="button"
         tabindex="0">
        <span class="kpi-ink-wave" aria-hidden="true"></span>
        <div class="kpi-card-inner">
            <div class="kpi-top-row">
                <div class="kpi-header">
                    <div class="kpi-icon icon-green">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
                        </svg>
                    </div>
                    <span class="kpi-label">QC Selesai</span>
                </div>
                <span class="kpi-pill pill-green">{{ $pctSelesai }}%</span>
            </div>
            <div class="kpi-value count-up" data-target="{{ (int) $summary['qc_selesai'] }}">
                {{ number_format($summary['qc_selesai'], 0, ',', '.') }}
            </div>
            <div class="kpi-sub"><span class="trend-up">{{ $pctSelesai }}%</span> dari total pelanggan</div>
            <div class="kpi-mini-progress">
                <div class="kpi-mini-fill bg-green" style="width: {{ min(100, $pctSelesai) }}%;"></div>
            </div>
        </div>
        <svg class="sparkline spark-green" viewBox="0 0 200 32" preserveAspectRatio="none">
            <path d="M0,28 L35,24 L70,18 L105,20 L140,12 L175,14 L200,7 L200,32 L0,32 Z" fill="rgba(22, 163, 74, 0.10)"/>
            <path d="M0,28 L35,24 L70,18 L105,20 L140,12 L175,14 L200,7" fill="none" stroke="#16A34A" stroke-width="2.2"/>
        </svg>
    </div>

    <!-- QC In Progress -->
    <div class="card kpi-card interactive-card spotlight-card"
         data-kpi="progress"
         data-target-section="agencySection"
         data-message="Fokus QC In Progress: {{ number_format($summary['qc_in_progress'], 0, ',', '.') }} pelanggan ({{ $pctProgress }}%) sedang dalam tahap follow-up call."
         role="button"
         tabindex="0">
        <span class="kpi-ink-wave" aria-hidden="true"></span>
        <div class="kpi-card-inner">
            <div class="kpi-top-row">
                <div class="kpi-header">
                    <div class="kpi-icon icon-amber">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                        </svg>
                    </div>
                    <span class="kpi-label">QC In Progress</span>
                </div>
                <span class="kpi-pill pill-amber">{{ $pctProgress }}%</span>
            </div>
            <div class="kpi-value count-up" data-target="{{ (int) $summary['qc_in_progress'] }}">
                {{ number_format($summary['qc_in_progress'], 0, ',', '.') }}
            </div>
            <div class="kpi-sub">Dalam proses follow-up call</div>
            <div class="kpi-mini-progress">
                <div class="kpi-mini-fill bg-amber" style="width: {{ min(100, $pctProgress) }}%;"></div>
            </div>
        </div>
        <svg class="sparkline spark-amber" viewBox="0 0 200 32" preserveAspectRatio="none">
            <path d="M0,23 L35,26 L70,19 L105,22 L140,15 L175,18 L200,11 L200,32 L0,32 Z" fill="rgba(245, 158, 11, 0.12)"/>
            <path d="M0,23 L35,26 L70,19 L105,22 L140,15 L175,18 L200,11" fill="none" stroke="#F59E0B" stroke-width="2.2"/>
        </svg>
    </div>

    <!-- Belum Dihubungi -->
    <div class="card kpi-card interactive-card spotlight-card"
         data-kpi="belum"
         data-target-section="salesSection"
         data-message="Fokus Belum Dihubungi: {{ number_format($summary['belum_dihubungi'], 0, ',', '.') }} pelanggan ({{ $pctBelum }}%) menunggu panggilan QC pertama."
         role="button"
         tabindex="0">
        <span class="kpi-ink-wave" aria-hidden="true"></span>
        <div class="kpi-card-inner">
            <div class="kpi-top-row">
                <div class="kpi-header">
                    <div class="kpi-icon icon-blue">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                        </svg>
                    </div>
                    <span class="kpi-label">Belum Dihubungi</span>
                </div>
                <span class="kpi-pill pill-blue">{{ $pctBelum }}%</span>
            </div>
            <div class="kpi-value count-up" data-target="{{ (int) $summary['belum_dihubungi'] }}">
                {{ number_format($summary['belum_dihubungi'], 0, ',', '.') }}
            </div>
            <div class="kpi-sub">Menunggu percobaan call pertama</div>
            <div class="kpi-mini-progress">
                <div class="kpi-mini-fill bg-blue" style="width: {{ min(100, $pctBelum) }}%;"></div>
            </div>
        </div>
        <svg class="sparkline spark-blue" viewBox="0 0 200 32" preserveAspectRatio="none">
            <path d="M0,25 L35,20 L70,22 L105,17 L140,19 L175,12 L200,13 L200,32 L0,32 Z" fill="rgba(37, 99, 235, 0.10)"/>
            <path d="M0,25 L35,20 L70,22 L105,17 L140,19 L175,12 L200,13" fill="none" stroke="#2563EB" stroke-width="2.2"/>
        </svg>
    </div>
</section>

<!-- Middle Analytics Grid -->
<section class="analytics-grid" id="analyticsSection">
    <!-- % QC Selesai per Branch -->
    <div class="card section-card spotlight-card">
        <div class="card-header">
            <div class="card-title-wrap">
                <span class="title-accent"></span>
                <div>
                    <h2>% QC Selesai per Branch</h2>
                    <p>Klik nama branch untuk memfilter wilayah secara langsung</p>
                </div>
            </div>
            @if($activeBranchId)
                <button type="button" class="badge badge-neutral clickable-badge" id="clearBranchFilterBtn">
                    Semua Branch ✕
                </button>
            @endif
        </div>

        <div class="progress-list">
            @forelse ($branchProgress as $item)
                @php
                    $pct = max(0, min(100, (float) ($item['percentage'] ?? 0)));
                    $matchedBranch = $branches->firstWhere('name', $item['branch_name'] ?? '');
                    $isSelectedBranch = $matchedBranch && (string) $activeBranchId === (string) $matchedBranch->id;
                @endphp
                <div class="progress-row interactive-branch-row {{ $isSelectedBranch ? 'selected-branch' : '' }}"
                     data-branch-id="{{ $matchedBranch->id ?? '' }}"
                     title="Klik untuk filter cabang {{ $item['branch_name'] ?? '' }}">
                    <div class="progress-meta">
                        <span class="progress-name">
                            {{ $item['branch_name'] ?? '-' }}
                            @if($isSelectedBranch)
                                <span class="pill-active">Aktif</span>
                            @endif
                        </span>
                        <span class="progress-stat">
                            <strong>{{ $pct }}%</strong>
                            <small>({{ number_format($item['qc_selesai'] ?? 0) }}/{{ number_format($item['total'] ?? 0) }})</small>
                        </span>
                    </div>
                    <div class="progress-track">
                        <div class="progress-fill fill-red animated-bar" style="width: 0%;" data-width="{{ $pct }}%"></div>
                    </div>
                </div>
            @empty
                <div class="empty-state">Belum ada data progres QC per branch pada periode ini.</div>
            @endforelse
        </div>
    </div>

    <!-- Pemahaman Billing dengan Donut Chart Interaktif -->
    <div class="card section-card spotlight-card">
        <div class="card-header">
            <div class="card-title-wrap">
                <span class="title-accent"></span>
                <div>
                    <h2>Pemahaman Billing</h2>
                    <p>Arahkan kursor pada grafik atau kategori untuk melihat detail</p>
                </div>
            </div>
        </div>

        @php
            $pahamPct   = max(0, min(100, (float) ($billingSummary['paham']['percentage'] ?? 0)));
            $edukasiPct = max(0, min(100, (float) ($billingSummary['perlu_diedukasi']['percentage'] ?? 0)));
            $belumPct   = max(0, min(100, (float) ($billingSummary['belum_dikonfirmasi']['percentage'] ?? 0)));

            $pahamCnt   = (int) ($billingSummary['paham']['count'] ?? 0);
            $edukasiCnt = (int) ($billingSummary['perlu_diedukasi']['count'] ?? 0);
            $belumCnt   = (int) ($billingSummary['belum_dikonfirmasi']['count'] ?? 0);
        @endphp

        <div class="billing-interactive-layout">
            <div class="donut-wrapper">
                <canvas id="billingDonutChart"
                        data-paham="{{ $pahamCnt }}"
                        data-edukasi="{{ $edukasiCnt }}"
                        data-belum="{{ $belumCnt }}"
                        data-paham-pct="{{ $pahamPct }}"></canvas>
                <div class="donut-center-label" id="donutCenterInfo">
                    <strong id="donutCenterPct">{{ $pahamPct }}%</strong>
                    <span id="donutCenterText">Paham</span>
                </div>
            </div>

            <div class="progress-list billing-legend-list">
                <div class="progress-row billing-legend-item" data-index="0">
                    <div class="progress-meta">
                        <span class="progress-name"><span class="legend-dot dot-green"></span> Paham</span>
                        <span class="progress-stat">
                            <strong>{{ $pahamPct }}%</strong>
                            <small>({{ number_format($pahamCnt) }})</small>
                        </span>
                    </div>
                    <div class="progress-track">
                        <div class="progress-fill fill-green animated-bar" style="width: 0%;" data-width="{{ $pahamPct }}%"></div>
                    </div>
                </div>

                <div class="progress-row billing-legend-item" data-index="1">
                    <div class="progress-meta">
                        <span class="progress-name"><span class="legend-dot dot-amber"></span> Perlu Diedukasi</span>
                        <span class="progress-stat">
                            <strong>{{ $edukasiPct }}%</strong>
                            <small>({{ number_format($edukasiCnt) }})</small>
                        </span>
                    </div>
                    <div class="progress-track">
                        <div class="progress-fill fill-amber animated-bar" style="width: 0%;" data-width="{{ $edukasiPct }}%"></div>
                    </div>
                </div>

                <div class="progress-row billing-legend-item" data-index="2">
                    <div class="progress-meta">
                        <span class="progress-name"><span class="legend-dot dot-red"></span> Belum Dikonfirmasi</span>
                        <span class="progress-stat">
                            <strong>{{ $belumPct }}%</strong>
                            <small>({{ number_format($belumCnt) }})</small>
                        </span>
                    </div>
                    <div class="progress-track">
                        <div class="progress-fill fill-red animated-bar" style="width: 0%;" data-width="{{ $belumPct }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Performa Agency -->
<section class="card section-card table-section spotlight-card" id="agencySection">
    <div class="card-header header-with-filter">
        <div class="card-title-wrap">
            <span class="title-accent"></span>
            <div>
                <h2>Performa Agency</h2>
                <p>Klik header kolom untuk mengurutkan atau ketik nama agency untuk mencari instan</p>
            </div>
        </div>
        <div class="inline-sales-filter">
            <div class="search-input-icon-wrap">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text"
                       id="liveAgencySearch"
                       placeholder="Cari Agency atau Branch..."
                       class="form-control form-control-sm has-left-icon">
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="data-table" id="agencyTable">
            <thead>
                <tr>
                    <th class="sortable-th" data-col="0" data-type="text">Agency <span class="sort-icon">⇅</span></th>
                    <th class="sortable-th" data-col="1" data-type="text">Branch <span class="sort-icon">⇅</span></th>
                    <th class="text-right sortable-th" data-col="2" data-type="num">Total <span class="sort-icon">⇅</span></th>
                    <th class="text-right sortable-th" data-col="3" data-type="num">QC Selesai <span class="sort-icon">⇅</span></th>
                    <th class="text-right sortable-th" data-col="4" data-type="num">Perlu Edukasi <span class="sort-icon">⇅</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($agencyPerformances as $agencyRow)
                    @php
                        $agencyPct = (float) ($agencyRow->qc_selesai_pct ?? 0);
                    @endphp
                    <tr class="searchable-agency-row">
                        <td class="font-semibold">{{ $agencyRow->agency_name ?? '-' }}</td>
                        <td><span class="badge badge-neutral">{{ $agencyRow->branch_name ?? '-' }}</span></td>
                        <td class="text-right" data-val="{{ (int) ($agencyRow->total ?? 0) }}">
                            {{ number_format($agencyRow->total ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="text-right" data-val="{{ $agencyPct }}">
                            <div class="inline-bar-cell">
                                <div class="mini-track">
                                    <div class="mini-fill" style="width: {{ min(100, $agencyPct) }}%;"></div>
                                </div>
                                <span class="font-semibold">{{ $agencyPct }}%</span>
                            </div>
                        </td>
                        <td class="text-right" data-val="{{ (int) ($agencyRow->perlu_edukasi ?? 0) }}">
                            <span class="badge {{ ($agencyRow->perlu_edukasi ?? 0) > 0 ? 'badge-warning' : 'badge-success' }}">
                                {{ number_format($agencyRow->perlu_edukasi ?? 0, 0, ',', '.') }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center empty-cell">Belum ada data performa agency untuk filter yang dipilih.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

<!-- Performa Sales -->
<section class="card section-card table-section spotlight-card" id="salesSection">
    <div class="card-header header-with-filter">
        <div class="card-title-wrap">
            <span class="title-accent"></span>
            <div>
                <h2>Performa Sales</h2>
                <p>Pencarian instan & pemeringkatan kualitas pelanggan per Sales Force (SF)</p>
            </div>
        </div>

        <div class="inline-sales-filter">
            <div class="search-input-icon-wrap">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text"
                       id="liveSalesSearch"
                       value="{{ request('sales_search') }}"
                       placeholder="Ketik Kode SF / Nama SF..."
                       class="form-control form-control-sm has-left-icon">
            </div>

            <!-- Custom Sort Dropdown untuk Performa Sales -->
            <div class="custom-select-wrapper custom-select-sm" id="salesSortDropdown">
                <button type="button" class="custom-select-trigger trigger-sm" aria-expanded="false">
                    <span class="trigger-left">
                        <span class="trigger-text" id="salesSortLabel">Total Tertinggi</span>
                    </span>
                    <svg class="chevron-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <polyline points="6 9 12 15 18 9"/>
                    </svg>
                </button>
                <div class="custom-select-panel panel-right">
                    <ul class="custom-select-options">
                        <li class="custom-option selected" data-sort="total_desc">
                            <span>Total Tertinggi</span>
                            <span class="check-mark">✓</span>
                        </li>
                        <li class="custom-option" data-sort="qc_pct_desc">
                            <span>% QC Selesai Tertinggi</span>
                        </li>
                        <li class="custom-option" data-sort="edukasi_desc">
                            <span>Perlu Edukasi Terbanyak</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="data-table" id="salesTable">
            <thead>
                <tr>
                    <th class="sortable-th" data-col="0" data-type="text">Kode SF <span class="sort-icon">⇅</span></th>
                    <th class="sortable-th" data-col="1" data-type="text">Nama Sales (SF) <span class="sort-icon">⇅</span></th>
                    <th class="sortable-th" data-col="2" data-type="text">Branch <span class="sort-icon">⇅</span></th>
                    <th class="text-right sortable-th" data-col="3" data-type="num">Total Pelanggan <span class="sort-icon">⇅</span></th>
                    <th class="text-right sortable-th" data-col="4" data-type="num">QC Selesai <span class="sort-icon">⇅</span></th>
                    <th class="text-right sortable-th" data-col="5" data-type="num">Paham Billing <span class="sort-icon">⇅</span></th>
                    <th class="text-right sortable-th" data-col="6" data-type="num">Perlu Edukasi <span class="sort-icon">⇅</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($salesPerformances as $salesRow)
                    @php
                        $salesQcPct = (float) ($salesRow->qc_selesai_pct ?? 0);
                    @endphp
                    <tr class="searchable-sales-row">
                        <td><code>{{ $salesRow->kode_sf ?? '-' }}</code></td>
                        <td class="font-semibold">{{ $salesRow->nama_sf ?? '-' }}</td>
                        <td><span class="badge badge-neutral">{{ $salesRow->branch_name ?? '-' }}</span></td>
                        <td class="text-right" data-val="{{ (int) ($salesRow->total ?? 0) }}">
                            {{ number_format($salesRow->total ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="text-right" data-val="{{ $salesQcPct }}">
                            <div class="inline-bar-cell">
                                <div class="mini-track">
                                    <div class="mini-fill" style="width: {{ min(100, $salesQcPct) }}%;"></div>
                                </div>
                                <span class="font-semibold">{{ $salesQcPct }}%</span>
                            </div>
                        </td>
                        <td class="text-right" data-val="{{ (int) ($salesRow->paham_billing ?? 0) }}">
                            {{ number_format($salesRow->paham_billing ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="text-right" data-val="{{ (int) ($salesRow->perlu_edukasi ?? 0) }}">
                            <span class="badge {{ ($salesRow->perlu_edukasi ?? 0) > 0 ? 'badge-warning' : 'badge-success' }}">
                                {{ number_format($salesRow->perlu_edukasi ?? 0, 0, ',', '.') }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center empty-cell">Belum ada data performa sales untuk filter yang dipilih.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const loader = document.getElementById('topLoader');
    const mainForm = document.getElementById('mainFilterForm');
    const branchInput = document.getElementById('branch_id');

    function startLoading() {
        if (loader) loader.classList.add('active');
    }

    function closeAllPopovers(except = null) {
        document.querySelectorAll('.custom-select-wrapper.open, .custom-month-wrapper.open').forEach(el => {
            if (el !== except) {
                el.classList.remove('open');
                el.querySelector('button')?.setAttribute('aria-expanded', 'false');
            }
        });
    }

    // 1. Custom Dropdown Logic (Branch, Agency & Sales Sort)
    document.querySelectorAll('.custom-select-wrapper').forEach(wrapper => {
        const trigger = wrapper.querySelector('.custom-select-trigger');
        const searchInput = wrapper.querySelector('.dropdown-search-input');
        const options = wrapper.querySelectorAll('.custom-option');
        const targetInputId = wrapper.dataset.targetInput;
        const hiddenInput = targetInputId ? document.getElementById(targetInputId) : null;

        trigger?.addEventListener('click', (e) => {
            e.stopPropagation();
            const willOpen = !wrapper.classList.contains('open');
            closeAllPopovers(wrapper);
            wrapper.classList.toggle('open', willOpen);
            trigger.setAttribute('aria-expanded', String(willOpen));
            if (willOpen && searchInput) {
                searchInput.value = '';
                options.forEach(opt => opt.style.display = '');
                setTimeout(() => searchInput.focus(), 50);
            }
        });

        searchInput?.addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            options.forEach(opt => {
                const txt = opt.textContent.toLowerCase();
                opt.style.display = txt.includes(q) ? '' : 'none';
            });
        });

        options.forEach(opt => {
            opt.addEventListener('click', (e) => {
                e.stopPropagation();
                // Jika dropdown filter utama (Branch / Agency)
                if (hiddenInput) {
                    const val = opt.dataset.value ?? '';
                    hiddenInput.value = val;
                    wrapper.querySelector('.trigger-text').textContent = opt.querySelector('span')?.textContent.trim() || '';
                    closeAllPopovers();
                    startLoading();
                    mainForm?.submit();
                    return;
                }

                // Jika dropdown sort lokal Performa Sales
                if (wrapper.id === 'salesSortDropdown') {
                    options.forEach(o => {
                        o.classList.remove('selected');
                        o.querySelector('.check-mark')?.remove();
                    });
                    opt.classList.add('selected');
                    const check = document.createElement('span');
                    check.className = 'check-mark';
                    check.textContent = '✓';
                    opt.appendChild(check);

                    document.getElementById('salesSortLabel').textContent = opt.querySelector('span')?.textContent.trim() || '';
                    closeAllPopovers();
                    sortSalesTableByKey(opt.dataset.sort || 'total_desc');
                }
            });
        });
    });

    // 2. Custom Calendar Month Picker Logic (Periode PS)
    const monthWrapper = document.getElementById('monthPickerWrapper');
    const monthTrigger = document.getElementById('monthPickerTrigger');
    const yearDisplay  = document.getElementById('pickerYearDisplay');
    const periodeInput = document.getElementById('periode_ps');

    if (monthWrapper && monthTrigger && yearDisplay && periodeInput) {
        let currentPickerYear = parseInt(monthWrapper.dataset.initialYear || '{{ now()->format("Y") }}', 10);
        const activeMonth = monthWrapper.dataset.initialMonth || '{{ now()->format("m") }}';
        const initialYear = currentPickerYear;

        function refreshMonthCellsHighlight() {
            document.querySelectorAll('#monthGrid .month-cell').forEach(cell => {
                const isMatch = (currentPickerYear === initialYear && cell.dataset.month === activeMonth);
                cell.classList.toggle('active', isMatch);
            });
        }

        monthTrigger.addEventListener('click', (e) => {
            e.stopPropagation();
            const willOpen = !monthWrapper.classList.contains('open');
            closeAllPopovers(monthWrapper);
            monthWrapper.classList.toggle('open', willOpen);
            monthTrigger.setAttribute('aria-expanded', String(willOpen));
        });

        document.getElementById('prevYearBtn')?.addEventListener('click', (e) => {
            e.stopPropagation();
            currentPickerYear--;
            yearDisplay.textContent = currentPickerYear;
            refreshMonthCellsHighlight();
        });

        document.getElementById('nextYearBtn')?.addEventListener('click', (e) => {
            e.stopPropagation();
            currentPickerYear++;
            yearDisplay.textContent = currentPickerYear;
            refreshMonthCellsHighlight();
        });

        document.querySelectorAll('#monthGrid .month-cell').forEach(cell => {
            cell.addEventListener('click', (e) => {
                e.stopPropagation();
                const m = cell.dataset.month;
                const fullName = cell.dataset.fullname;
                periodeInput.value = `${currentPickerYear}-${m}`;
                document.getElementById('monthPickerLabel').textContent = `${fullName} ${currentPickerYear}`;
                closeAllPopovers();
                startLoading();
                mainForm?.submit();
            });
        });

        document.getElementById('selectCurrentMonthBtn')?.addEventListener('click', function (e) {
            e.stopPropagation();
            periodeInput.value = this.dataset.nowYm;
            closeAllPopovers();
            startLoading();
            mainForm?.submit();
        });
    }

    document.addEventListener('click', () => closeAllPopovers());

    // 3. Spotlight Hover mengikuti gerakan kursor di atas kartu
    document.querySelectorAll('.spotlight-card').forEach(card => {
        card.addEventListener('mousemove', (e) => {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            card.style.setProperty('--mouse-x', `${x}px`);
            card.style.setProperty('--mouse-y', `${y}px`);
        });
    });

    // 4. Klik Kartu KPI untuk menyorot & melompat halus ke bagian terkait
    const focusBanner = document.getElementById('kpiFocusBanner');
    const focusMsg = document.getElementById('kpiFocusMessage');
    const clearFocusBtn = document.getElementById('clearKpiFocusBtn');
    const kpiCards = document.querySelectorAll('.kpi-card');

    function activateKpiCard(card) {
        const alreadySelected = card.classList.contains('is-selected');
        kpiCards.forEach(c => c.classList.remove('is-selected'));

        if (alreadySelected) {
            if (focusBanner) focusBanner.hidden = true;
            return;
        }

        card.classList.add('is-selected');
        if (focusBanner && focusMsg) {
            focusMsg.textContent = card.dataset.message || '';
            focusBanner.hidden = false;
        }

        const targetId = card.dataset.targetSection;
        const targetEl = targetId ? document.getElementById(targetId) : null;
        if (targetEl) {
            targetEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            targetEl.classList.add('section-pulse');
            setTimeout(() => targetEl.classList.remove('section-pulse'), 1100);
        }
    }

    kpiCards.forEach(card => {
        card.addEventListener('click', () => activateKpiCard(card));
        card.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                activateKpiCard(card);
            }
        });
    });

    clearFocusBtn?.addEventListener('click', () => {
        kpiCards.forEach(c => c.classList.remove('is-selected'));
        if (focusBanner) focusBanner.hidden = true;
    });

    // 5. Klik baris Branch Progress untuk filter cepat
    document.querySelectorAll('.interactive-branch-row').forEach(row => {
        row.addEventListener('click', () => {
            const bId = row.dataset.branchId;
            if (!branchInput || !bId) return;
            branchInput.value = (branchInput.value === bId) ? '' : bId;
            startLoading();
            mainForm?.submit();
        });
    });

    document.getElementById('clearBranchFilterBtn')?.addEventListener('click', () => {
        if (!branchInput) return;
        branchInput.value = '';
        startLoading();
        mainForm?.submit();
    });

    // 6. Animasi Count-Up & Progress Bar
    document.querySelectorAll('.count-up').forEach(el => {
        const target = parseInt(el.dataset.target || '0', 10);
        if (target <= 0) return;
        const duration = 650;
        const startTime = performance.now();

        function update(now) {
            const progress = Math.min((now - startTime) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = Math.floor(eased * target).toLocaleString('id-ID');
            if (progress < 1) requestAnimationFrame(update);
            else el.textContent = target.toLocaleString('id-ID');
        }
        requestAnimationFrame(update);
    });

    requestAnimationFrame(() => {
        document.querySelectorAll('.animated-bar').forEach(bar => {
            bar.style.width = bar.dataset.width || '0%';
        });
    });

    // 7. Donut Chart Interaktif Pemahaman Billing
    const donutCanvas = document.getElementById('billingDonutChart');
    if (donutCanvas && typeof Chart !== 'undefined') {
        const paham   = parseInt(donutCanvas.dataset.paham || '0', 10);
        const edukasi = parseInt(donutCanvas.dataset.edukasi || '0', 10);
        const belum   = parseInt(donutCanvas.dataset.belum || '0', 10);
        const total   = paham + edukasi + belum;

        const labels = ['Paham', 'Perlu Diedukasi', 'Belum Dikonfirmasi'];
        const values = total > 0 ? [paham, edukasi, belum] : [1, 0, 0];
        const colors = total > 0 ? ['#16a34a', '#f59e0b', '#e11d2e'] : ['#e2e8f0', '#e2e8f0', '#e2e8f0'];

        const centerPct  = document.getElementById('donutCenterPct');
        const centerText = document.getElementById('donutCenterText');

        const billingChart = new Chart(donutCanvas, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: colors,
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                cutout: '72%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        enabled: total > 0,
                        callbacks: {
                            label: function (ctx) {
                                const val = ctx.raw || 0;
                                const pct = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
                                return ` ${ctx.label}: ${val.toLocaleString('id-ID')} (${pct}%)`;
                            }
                        }
                    }
                },
                onHover: function (event, elements) {
                    if (total === 0) return;
                    if (elements.length > 0) {
                        const idx = elements[0].index;
                        const val = [paham, edukasi, belum][idx];
                        const pct = ((val / total) * 100).toFixed(1);
                        centerPct.textContent = pct + '%';
                        centerText.textContent = labels[idx];
                    } else {
                        centerPct.textContent = (donutCanvas.dataset.pahamPct || '0') + '%';
                        centerText.textContent = 'Paham';
                    }
                }
            }
        });

        document.querySelectorAll('.billing-legend-item').forEach(item => {
            item.addEventListener('mouseenter', () => {
                if (total === 0) return;
                const idx = parseInt(item.dataset.index, 10);
                billingChart.setActiveElements([{ datasetIndex: 0, index: idx }]);
                const val = [paham, edukasi, belum][idx];
                centerPct.textContent = ((val / total) * 100).toFixed(1) + '%';
                centerText.textContent = labels[idx];
                billingChart.update();
            });
            item.addEventListener('mouseleave', () => {
                if (total === 0) return;
                billingChart.setActiveElements([]);
                centerPct.textContent = (donutCanvas.dataset.pahamPct || '0') + '%';
                centerText.textContent = 'Paham';
                billingChart.update();
            });
        });
    }

    // 8. Live Search & Instant Sort Tabel
    function bindLiveSearch(inputId, rowSelector) {
        const input = document.getElementById(inputId);
        if (!input) return;
        input.addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            document.querySelectorAll(rowSelector).forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(q) ? '' : 'none';
            });
        });
    }
    bindLiveSearch('liveAgencySearch', '.searchable-agency-row');
    bindLiveSearch('liveSalesSearch', '.searchable-sales-row');

    function makeTableSortable(tableId) {
        const table = document.getElementById(tableId);
        if (!table) return;
        const headers = table.querySelectorAll('th.sortable-th');
        let sortDir = {};

        headers.forEach(th => {
            th.addEventListener('click', () => {
                const colIdx = parseInt(th.dataset.col, 10);
                const type = th.dataset.type || 'text';
                const asc = !sortDir[colIdx];
                sortDir = { [colIdx]: asc };

                const tbody = table.querySelector('tbody');
                const rows = Array.from(tbody.querySelectorAll('tr')).filter(r => r.children.length > 1);

                rows.sort((a, b) => {
                    const cellA = a.children[colIdx];
                    const cellB = b.children[colIdx];
                    if (type === 'num') {
                        const valA = parseFloat(cellA.dataset.val || cellA.textContent.replace(/[^\d.-]/g, '') || '0');
                        const valB = parseFloat(cellB.dataset.val || cellB.textContent.replace(/[^\d.-]/g, '') || '0');
                        return asc ? valA - valB : valB - valA;
                    }
                    const txtA = cellA.textContent.trim().toLowerCase();
                    const txtB = cellB.textContent.trim().toLowerCase();
                    return asc ? txtA.localeCompare(txtB) : txtB.localeCompare(txtA);
                });

                rows.forEach(r => tbody.appendChild(r));
            });
        });
    }
    makeTableSortable('agencyTable');
    makeTableSortable('salesTable');

    function sortSalesTableByKey(sortKey) {
        const table = document.getElementById('salesTable');
        if (!table) return;
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr')).filter(r => r.children.length > 1);
        const colMap = { total_desc: 3, qc_pct_desc: 4, edukasi_desc: 6 };
        const colIdx = colMap[sortKey] ?? 3;

        rows.sort((a, b) => {
            const valA = parseFloat(a.children[colIdx]?.dataset.val || '0');
            const valB = parseFloat(b.children[colIdx]?.dataset.val || '0');
            return valB - valA;
        });
        rows.forEach(r => tbody.appendChild(r));
    }
});
</script>
@endpush