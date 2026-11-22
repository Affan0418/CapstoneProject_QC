<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Capstone Project QC</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-100 flex items-center justify-center min-h-screen p-4">

    <div class="w-full max-w-md bg-white rounded-xl shadow-md p-8">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-slate-800">Selamat Datang Di QC</h1>
            <p class="text-sm text-slate-500 mt-1">Silakan masuk untuk mengakses dashboard</p>
        </div>

        @if ($errors->any())
        <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-600">
            {{ $errors->first() }}
        </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                <input
                    type="email"
                    name="email"
                    id="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    placeholder="admin@qc.com"
                    class="w-full px-3.5 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                <input
                    type="password"
                    name="password"
                    id="password"
                    required
                    placeholder="••••••••"
                    class="w-full px-3.5 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div class="flex items-center justify-between text-sm">
                <label class="inline-flex items-center text-slate-600 cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 mr-2">
                    Ingat saya
                </label>
            </div>

            <button
                type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 px-4 rounded-lg text-sm transition duration-150 cursor-pointer">
                Masuk ke Dashboard
            </button>
        </form>
    </div>

</body>

</html>