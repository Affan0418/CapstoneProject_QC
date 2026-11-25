@extends('layouts.region')

@section('title', 'Laporan Quality Control - Region Sumbagsel')

@section('content')
@php
    $branches      = $branches ?? collect();
    $agencies      = $agencies ?? collect();
    $reportRows    = $reportRows ?? collect();
    $reportSummary = $reportSummary ?? [
        'total_data'    => 0,
        'total_selesai' => 0,
        'pct_selesai'   => 0,
        'total_paham'   => 0,
        'pct_paham'     => 0,
        'total_edukasi' => 0,
    ];

    $activeBranchId   = request('branch_id');
    $activeAgencyId   = request('agency_id');
    $activeQcStatus   = request('qc_status', '');
    $activeBilling    = request('pemahaman_billing', '');
    $activeBranchName = $branches->firstWhere('id', (int) $activeBranchId)?->name ?? 'Semua Branch (Sumbagsel)';
    $activeAgencyName = $agencies->firstWhere('id', (int) $activeAgencyId)?->name ?? 'Semua Agency (SPV)';

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
@endphp

<div class="top-loader" id="topLoader"></div>

<!-- Hero Header -->
<section class="hero-banner">
    <div class="hero-content">
        <div class="hero-badge-row">
            <span class="live-dot"></span>
            <span>Rekapitulasi & Export Data • {{ $formattedPeriodeLabel }}</span>
        </div>
        <h1>Laporan Hasil Quality Control</h1>
        <p>Unduh dan audit rincian hasil panggilan QC pelanggan, pemahaman tagihan, serta catatan verifikasi per Branch dan Agency.</p>
    </div>
    <div class="hero-action-cluster">
        <a href="{{ route('region.reports.export', request()->query()) }}" class="btn-export-excel">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="7 10 12 15 17 10"/>
                <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            <span>Export CSV / Excel</span>
        </a>
        <button type="button" onclick="window.print()" class="btn-print-report">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <polyline points="6 9 6 2 18 2 18 9"/>
                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                <rect x="6" y="14" width="12" height="8"/>
            </svg>
            <span>Cetak</span>
        </button>
    </div>
</section>

<!-- Filter Laporan -->
<section class="card filter-card filter-elevated no-print">
    <form method="GET" action="{{ url()->current() }}" class="report-filter-grid" id="reportFilterForm">
        <!-- Branch -->
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
                        <span class="trigger-text">{{ $activeBranchName }}</span>
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

        <!-- Agency -->
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
                        <span class="trigger-text">{{ $activeAgencyName }}</span>
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
                        <input type="text" class="dropdown-search-input" placeholder="Cari agency...">
                    </div>
                    <ul class="custom-select-options">
                        <li class="custom-option {{ empty($activeAgencyId) ? 'selected' : '' }}" data-value="">
                            <span>Semua Agency (SPV)</span>
                            @if(empty($activeAgencyId)) <span class="check-mark">✓</span> @endif
                        </li>
                        @foreach ($agencies as $agency)
                            @php $isAgSel = (string) $activeAgencyId === (string) $agency->id; @endphp
                            <li class="custom-option {{ $isAgSel ? 'selected' : '' }}" data-value="{{ $agency->id }}">
                                <span>{{ $agency->name }}</span>
                                @if($isAgSel) <span class="check-mark">✓</span> @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <!-- Status QC -->
        <div class="filter-group">
            <label>Status QC</label>
            <input type="hidden" name="qc_status" id="qc_status" value="{{ $activeQcStatus }}">
            <div class="custom-select-wrapper" data-target-input="qc_status">
                <button type="button" class="custom-select-trigger" aria-expanded="false">
                    <span class="trigger-left">
                        <span class="trigger-icon icon-soft-amber">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                            </svg>
                        </span>
                        <span class="trigger-text">
                            @if($activeQcStatus === 'selesai') QC Selesai
                            @elseif($activeQcStatus === 'progress') QC In Progress
                            @elseif($activeQcStatus === 'belum') Belum Dihubungi
                            @else Semua Status QC
                            @endif
                        </span>
                    </span>
                    <svg class="chevron-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <polyline points="6 9 12 15 18 9"/>
                    </svg>
                </button>
                <div class="custom-select-panel">
                    <ul class="custom-select-options">
                        <li class="custom-option {{ $activeQcStatus === '' ? 'selected' : '' }}" data-value=""><span>Semua Status QC</span></li>
                        <li class="custom-option {{ $activeQcStatus === 'selesai' ? 'selected' : '' }}" data-value="selesai"><span>QC Selesai</span></li>
                        <li class="custom-option {{ $activeQcStatus === 'progress' ? 'selected' : '' }}" data-value="progress"><span>QC In Progress</span></li>
                        <li class="custom-option {{ $activeQcStatus === 'belum' ? 'selected' : '' }}" data-value="belum"><span>Belum Dihubungi</span></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Pemahaman Billing -->
        <div class="filter-group">
            <label>Pemahaman Billing</label>
            <input type="hidden" name="pemahaman_billing" id="pemahaman_billing" value="{{ $activeBilling }}">
            <div class="custom-select-wrapper" data-target-input="pemahaman_billing">
                <button type="button" class="custom-select-trigger" aria-expanded="false">
                    <span class="trigger-left">
                        <span class="trigger-icon icon-soft-red">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
                            </svg>
                        </span>
                        <span class="trigger-text">
                            @if($activeBilling === 'paham') Sudah Paham
                            @elseif($activeBilling === 'edukasi') Perlu Diedukasi
                            @else Semua Status Billing
                            @endif
                        </span>
                    </span>
                    <svg class="chevron-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <polyline points="6 9 12 15 18 9"/>
                    </svg>
                </button>
                <div class="custom-select-panel">
                    <ul class="custom-select-options">
                        <li class="custom-option {{ $activeBilling === '' ? 'selected' : '' }}" data-value=""><span>Semua Status Billing</span></li>
                        <li class="custom-option {{ $activeBilling === 'paham' ? 'selected' : '' }}" data-value="paham"><span>Sudah Paham</span></li>
                        <li class="custom-option {{ $activeBilling === 'edukasi' ? 'selected' : '' }}" data-value="edukasi"><span>Perlu Diedukasi</span></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Periode PS -->
        <div class="filter-group">
            <label>Periode PS</label>
            <input type="hidden" name="periode_ps" id="periode_ps" value="{{ $rawPeriode }}">
            <div class="custom-month-wrapper" id="monthPickerWrapper" data-initial-year="{{ $selectedYear }}" data-initial-month="{{ $selectedMonth }}">
                <button type="button" class="custom-select-trigger" id="monthPickerTrigger" aria-expanded="false">
                    <span class="trigger-left">
                        <span class="trigger-icon icon-soft-amber">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                            </svg>
                        </span>
                        <span class="trigger-text">{{ $formattedPeriodeLabel }}</span>
                    </span>
                    <svg class="chevron-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <polyline points="6 9 12 15 18 9"/>
                    </svg>
                </button>

                <div class="month-picker-panel" id="monthPickerPanel">
                    <div class="month-picker-header">
                        <button type="button" class="year-nav-btn" id="prevYearBtn">‹</button>
                        <span class="year-display" id="pickerYearDisplay">{{ $selectedYear }}</span>
                        <button type="button" class="year-nav-btn" id="nextYearBtn">›</button>
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
                                    data-month="{{ $mNum }}">
                                {{ $mShort }}
                            </button>
                        @endforeach
                    </div>
                    <div class="month-picker-footer">
                        <button type="button" class="btn-this-month" id="selectCurrentMonthBtn" data-now-ym="{{ now()->format('Y-m') }}">
                            Bulan Ini ({{ $monthNamesId[now()->format('m')] }} {{ now()->format('Y') }})
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>

<!-- 4 Ringkasan Laporan -->
<section class="kpi-grid">
    <div class="card kpi-card interactive-card spotlight-card" data-kpi="total">
        <span class="kpi-ink-wave" aria-hidden="true"></span>
        <div class="kpi-card-inner">
            <div class="kpi-top-row">
                <div class="kpi-header">
                    <div class="kpi-icon icon-red">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
                        </svg>
                    </div>
                    <span class="kpi-label">Total Baris Laporan</span>
                </div>
                <span class="kpi-pill pill-red">Terfilter</span>
            </div>
            <div class="kpi-value">{{ number_format($reportSummary['total_data'], 0, ',', '.') }}</div>
            <div class="kpi-sub">Total record pelanggan sesuai filter</div>
        </div>
    </div>

    <div class="card kpi-card interactive-card spotlight-card" data-kpi="selesai">
        <span class="kpi-ink-wave" aria-hidden="true"></span>
        <div class="kpi-card-inner">
            <div class="kpi-top-row">
                <div class="kpi-header">
                    <div class="kpi-icon icon-green">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
                        </svg>
                    </div>
                    <span class="kpi-label">Terverifikasi Selesai</span>
                </div>
                <span class="kpi-pill pill-green">{{ $reportSummary['pct_selesai'] }}%</span>
            </div>
            <div class="kpi-value">{{ number_format($reportSummary['total_selesai'], 0, ',', '.') }}</div>
            <div class="kpi-sub">Pelanggan telah selesai proses QC</div>
        </div>
    </div>

    <div class="card kpi-card interactive-card spotlight-card" data-kpi="belum">
        <span class="kpi-ink-wave" aria-hidden="true"></span>
        <div class="kpi-card-inner">
            <div class="kpi-top-row">
                <div class="kpi-header">
                    <div class="kpi-icon icon-blue">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                        </svg>
                    </div>
                    <span class="kpi-label">Paham Tagihan</span>
                </div>
                <span class="kpi-pill pill-blue">{{ $reportSummary['pct_paham'] }}%</span>
            </div>
            <div class="kpi-value">{{ number_format($reportSummary['total_paham'], 0, ',', '.') }}</div>
            <div class="kpi-sub">Pelanggan memahami rincian billing</div>
        </div>
    </div>

    <div class="card kpi-card interactive-card spotlight-card" data-kpi="progress">
        <span class="kpi-ink-wave" aria-hidden="true"></span>
        <div class="kpi-card-inner">
            <div class="kpi-top-row">
                <div class="kpi-header">
                    <div class="kpi-icon icon-amber">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                    </div>
                    <span class="kpi-label">Perlu Edukasi</span>
                </div>
                <span class="kpi-pill pill-amber">Tindak Lanjut</span>
            </div>
            <div class="kpi-value">{{ number_format($reportSummary['total_edukasi'], 0, ',', '.') }}</div>
            <div class="kpi-sub">Butuh penjelasan tagihan tambahan</div>
        </div>
    </div>
</section>

<!-- Tabel Rincian Laporan -->
<section class="card section-card table-section spotlight-card">
    <div class="card-header header-with-filter">
        <div class="card-title-wrap">
            <span class="title-accent"></span>
            <div>
                <h2>Rincian Data Laporan QC Pelanggan</h2>
                <p>Menampilkan data hasil verifikasi pelanggan berdasarkan filter aktif</p>
            </div>
        </div>

        <div class="inline-sales-filter no-print">
            @if(request()->hasAny(['branch_id', 'agency_id', 'qc_status', 'pemahaman_billing', 'periode_ps']))
                <a href="{{ url()->current() }}" class="btn btn-outline btn-reset-pill">Reset Filter</a>
            @endif
        </div>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Pelanggan & Kontak</th>
                    <th>Branch</th>
                    <th>Agency (SPV)</th>
                    <th>Sales Force</th>
                    <th>Tanggal PS</th>
                    <th class="text-center">Status QC</th>
                    <th class="text-center">Pemahaman Billing</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reportRows as $row)
                    @php
                        $rawStatus  = strtolower(trim((string) ($row->qc_status ?? '')));
                        $rawBilling = strtolower(trim((string) ($row->pemahaman_billing ?? '')));
                    @endphp
                    <tr>
                        <td>
                            <div class="pic-cell">
                                <span class="pic-name">{{ $row->customer_name ?? '-' }}</span>
                                <small class="pic-phone">{{ $row->internet_number ?? $row->customer_code ?? '' }} • {{ $row->customer_phone ?? '-' }}</small>
                            </div>
                        </td>
                        <td><span class="badge badge-neutral">{{ $row->branch_name ?? '-' }}</span></td>
                        <td class="font-semibold">{{ $row->agency_name ?? '-' }}</td>
                        <td>
                            <div class="pic-cell">
                                <span class="pic-name">{{ $row->nama_sf ?? '-' }}</span>
                                <small class="pic-phone">{{ $row->kode_sf ?? '-' }}</small>
                            </div>
                        </td>
                        <td>{{ $row->tanggal_ps ? \Carbon\Carbon::parse($row->tanggal_ps)->format('d M Y') : '-' }}</td>
                        <td class="text-center">
                            @if(in_array($rawStatus, ['complete', 'selesai', 'done'], true))
                                <span class="badge badge-success">QC Selesai</span>
                            @elseif(in_array($rawStatus, ['in progress', 'in_progress', 'proses'], true))
                                <span class="badge badge-warning">In Progress</span>
                            @else
                                <span class="badge badge-neutral">Belum Dihubungi</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($rawBilling === 'paham')
                                <span class="badge badge-success">Paham</span>
                            @elseif(in_array($rawBilling, ['perlu diedukasi', 'perlu edukasi'], true))
                                <span class="badge badge-danger-soft">Perlu Diedukasi</span>
                            @else
                                <span class="badge badge-neutral">Belum Dikonfirmasi</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center empty-cell">Tidak ada data laporan QC pada filter dan periode yang dipilih.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(method_exists($reportRows, 'hasPages') && $reportRows->hasPages())
        <div class="report-pagination-wrap no-print">
            {{ $reportRows->links() }}
        </div>
    @endif
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const loader = document.getElementById('topLoader');
    const filterForm = document.getElementById('reportFilterForm');

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

    document.querySelectorAll('.custom-select-wrapper').forEach(wrapper => {
        const trigger = wrapper.querySelector('.custom-select-trigger');
        const searchInput = wrapper.querySelector('.dropdown-search-input');
        const options = wrapper.querySelectorAll('.custom-option');
        const hiddenInput = document.getElementById(wrapper.dataset.targetInput);

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
                opt.style.display = opt.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });

        options.forEach(opt => {
            opt.addEventListener('click', (e) => {
                e.stopPropagation();
                if (hiddenInput) {
                    hiddenInput.value = opt.dataset.value ?? '';
                    closeAllPopovers();
                    startLoading();
                    filterForm?.submit();
                }
            });
        });
    });

    const monthWrapper = document.getElementById('monthPickerWrapper');
    const monthTrigger = document.getElementById('monthPickerTrigger');
    const yearDisplay  = document.getElementById('pickerYearDisplay');
    const periodeInput = document.getElementById('periode_ps');

    if (monthWrapper && monthTrigger && yearDisplay && periodeInput) {
        let currentPickerYear = parseInt(monthWrapper.dataset.initialYear || '{{ now()->format("Y") }}', 10);
        const activeMonth = monthWrapper.dataset.initialMonth || '{{ now()->format("m") }}';
        const initialYear = currentPickerYear;

        function refreshCells() {
            document.querySelectorAll('#monthGrid .month-cell').forEach(cell => {
                cell.classList.toggle('active', currentPickerYear === initialYear && cell.dataset.month === activeMonth);
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
            refreshCells();
        });

        document.getElementById('nextYearBtn')?.addEventListener('click', (e) => {
            e.stopPropagation();
            currentPickerYear++;
            yearDisplay.textContent = currentPickerYear;
            refreshCells();
        });

        document.querySelectorAll('#monthGrid .month-cell').forEach(cell => {
            cell.addEventListener('click', (e) => {
                e.stopPropagation();
                periodeInput.value = `${currentPickerYear}-${cell.dataset.month}`;
                closeAllPopovers();
                startLoading();
                filterForm?.submit();
            });
        });

        document.getElementById('selectCurrentMonthBtn')?.addEventListener('click', function (e) {
            e.stopPropagation();
            periodeInput.value = this.dataset.nowYm;
            closeAllPopovers();
            startLoading();
            filterForm?.submit();
        });
    }

    document.addEventListener('click', () => closeAllPopovers());
});
</script>
@endpush