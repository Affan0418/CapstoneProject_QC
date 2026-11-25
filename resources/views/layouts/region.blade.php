<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard Monitoring Region') - QC Billco</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/region-dashboard.css') }}">
    @stack('styles')
</head>
<body>
    <div class="app-shell">
        <!-- Sidebar Region -->
        <aside class="sidebar" id="regionSidebar">
            <div class="sidebar-brand">
                <div class="brand-logo">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        <path d="m9 12 2 2 4-4"/>
                    </svg>
                </div>
                <div class="brand-text">
                    <span class="brand-title">QC Monitoring</span>
                    <span class="brand-subtitle">Telkomsel Region Sumbagsel</span>
                </div>
            </div>

            <nav class="sidebar-nav">
                <a href="{{ Route::has('region.dashboard') ? route('region.dashboard') : '#' }}"
                   class="nav-item {{ request()->routeIs('region.dashboard') ? 'active' : '' }}">
                    <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>
                    <span>Dashboard</span>
                </a>

                <a href="{{ Route::has('region.agencies.index') ? route('region.agencies.index') : '#' }}"
                   class="nav-item {{ request()->routeIs('region.agencies.*') ? 'active' : '' }}">
                    <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
                    </svg>
                    <span>Monitoring Agency</span>
                </a>

                <a href="{{ Route::has('region.reports.index') ? route('region.reports.index') : '#' }}"
                   class="nav-item {{ request()->routeIs('region.reports.*') ? 'active' : '' }}">
                    <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>
                    </svg>
                    <span>Laporan</span>
                </a>
            </nav>

            <div class="sidebar-footer-wave">
                <form method="POST" action="{{ Route::has('logout') ? route('logout') : '#' }}">
                    @csrf
                    <button type="submit" class="btn-logout">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
                        </svg>
                        <span>Keluar</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Area -->
        <div class="main-wrapper">
            <header class="topbar">
                <div class="topbar-left">
                    <button type="button" class="mobile-menu-btn" id="toggleSidebarBtn" aria-label="Buka Menu">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/>
                        </svg>
                    </button>
                    <div class="topbar-breadcrumb">
                        <span class="region-pill">Region Sumbagsel</span>
                    </div>
                </div>

                <div class="topbar-right">
                    <div class="user-profile">
                        <div class="user-avatar">
                            {{ strtoupper(substr(auth()->user()->name ?? 'RG', 0, 2)) }}
                        </div>
                        <div class="user-meta">
                            <span class="user-name">{{ auth()->user()->name ?? 'User Region' }}</span>
                            <span class="user-role">Region</span>
                        </div>
                    </div>
                </div>
            </header>

            <main class="content-container">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script>
        document.getElementById('toggleSidebarBtn')?.addEventListener('click', function () {
            document.getElementById('regionSidebar')?.classList.toggle('open');
        });
    </script>
    @stack('scripts')
</body>
</html>