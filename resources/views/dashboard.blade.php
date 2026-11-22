<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Capstone Project QC</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen">

    <!-- Navbar -->
    <nav class="bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <span class="text-lg font-bold text-blue-600">QC System</span>
            <span class="text-slate-300">|</span>
            <span class="text-sm text-slate-600 font-medium">Dashboard Utama</span>
        </div>

        <div class="flex items-center space-x-4">
            <div class="text-right">
                <p class="text-sm font-semibold text-slate-700">{{ Auth::user()->name }}</p>
                <span class="inline-block text-[11px] font-medium uppercase tracking-wide px-2 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-200">
                    {{ str_replace('_', ' ', Auth::user()->role) }}
                </span>
            </div>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button
                    type="submit"
                    class="bg-red-50 hover:bg-red-100 text-red-600 text-sm font-medium px-3.5 py-1.5 rounded-lg border border-red-200 transition cursor-pointer"
                >
                    Logout
                </button>
            </form>
        </div>
    </nav>

    <!-- Konten Utama -->
    <main class="max-w-6xl mx-auto p-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
            <h2 class="text-xl font-bold text-slate-800">Selamat Datang, {{ Auth::user()->name }}</h2>
            <p class="text-sm text-slate-500 mt-1">
                Login sebagai <strong>{{ Auth::user()->email }}</strong> dengan hak akses
                <span class="font-semibold text-blue-600">{{ Auth::user()->role }}</span>.
            </p>
        </div>

        <!-- Ringkasan Modul Database -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Branches & Agencies</p>
                <p class="text-2xl font-bold text-slate-800 mt-2">Master Data</p>
                <p class="text-xs text-slate-500 mt-1">Cabang & agensi terdaftar</p>
            </div>

            <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Sales & Customers</p>
                <p class="text-2xl font-bold text-slate-800 mt-2">Data Klien</p>
                <p class="text-xs text-slate-500 mt-1">Manajemen sales & pelanggan</p>
            </div>

            <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">QC Records</p>
                <p class="text-2xl font-bold text-blue-600 mt-2">Quality Control</p>
                <p class="text-xs text-slate-500 mt-1">Rekap data pemeriksaan QC</p>
            </div>

            <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">QC Calls</p>
                <p class="text-2xl font-bold text-emerald-600 mt-2">Log Panggilan</p>
                <p class="text-xs text-slate-500 mt-1">Riwayat panggilan verifikasi</p>
            </div>
        </div>
    </main>

</body>
</html>