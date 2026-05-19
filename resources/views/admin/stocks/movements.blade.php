<x-app-layout>
    <x-slot name="header">Riwayat Stok</x-slot>

    <div class="space-y-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-base-content">Riwayat Pergerakan Stok</h2>
                <p class="text-sm text-base-content/60">Audit semua perubahan stok produk.</p>
            </div>
            <a href="{{ route('admin.stocks.index') }}" class="btn btn-outline btn-sm">Kembali ke Stok</a>
        </div>

        <form method="GET" class="filter-card rounded-xl p-4">
            <div class="grid gap-3 lg:grid-cols-5">
                <select name="product_id" class="select select-bordered select-sm w-full">
                    <option value="">Semua produk</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}" @selected((string) request('product_id') === (string) $product->id)>{{ $product->code }} - {{ $product->name }}</option>
                    @endforeach
                </select>
                <select name="type" class="select select-bordered select-sm w-full">
                    <option value="">Semua tipe</option>
                    <option value="in" @selected(request('type') === 'in')>Masuk</option>
                    <option value="out" @selected(request('type') === 'out')>Keluar</option>
                    <option value="adjustment" @selected(request('type') === 'adjustment')>Adjustment</option>
                </select>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="input input-bordered input-sm w-full">
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="input input-bordered input-sm w-full">
                <div class="flex gap-2">
                    <button class="btn btn-primary btn-sm">Filter</button>
                    <a href="{{ route('admin.stocks.movements') }}" class="btn btn-ghost btn-sm">Reset</a>
                </div>
            </div>
        </form>

        <div class="overflow-x-auto rounded-xl border border-base-300 bg-base-100 shadow-sm">
            <table class="table table-sm">
                <thead class="bg-base-200">
                    <tr>
                        <th class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Waktu</th>
                        <th class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Produk</th>
                        <th class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Tipe</th>
                        <th class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Sebelum</th>
                        <th class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Jumlah</th>
                        <th class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Sesudah</th>
                        <th class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Catatan</th>
                        <th class="text-xs font-semibold uppercase tracking-wide text-base-content/60">User</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($movements as $movement)
                        <tr class="hover:bg-base-200/70">
                            <td class="text-xs">{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <div class="font-semibold">{{ $movement->product?->name ?? 'Produk tidak tersedia' }}</div>
                                <div class="product-code text-xs text-base-content/55">{{ $movement->product?->code ?? '-' }}</div>
                            </td>
                            <td><span class="badge badge-ghost badge-sm">{{ $movement->type }}</span></td>
                            <td class="money-value">{{ $movement->before_quantity }}</td>
                            <td class="money-value">{{ $movement->quantity }}</td>
                            <td class="money-value">{{ $movement->after_quantity }}</td>
                            <td>{{ $movement->notes ?: '-' }}</td>
                            <td>{{ $movement->user?->name ?? 'System' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="flex flex-col items-center justify-center py-16 text-center text-base-content/60">
                                    <p class="text-sm font-semibold text-base-content">Belum ada riwayat stok</p>
                                    <p class="mt-1 text-xs">Pergerakan stok akan muncul setelah ada penyesuaian.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $movements->links() }}
    </div>
</x-app-layout>
