@php
    $user = auth()->user();
    $target = $user ? ($user->isAdmin() ? route('admin.dashboard') : route('kasir.dashboard')) : route('login');
@endphp

<!DOCTYPE html>
<html lang="id" data-theme="kasirku">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Akses tidak diizinkan</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-base-200 font-sans text-base-content antialiased">
    <main class="flex min-h-screen items-center justify-center p-6">
        <section class="w-full max-w-md rounded-3xl border border-base-300 bg-base-100 p-8 text-center shadow-sm">
            <p class="product-code text-xs font-bold text-error">403</p>
            <h1 class="mt-2 text-2xl font-bold">Akses tidak diizinkan</h1>
            <p class="mt-3 text-sm text-base-content/65">Akun Anda tidak memiliki izin untuk membuka halaman ini.</p>
            <a href="{{ $target }}" class="btn btn-primary mt-6">Kembali ke Area Saya</a>
        </section>
    </main>
</body>
</html>
