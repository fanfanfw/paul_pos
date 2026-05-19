<x-app-layout>
    <x-slot name="header">Dashboard Admin</x-slot>

    <div class="space-y-6">
        <section class="flex flex-col gap-3 rounded-2xl border border-base-300 bg-base-100 p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-primary">Admin</p>
                <h2 class="mt-2 text-xl font-bold text-base-content">Dashboard Admin</h2>
                <p class="mt-1 text-sm text-base-content/60">Fondasi dashboard siap. Data penjualan, produk, stok, dan laporan akan masuk pada phase berikutnya.</p>
            </div>
            <span class="badge badge-primary badge-lg">Base UI</span>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach (['Penjualan Hari Ini', 'Transaksi Hari Ini', 'Produk Aktif', 'Stok Menipis'] as $label)
                <div class="stat rounded-xl border border-base-300 bg-base-100 shadow-sm">
                    <div class="stat-title text-xs text-base-content/60">{{ $label }}</div>
                    <div class="stat-value money-value text-2xl font-bold">-</div>
                    <div class="stat-desc text-xs">Tersedia setelah data POS dibuat</div>
                </div>
            @endforeach
        </section>
    </div>
</x-app-layout>
