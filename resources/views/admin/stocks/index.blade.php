<x-app-layout>
    <x-slot name="header">Stok</x-slot>

    <div class="space-y-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-base-content">Stok</h2>
                <p class="text-sm text-base-content/60">Pantau stok dan lakukan penyesuaian manual.</p>
            </div>
            <a href="{{ route('admin.stocks.movements') }}" class="btn btn-outline btn-sm">Riwayat Pergerakan</a>
        </div>

        <form method="GET" class="filter-card rounded-xl p-4">
            <div class="grid gap-3 lg:grid-cols-[1fr_180px_auto]">
                <input type="search" name="search" value="{{ request('search') }}" class="input input-bordered input-sm w-full" placeholder="Cari produk atau kode">
                <select name="status" class="select select-bordered select-sm w-full">
                    <option value="">Semua status</option>
                    <option value="aman" @selected(request('status') === 'aman')>Aman</option>
                    <option value="menipis" @selected(request('status') === 'menipis')>Menipis</option>
                    <option value="habis" @selected(request('status') === 'habis')>Habis</option>
                </select>
                <div class="flex gap-2">
                    <button class="btn btn-primary btn-sm">Filter</button>
                    <a href="{{ route('admin.stocks.index') }}" class="btn btn-ghost btn-sm">Reset</a>
                </div>
            </div>
        </form>

        <div class="overflow-x-auto rounded-xl border border-base-300 bg-base-100 shadow-sm">
            <table class="table table-sm">
                <thead class="bg-base-200">
                    <tr>
                        <th class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Kode</th>
                        <th class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Produk</th>
                        <th class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Stok</th>
                        <th class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Min</th>
                        <th class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Status</th>
                        <th class="text-right text-xs font-semibold uppercase tracking-wide text-base-content/60">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($stocks as $stock)
                        @php
                            $status = $stock->quantity === 0 ? 'habis' : ($stock->quantity <= $stock->min_quantity ? 'menipis' : 'aman');
                            $badge = ['aman' => 'badge-success', 'menipis' => 'badge-warning', 'habis' => 'badge-error'][$status];
                        @endphp
                        <tr class="hover:bg-base-200/70">
                            <td class="product-code text-xs">{{ $stock->product->code }}</td>
                            <td class="font-semibold">{{ $stock->product->name }}</td>
                            <td class="money-value">{{ $stock->quantity }}</td>
                            <td>{{ $stock->min_quantity }}</td>
                            <td><span class="badge {{ $badge }} badge-sm">{{ ucfirst($status) }}</span></td>
                            <td class="text-right">
                                <a href="{{ route('admin.stocks.adjust', $stock) }}" class="btn btn-primary btn-xs">Sesuaikan</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="flex flex-col items-center justify-center py-16 text-center text-base-content/60">
                                    <p class="text-sm font-semibold text-base-content">Belum ada stok</p>
                                    <p class="mt-1 text-xs">Stok akan muncul setelah produk dibuat.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $stocks->links() }}
    </div>
</x-app-layout>
