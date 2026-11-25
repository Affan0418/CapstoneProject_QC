@extends('layouts.region')

@section('title', 'Monitoring Agency - Region Sumbagsel')

@section('content')
    @php
        $branches = $branches ?? collect();
        $agencyList = $agencyList ?? collect();
        $agencySummary = $agencySummary ?? [
            'total_agency' => 0,
            'agency_aktif' => 0,
            'avg_qc_pct' => 0,
            'top_agency_name' => '-',
            'top_agency_pct' => 0,
            'total_edukasi' => 0,
        ];

        $activeBranchId = request('branch_id');
        $activeStatus = request('status', '');
        $activeBranchName = $branches->firstWhere('id', (int) $activeBranchId)?->name ?? 'Semua Wilayah (Sumbagsel)';

        $rawPeriode = request('periode_ps', now()->format('Y-m'));
        if (!preg_match('/^\d{4}-\d{2}$/', $rawPeriode)) {
            $rawPeriode = now()->format('Y-m');
        }
        [$selectedYear, $selectedMonth] = explode('-', $rawPeriode);
        $monthNamesId = [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember',
        ];
        $formattedPeriodeLabel = ($monthNamesId[$selectedMonth] ?? 'Bulan') . ' ' . $selectedYear;
    @endphp

    <div class="top-loader" id="topLoader"></div>

    <!-- Hero Header -->
    <section class="hero-banner">
        <div class="hero-content">
            <div class="hero-badge-row">
                <span class="live-dot"></span>
                <span>Monitoring Agency (SPV) • {{ $activeBranchName }}</span>
            </div>
            <h1>Monitoring Kinerja Agency</h1>
            <p>Pemantauan kapasitas, kontak PIC, dan progres pelaksanaan QC pada seluruh <strong>Agency (SPV)</strong> di
                wilayah Sumbagsel.</p>
        </div>
        <div class="hero-graphic-card" aria-hidden="true">
            <div class="bar b1"></div>
            <div class="bar b2"></div>
            <div class="bar b3"></div>
            <div class="shield-badge">✓</div>
        </div>
    </section>

    <!-- Filter Bar -->
    <section class="card filter-card filter-elevated">
        <form method="GET" action="{{ url()->current() }}" class="filter-grid" id="agencyFilterForm">
            <!-- Filter Branch -->
            <div class="filter-group">
                <label>Area / Branch</label>
                <input type="hidden" name="branch_id" id="branch_id" value="{{ $activeBranchId }}">
                <div class="custom-select-wrapper" data-target-input="branch_id">
                    <button type="button" class="custom-select-trigger" aria-expanded="false">
                        <span class="trigger-left">
                            <span class="trigger-icon icon-soft-red">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.2">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                    <circle cx="12" cy="10" r="3" />
                                </svg>
                            </span>
                            <span
                                class="trigger-text">{{ $activeBranchId ? $activeBranchName : 'Semua Branch (Sumbagsel)' }}</span>
                        </span>
                        <svg class="chevron-icon" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2.2">
                            <polyline points="6 9 12 15 18 9" />
                        </svg>
                    </button>
                    <div class="custom-select-panel">
                        <div class="dropdown-search-box">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.2">
                                <circle cx="11" cy="11" r="8" />
                                <line x1="21" y1="21" x2="16.65" y2="16.65" />
                            </svg>
                            <input type="text" class="dropdown-search-input" placeholder="Cari branch...">
                        </div>
                        <ul class="custom-select-options">
                            <li class="custom-option {{ empty($activeBranchId) ? 'selected' : '' }}" data-value="">
                                <span>Semua Branch (Sumbagsel)</span>
                                @if (empty($activeBranchId))
                                    <span class="check-mark">✓</span>
                                @endif
                            </li>
                            @foreach ($branches as $branch)
                                @php $isSel = (string) $activeBranchId === (string) $branch->id; @endphp
                                <li class="custom-option {{ $isSel ? 'selected' : '' }}" data-value="{{ $branch->id }}">
                                    <span>{{ $branch->name }}</span>
                                    @if ($isSel)
                                        <span class="check-mark">✓</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Filter Status Agency -->
            <div class="filter-group">
                <label>Status Agency</label>
                <input type="hidden" name="status" id="status_input" value="{{ $activeStatus }}">
                <div class="custom-select-wrapper" data-target-input="status_input">
                    <button type="button" class="custom-select-trigger" aria-expanded="false">
                        <span class="trigger-left">
                            <span class="trigger-icon icon-soft-blue">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.2">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                                    <polyline points="22 4 12 14.01 9 11.01" />
                                </svg>
                            </span>
                            <span class="trigger-text">
                                @if ($activeStatus === 'active')
                                    Agency Aktif
                                @elseif($activeStatus === 'inactive')
                                    Agency Nonaktif
                                @else
                                    Semua Status
                                @endif
                            </span>
                        </span>
                        <svg class="chevron-icon" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2.2">
                            <polyline points="6 9 12 15 18 9" />
                        </svg>
                    </button>
                    <div class="custom-select-panel">
                        <ul class="custom-select-options">
                            <li class="custom-option {{ $activeStatus === '' ? 'selected' : '' }}" data-value="">
                                <span>Semua Status</span>
                                @if ($activeStatus === '')
                                    <span class="check-mark">✓</span>
                                @endif
                            </li>
                            <li class="custom-option {{ $activeStatus === 'active' ? 'selected' : '' }}"
                                data-value="active">
                                <span>Agency Aktif</span>
                                @if ($activeStatus === 'active')
                                    <span class="check-mark">✓</span>
                                @endif
                            </li>
                            <li class="custom-option {{ $activeStatus === 'inactive' ? 'selected' : '' }}"
                                data-value="inactive">
                                <span>Agency Nonaktif</span>
                                @if ($activeStatus === 'inactive')
                                    <span class="check-mark">✓</span>
                                @endif
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Custom Calendar Month Picker -->
            <div class="filter-group">
                <label>Periode PS (Bulan)</label>
                <input type="hidden" name="periode_ps" id="periode_ps" value="{{ $rawPeriode }}">
                <div class="custom-month-wrapper" id="monthPickerWrapper" data-initial-year="{{ $selectedYear }}"
                    data-initial-month="{{ $selectedMonth }}">
                    <button type="button" class="custom-select-trigger" id="monthPickerTrigger" aria-expanded="false">
                        <span class="trigger-left">
                            <span class="trigger-icon icon-soft-amber">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2.2">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                                    <line x1="16" y1="2" x2="16" y2="6" />
                                    <line x1="8" y1="2" x2="8" y2="6" />
                                    <line x1="3" y1="10" x2="21" y2="10" />
                                </svg>
                            </span>
                            <span class="trigger-text" id="monthPickerLabel">{{ $formattedPeriodeLabel }}</span>
                        </span>
                        <svg class="chevron-icon" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2.2">
                            <polyline points="6 9 12 15 18 9" />
                        </svg>
                    </button>

                    <div class="month-picker-panel" id="monthPickerPanel">
                        <div class="month-picker-header">
                            <button type="button" class="year-nav-btn" id="prevYearBtn" title="Tahun Sebelumnya">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2.4">
                                    <polyline points="15 18 9 12 15 6" />
                                </svg>
                            </button>
                            <span class="year-display" id="pickerYearDisplay">{{ $selectedYear }}</span>
                            <button type="button" class="year-nav-btn" id="nextYearBtn" title="Tahun Berikutnya">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2.4">
                                    <polyline points="9 18 15 12 9 6" />
                                </svg>
                            </button>
                        </div>

                        <div class="month-grid" id="monthGrid">
                            @php
                                $shortMonths = [
                                    '01' => 'Jan',
                                    '02' => 'Feb',
                                    '03' => 'Mar',
                                    '04' => 'Apr',
                                    '05' => 'Mei',
                                    '06' => 'Jun',
                                    '07' => 'Jul',
                                    '08' => 'Agu',
                                    '09' => 'Sep',
                                    '10' => 'Okt',
                                    '11' => 'Nov',
                                    '12' => 'Des',
                                ];
                            @endphp
                            @foreach ($shortMonths as $mNum => $mShort)
                                <button type="button" class="month-cell {{ $selectedMonth === $mNum ? 'active' : '' }}"
                                    data-month="{{ $mNum }}" data-fullname="{{ $monthNamesId[$mNum] }}">
                                    {{ $mShort }}
                                </button>
                            @endforeach
                        </div>

                        <div class="month-picker-footer">
                            <button type="button" class="btn-this-month" id="selectCurrentMonthBtn"
                                data-now-ym="{{ now()->format('Y-m') }}">
                                Bulan Ini ({{ $monthNamesId[now()->format('m')] }} {{ now()->format('Y') }})
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="filter-actions">
                @if (request()->hasAny(['branch_id', 'status', 'periode_ps']))
                    <a href="{{ url()->current() }}" class="btn btn-outline btn-reset-pill">Reset Filter</a>
                @else
                    <span class="filter-hint-badge">Filter Otomatis</span>
                @endif
            </div>
        </form>
    </section>

    <!-- 4 KPI Cards Agency -->
    <section class="kpi-grid">
        <div class="card kpi-card interactive-card spotlight-card" data-kpi="total">
            <span class="kpi-ink-wave" aria-hidden="true"></span>
            <div class="kpi-card-inner">
                <div class="kpi-top-row">
                    <div class="kpi-header">
                        <div class="kpi-icon icon-red">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <rect x="2" y="7" width="20" height="14" rx="2" ry="2" />
                                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16" />
                            </svg>
                        </div>
                        <span class="kpi-label">Agency Terdaftar</span>
                    </div>
                    <span class="kpi-pill pill-red">{{ $agencySummary['agency_aktif'] }} Aktif</span>
                </div>
                <div class="kpi-value count-up" data-target="{{ (int) $agencySummary['total_agency'] }}">
                    {{ number_format($agencySummary['total_agency'], 0, ',', '.') }}
                </div>
                <div class="kpi-sub">Total mitra Agency (SPV) pada wilayah</div>
            </div>
        </div>

        <div class="card kpi-card interactive-card spotlight-card" data-kpi="selesai">
            <span class="kpi-ink-wave" aria-hidden="true"></span>
            <div class="kpi-card-inner">
                <div class="kpi-top-row">
                    <div class="kpi-header">
                        <div class="kpi-icon icon-green">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                                <polyline points="22 4 12 14.01 9 11.01" />
                            </svg>
                        </div>
                        <span class="kpi-label">Rata-rata QC Selesai</span>
                    </div>
                    <span class="kpi-pill pill-green">{{ $agencySummary['avg_qc_pct'] }}%</span>
                </div>
                <div class="kpi-value">{{ $agencySummary['avg_qc_pct'] }}%</div>
                <div class="kpi-sub">Rata-rata penyelesaian QC seluruh Agency</div>
            </div>
        </div>

        <div class="card kpi-card interactive-card spotlight-card" data-kpi="belum">
            <span class="kpi-ink-wave" aria-hidden="true"></span>
            <div class="kpi-card-inner">
                <div class="kpi-top-row">
                    <div class="kpi-header">
                        <div class="kpi-icon icon-blue">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <polygon
                                    points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                            </svg>
                        </div>
                        <span class="kpi-label">Performa Tertinggi</span>
                    </div>
                    <span class="kpi-pill pill-blue">{{ $agencySummary['top_agency_pct'] }}%</span>
                </div>
                <div class="kpi-value kpi-value-text" title="{{ $agencySummary['top_agency_name'] }}">
                    {{ \Illuminate\Support\Str::limit($agencySummary['top_agency_name'], 18) }}
                </div>
                <div class="kpi-sub">Capaian penyelesaian QC tertinggi</div>
            </div>
        </div>

        <div class="card kpi-card interactive-card spotlight-card" data-kpi="progress">
            <span class="kpi-ink-wave" aria-hidden="true"></span>
            <div class="kpi-card-inner">
                <div class="kpi-top-row">
                    <div class="kpi-header">
                        <div class="kpi-icon icon-amber">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <circle cx="12" cy="12" r="10" />
                                <line x1="12" y1="8" x2="12" y2="12" />
                                <line x1="12" y1="16" x2="12.01" y2="16" />
                            </svg>
                        </div>
                        <span class="kpi-label">Perlu Edukasi Billing</span>
                    </div>
                    <span class="kpi-pill pill-amber">Follow-up</span>
                </div>
                <div class="kpi-value count-up" data-target="{{ (int) $agencySummary['total_edukasi'] }}">
                    {{ number_format($agencySummary['total_edukasi'], 0, ',', '.') }}
                </div>
                <div class="kpi-sub">Total pelanggan butuh edukasi tagihan</div>
            </div>
        </div>
    </section>

    <!-- Tabel Utama Daftar & Kinerja Agency -->
    <section class="card section-card table-section spotlight-card">
        <div class="card-header header-with-filter">
            <div class="card-title-wrap">
                <span class="title-accent"></span>
                <div>
                    <h2>Daftar & Progres QC per Agency</h2>
                    <p>Klik tombol <strong>Detail</strong> untuk melihat rincian PIC, distribusi call, dan pemahaman billing
                    </p>
                </div>
            </div>

            <div class="inline-sales-filter">
                <div class="search-input-icon-wrap">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.2">
                        <circle cx="11" cy="11" r="8" />
                        <line x1="21" y1="21" x2="16.65" y2="16.65" />
                    </svg>
                    <input type="text" id="liveAgencyDirectorySearch"
                        placeholder="Cari Nama Agency, PIC, atau Branch..."
                        class="form-control form-control-sm has-left-icon">
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table" id="agencyDirectoryTable">
                <thead>
                    <tr>
                        <th class="sortable-th" data-col="0" data-type="text">Nama Agency <span
                                class="sort-icon">⇅</span></th>
                        <th class="sortable-th" data-col="1" data-type="text">PIC & Kontak <span
                                class="sort-icon">⇅</span></th>
                        <th class="sortable-th" data-col="2" data-type="text">Branch Terkait <span
                                class="sort-icon">⇅</span></th>
                        <th class="text-center sortable-th" data-col="3" data-type="text">Status <span
                                class="sort-icon">⇅</span></th>
                        <th class="text-right sortable-th" data-col="4" data-type="num">Total Pelanggan <span
                                class="sort-icon">⇅</span></th>
                        <th class="text-right sortable-th" data-col="5" data-type="num">QC Selesai <span
                                class="sort-icon">⇅</span></th>
                        <th class="text-right sortable-th" data-col="6" data-type="num">Perlu Edukasi <span
                                class="sort-icon">⇅</span></th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($agencyList as $row)
                        @php
                            $qcPct = (float) ($row->qc_selesai_pct ?? 0);
                        @endphp
                        <tr class="searchable-directory-row">
                            <td class="font-semibold">{{ $row->name ?? '-' }}</td>
                            <td>
                                <div class="pic-cell">
                                    <span class="pic-name">{{ $row->pic_name ?? '-' }}</span>
                                    <small class="pic-phone">{{ $row->phone ?? 'No. HP belum diisi' }}</small>
                                </div>
                            </td>
                            <td><span class="badge badge-neutral">{{ $row->branch_names ?? '-' }}</span></td>
                            <td class="text-center">
                                @if (!empty($row->is_active))
                                    <span class="badge badge-success">Aktif</span>
                                @else
                                    <span class="badge badge-danger-soft">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-right" data-val="{{ (int) ($row->total_pelanggan ?? 0) }}">
                                {{ number_format($row->total_pelanggan ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="text-right" data-val="{{ $qcPct }}">
                                <div class="inline-bar-cell">
                                    <div class="mini-track">
                                        <div class="mini-fill" style="width: {{ min(100, $qcPct) }}%;"></div>
                                    </div>
                                    <span class="font-semibold">{{ $qcPct }}%</span>
                                </div>
                            </td>
                            <td class="text-right" data-val="{{ (int) ($row->perlu_edukasi ?? 0) }}">
                                <span
                                    class="badge {{ ($row->perlu_edukasi ?? 0) > 0 ? 'badge-warning' : 'badge-success' }}">
                                    {{ number_format($row->perlu_edukasi ?? 0, 0, ',', '.') }}
                                </span>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn-detail-row open-agency-drawer"
                                    data-name="{{ $row->name ?? '-' }}" data-pic="{{ $row->pic_name ?? '-' }}"
                                    data-phone="{{ $row->phone ?? '-' }}"
                                    data-branches="{{ $row->branch_names ?? '-' }}"
                                    data-status="{{ !empty($row->is_active) ? 'Aktif' : 'Nonaktif' }}"
                                    data-total="{{ number_format($row->total_pelanggan ?? 0, 0, ',', '.') }}"
                                    data-selesai="{{ number_format($row->qc_selesai ?? 0, 0, ',', '.') }}"
                                    data-selesai-pct="{{ $qcPct }}"
                                    data-progress="{{ number_format($row->qc_in_progress ?? 0, 0, ',', '.') }}"
                                    data-belum="{{ number_format($row->belum_dihubungi ?? 0, 0, ',', '.') }}"
                                    data-paham="{{ number_format($row->paham_billing ?? 0, 0, ',', '.') }}"
                                    data-edukasi="{{ number_format($row->perlu_edukasi ?? 0, 0, ',', '.') }}">
                                    Detail
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center empty-cell">Belum ada data Agency untuk filter yang
                                dipilih.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <!-- Slide-Over Detail Drawer Agency -->
    <div class="drawer-backdrop" id="agencyDrawerBackdrop" hidden></div>
    <aside class="agency-drawer" id="agencyDetailDrawer" aria-hidden="true">
        <div class="drawer-header">
            <div>
                <span class="drawer-eyebrow">Rincian Kinerja Agency</span>
                <h3 id="drawerAgencyName">-</h3>
            </div>
            <button type="button" class="drawer-close-btn" id="closeAgencyDrawerBtn" aria-label="Tutup">✕</button>
        </div>

        <div class="drawer-body">
            <div class="drawer-info-card">
                <div class="info-pair">
                    <span>PIC Agency</span>
                    <strong id="drawerPicName">-</strong>
                </div>
                <div class="info-pair">
                    <span>Nomor Telepon</span>
                    <strong id="drawerPhone">-</strong>
                </div>
                <div class="info-pair">
                    <span>Wilayah Branch</span>
                    <strong id="drawerBranches">-</strong>
                </div>
                <div class="info-pair">
                    <span>Status Operasional</span>
                    <strong id="drawerStatus">-</strong>
                </div>
            </div>

            <h4 class="drawer-section-title">Rekapitulasi Status QC ({{ $formattedPeriodeLabel }})</h4>
            <div class="drawer-stat-grid">
                <div class="drawer-stat-box">
                    <span>Total Pelanggan</span>
                    <strong id="drawerTotal">0</strong>
                </div>
                <div class="drawer-stat-box stat-green">
                    <span>QC Selesai (<em id="drawerSelesaiPct">0%</em>)</span>
                    <strong id="drawerSelesai">0</strong>
                </div>
                <div class="drawer-stat-box stat-amber">
                    <span>QC In Progress</span>
                    <strong id="drawerProgress">0</strong>
                </div>
                <div class="drawer-stat-box stat-blue">
                    <span>Belum Dihubungi</span>
                    <strong id="drawerBelum">0</strong>
                </div>
            </div>

            <h4 class="drawer-section-title">Pemahaman Billing Pelanggan</h4>
            <div class="drawer-stat-grid">
                <div class="drawer-stat-box stat-green">
                    <span>Sudah Paham</span>
                    <strong id="drawerPaham">0</strong>
                </div>
                <div class="drawer-stat-box stat-amber">
                    <span>Perlu Diedukasi</span>
                    <strong id="drawerEdukasi">0</strong>
                </div>
            </div>
        </div>
    </aside>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loader = document.getElementById('topLoader');
            const filterForm = document.getElementById('agencyFilterForm');

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

            // Custom Dropdowns
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

                searchInput?.addEventListener('input', function() {
                    const q = this.value.toLowerCase().trim();
                    options.forEach(opt => {
                        opt.style.display = opt.textContent.toLowerCase().includes(q) ? '' :
                            'none';
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

            // Custom Month Picker
            const monthWrapper = document.getElementById('monthPickerWrapper');
            const monthTrigger = document.getElementById('monthPickerTrigger');
            const yearDisplay = document.getElementById('pickerYearDisplay');
            const periodeInput = document.getElementById('periode_ps');

            if (monthWrapper && monthTrigger && yearDisplay && periodeInput) {
                let currentPickerYear = parseInt(monthWrapper.dataset.initialYear || '{{ now()->format('Y') }}',
                    10);
                const activeMonth = monthWrapper.dataset.initialMonth || '{{ now()->format('m') }}';
                const initialYear = currentPickerYear;

                function refreshMonthCellsHighlight() {
                    document.querySelectorAll('#monthGrid .month-cell').forEach(cell => {
                        cell.classList.toggle('active', currentPickerYear === initialYear && cell.dataset
                            .month === activeMonth);
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
                        periodeInput.value = `${currentPickerYear}-${cell.dataset.month}`;
                        closeAllPopovers();
                        startLoading();
                        filterForm?.submit();
                    });
                });

                document.getElementById('selectCurrentMonthBtn')?.addEventListener('click', function(e) {
                    e.stopPropagation();
                    periodeInput.value = this.dataset.nowYm;
                    closeAllPopovers();
                    startLoading();
                    filterForm?.submit();
                });
            }

            document.addEventListener('click', () => closeAllPopovers());

            // Live Search & Sort Tabel
            document.getElementById('liveAgencyDirectorySearch')?.addEventListener('input', function() {
                const q = this.value.toLowerCase().trim();
                document.querySelectorAll('.searchable-directory-row').forEach(row => {
                    row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
                });
            });

            const table = document.getElementById('agencyDirectoryTable');
            if (table) {
                let sortDir = {};
                table.querySelectorAll('th.sortable-th').forEach(th => {
                    th.addEventListener('click', () => {
                        const colIdx = parseInt(th.dataset.col, 10);
                        const type = th.dataset.type || 'text';
                        const asc = !sortDir[colIdx];
                        sortDir = {
                            [colIdx]: asc
                        };

                        const tbody = table.querySelector('tbody');
                        const rows = Array.from(tbody.querySelectorAll('tr')).filter(r => r.children
                            .length > 1);
                        rows.sort((a, b) => {
                            const cellA = a.children[colIdx];
                            const cellB = b.children[colIdx];
                            if (type === 'num') {
                                const valA = parseFloat(cellA.dataset.val || '0');
                                const valB = parseFloat(cellB.dataset.val || '0');
                                return asc ? valA - valB : valB - valA;
                            }
                            return asc ?
                                cellA.textContent.trim().localeCompare(cellB.textContent
                                    .trim()) :
                                cellB.textContent.trim().localeCompare(cellA.textContent
                                    .trim());
                        });
                        rows.forEach(r => tbody.appendChild(r));
                    });
                });
            }

            // Slide-Over Drawer Detail Agency
            const drawer = document.getElementById('agencyDetailDrawer');
            const backdrop = document.getElementById('agencyDrawerBackdrop');
            const closeBtn = document.getElementById('closeAgencyDrawerBtn');

            function openDrawer(btn) {
                document.getElementById('drawerAgencyName').textContent = btn.dataset.name;
                document.getElementById('drawerPicName').textContent = btn.dataset.pic;
                document.getElementById('drawerPhone').textContent = btn.dataset.phone;
                document.getElementById('drawerBranches').textContent = btn.dataset.branches;
                document.getElementById('drawerStatus').textContent = btn.dataset.status;
                document.getElementById('drawerTotal').textContent = btn.dataset.total;
                document.getElementById('drawerSelesai').textContent = btn.dataset.selesai;
                document.getElementById('drawerSelesaiPct').textContent = btn.dataset.selesaiPct + '%';
                document.getElementById('drawerProgress').textContent = btn.dataset.progress;
                document.getElementById('drawerBelum').textContent = btn.dataset.belum;
                document.getElementById('drawerPaham').textContent = btn.dataset.paham;
                document.getElementById('drawerEdukasi').textContent = btn.dataset.edukasi;

                backdrop.hidden = false;
                drawer.classList.add('open');
                drawer.setAttribute('aria-hidden', 'false');
            }

            function closeDrawer() {
                drawer.classList.remove('open');
                drawer.setAttribute('aria-hidden', 'true');
                backdrop.hidden = true;
            }

            document.querySelectorAll('.open-agency-drawer').forEach(btn => {
                btn.addEventListener('click', () => openDrawer(btn));
            });
            closeBtn?.addEventListener('click', closeDrawer);
            backdrop?.addEventListener('click', closeDrawer);
        });
    </script>
@endpush
