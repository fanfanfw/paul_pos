<x-app-layout>
    <x-slot name="header">Dashboard Kasir</x-slot>

    <div class="space-y-6">
        <section class="rounded-2xl border border-base-300 bg-base-100 p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-secondary">Kasir</p>
            <h2 class="mt-2 text-xl font-bold text-base-content">Selamat bekerja, {{ auth()->user()->name }}</h2>
            <p class="mt-1 text-sm text-base-content/60">Area kerja kasir sudah siap untuk transaksi penjualan.</p>
            <a href="{{ route('kasir.transactions.create') }}" class="btn btn-primary btn-sm mt-5">Mulai Transaksi Baru</a>
        </section>

        <section class="grid gap-4 md:grid-cols-2">
            <div class="stat rounded-xl border border-base-300 bg-base-100 shadow-sm">
                <div class="stat-title text-xs text-base-content/60">Penjualanku Hari Ini</div>
                <div class="stat-value money-value text-2xl font-bold">-</div>
                <div class="stat-desc text-xs">Tersedia setelah transaksi dibuat</div>
            </div>

            <div class="stat rounded-xl border border-base-300 bg-base-100 shadow-sm">
                <div class="stat-title text-xs text-base-content/60">Transaksi Hari Ini</div>
                <div class="stat-value money-value text-2xl font-bold">-</div>
                <div class="stat-desc text-xs">Tersedia setelah transaksi dibuat</div>
            </div>
        </section>
    </div>
</x-app-layout>
