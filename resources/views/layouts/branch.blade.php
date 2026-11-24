.<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Dashboard Branch') | QC Monitor</title>

    <link rel="stylesheet" href="{{ asset('css/branch-dashboard.css') }}">

    @stack('styles')
</head>
<body>
    @php
        $customerMenu = [
            [
                'label' => 'Data Pelanggan',
                'route' => 'branch.customers.index',
            ],
            [
                'label' => 'Tambah Pelanggan',
                'route' => 'branch.customers.create',
            ],
            [
                'label' => 'Import Excel',
                'route' => 'branch.customers.import',
            ],
        ];
    @endphp

    <div class="app-shell">
        <aside class="sidebar">
            <a href="{{ route('branch.dashboard') }}" class="brand">
                <span class="brand-mark" aria-hidden="true">Q</span>

                <span>
                    <span class="brand-name">QC Monitor</span>
                    <span class="brand-caption">Sumbagsel Operations</span>
                </span>
            </a>

            <div class="workspace-label">WORKSPACE BRANCH</div>

            <nav class="sidebar-nav" aria-label="Navigasi Branch">
                <a
                    href="{{ route('branch.dashboard') }}"
                    class="nav-link {{ request()->routeIs('branch.dashboard') ? 'active' : '' }}"
                    @if(request()->routeIs('branch.dashboard')) aria-current="page" @endif
                >
                    <span class="nav-dot" aria-hidden="true"></span>
                    Dashboard
                </a>

                <div class="nav-section">PELANGGAN</div>

                @foreach($customerMenu as $item)
                    @if(\Illuminate\Support\Facades\Route::has($item['route']))
                        <a
                            href="{{ route($item['route']) }}"
                            class="nav-link {{ request()->routeIs($item['route']) ? 'active' : '' }}"
                        >
                            <span class="nav-dot" aria-hidden="true"></span>
                            {{ $item['label'] }}
                        </a>
                    @else
                        <span class="nav-link nav-disabled" aria-disabled="true">
                            <span class="nav-dot" aria-hidden="true"></span>
                            {{ $item['label'] }}
                            <span class="soon-tag">Segera</span>
                        </span>
                    @endif
                @endforeach
            </nav>

            <div class="sidebar-footer">
                <span class="sidebar-footer-label">Mode akses</span>
                <span>Admin Branch</span>
                <p>Data dibatasi sesuai cabang akun Anda.</p>
            </div>
        </aside>

        <div class="main-shell">
            <header class="topbar">
                <div>
                    <div class="topbar-title">Quality Control Monitoring</div>
                    <div class="muted small">Workspace pengelolaan cabang</div>
                </div>

                <div class="topbar-user">
                    <span class="avatar" aria-hidden="true">
                        {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                    </span>

                    <div class="user-detail">
                        <span class="user-name">{{ auth()->user()->name }}</span>
                        <span class="muted small">Admin Branch</span>
                    </div>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-ghost">
                            Keluar
                        </button>
                    </form>
                </div>
            </header>

            <main class="main-content">
                <nav class="breadcrumb" aria-label="Breadcrumb">
                    <a href="{{ route('branch.dashboard') }}">Branch</a>
                    <span aria-hidden="true">/</span>
                    <span>@yield('breadcrumb', 'Dashboard')</span>
                </nav>

                @if(session('success'))
                    <div class="notice notice-success" role="status">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="notice notice-error" role="alert">
                        {{ session('error') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="notice notice-error" role="alert">
                        <p>Periksa kembali data berikut:</p>

                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')

                <footer class="page-footer">
                    QC Monitoring System · Sumbagsel
                </footer>
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>