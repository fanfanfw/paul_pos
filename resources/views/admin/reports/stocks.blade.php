<x-app-layout>
    <x-slot name="header">Laporan Stok</x-slot>

    <div class="space-y-5">
        <section class="filter-card rounded-2xl p-5">
            <div class="mb-4">
                <h2 class="text-xl font-bold text-base-content">Laporan Stok</h2>
                <p class="text-sm text-base-content/60">Pantau stok aman, menipis, dan habis.</p>
            </div>
            <form method="GET" class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <select name="status" class="select select-bordered select-sm w-full sm:max-w-xs">
                    <option value="all" @selected($status === 'all')>Semua status</option>
                    <option value="aman" @selected($status === 'aman')>Aman</option>
                    <option value="menipis" @selected($status === 'menipis')>Menipis</option>
                    <option value="habis" @selected($status === 'habis')>Habis</option>
                </select>
                <button class="btn btn-primary btn-sm">Filter</button>
                <a href="{{ route('admin.reports.stocks') }}" class="btn btn-ghost btn-sm">Reset</a>
            </form>
        </section>

        <section class="grid gap-4 md:grid-cols-3">
            <div class="stat rounded-xl border border-base-300 bg-base-100 shadow-sm"><div class="stat-title text-xs">Total Produk</div><div class="stat-value money-value text-2xl">{{ $summary['total_products'] }}</div></div>
            <div class="stat rounded-xl border border-base-300 bg-base-100 shadow-sm"><div class="stat-title text-xs">Stok Menipis</div><div class="stat-value money-value text-2xl">{{ $summary['low_stock'] }}</div></div>
            <div class="stat rounded-xl border border-base-300 bg-base-100 shadow-sm"><div class="stat-title text-xs">Stok Habis</div><div class="stat-value money-value text-2xl">{{ $summary['out_of_stock'] }}</div></div>
        </section>

        <section class="overflow-x-auto rounded-2xl border border-base-300 bg-base-100 shadow-sm">
            <table class="table table-sm">
                <thead><tr><th>Kode</th><th>Produk</th><th>Kategori</th><th>Stok</th><th>Min</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse ($products as $product)
                        @php
                            $quantity = $product->stock?->quantity ?? 0;
                            $min = $product->stock?->min_quantity ?? 0;
                            $rowStatus = $quantity === 0 ? 'habis' : ($quantity <= $min ? 'menipis' : 'aman');
                            $badge = ['aman' => 'badge-success', 'menipis' => 'badge-warning', 'habis' => 'badge-error'][$rowStatus];
                        @endphp
                        <tr>
                            <td class="product-code text-xs">{{ $product->code }}</td>
                            <td class="font-semibold">{{ $product->name }}</td>
                            <td>{{ $product->category?->name ?? '-' }}</td>
                            <td class="money-value">{{ $quantity }}</td>
                            <td>{{ $min }}</td>
                            <td><span class="badge {{ $badge }} badge-sm">{{ ucfirst($rowStatus) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-14 text-center text-sm text-base-content/60">Tidak ada produk sesuai filter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        {{ $products->links() }}
    </div>
</x-app-layout>
