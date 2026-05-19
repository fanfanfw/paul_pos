<x-app-layout>
    <x-slot name="header">Produk</x-slot>

    <div class="space-y-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-base-content">Produk</h2>
                <p class="text-sm text-base-content/60">Kelola katalog produk yang tampil di halaman kasir.</p>
            </div>
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">Tambah Produk</a>
        </div>

        <form method="GET" class="filter-card rounded-xl p-4">
            <div class="grid gap-3 lg:grid-cols-[1fr_180px_160px_auto]">
                <input type="search" name="search" value="{{ request('search') }}" class="input input-bordered input-sm w-full" placeholder="Cari nama atau kode produk">
                <select name="category_id" class="select select-bordered select-sm w-full">
                    <option value="">Semua kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                <select name="status" class="select select-bordered select-sm w-full">
                    <option value="">Semua status</option>
                    <option value="active" @selected(request('status') === 'active')>Aktif</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Nonaktif</option>
                </select>
                <div class="flex gap-2">
                    <button class="btn btn-primary btn-sm">Filter</button>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-ghost btn-sm">Reset</a>
                </div>
            </div>
        </form>

        <div class="overflow-x-auto rounded-xl border border-base-300 bg-base-100/90 shadow-sm">
            <table class="table table-sm">
                <thead class="bg-base-200">
                    <tr>
                        <th class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Produk</th>
                        <th class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Kategori</th>
                        <th class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Harga</th>
                        <th class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Stok</th>
                        <th class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Status</th>
                        <th class="text-right text-xs font-semibold uppercase tracking-wide text-base-content/60">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr class="hover:bg-base-200/70">
                            <td>
                                <div class="flex items-center gap-3">
                                    @if ($product->imageUrl())
                                        <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" class="h-12 w-12 rounded-xl object-cover">
                                    @else
                                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-base-200 font-bold text-base-content/50">{{ strtoupper(substr($product->name, 0, 1)) }}</div>
                                    @endif
                                    <div>
                                        <div class="font-semibold text-base-content">{{ $product->name }}</div>
                                        <div class="product-code text-xs text-base-content/55">{{ $product->code }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $product->category?->name ?? '-' }}</td>
                            <td class="money-value">Rp {{ number_format((float) $product->price, 0, ',', '.') }}</td>
                            <td><span class="badge badge-ghost badge-sm">{{ $product->stockQuantity() }}</span></td>
                            <td><span class="badge {{ $product->is_active ? 'badge-success' : 'badge-ghost' }} badge-sm">{{ $product->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td>
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-ghost btn-xs">Edit</a>
                                    <form method="POST" action="{{ route('admin.products.toggle', $product) }}" data-confirm="Produk akan {{ $product->is_active ? 'dinonaktifkan dari katalog kasir' : 'diaktifkan kembali di katalog kasir' }}." data-confirm-title="Ubah status produk?" data-confirm-button="Ya, ubah status">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-outline btn-xs">{{ $product->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.products.destroy', $product) }}" data-confirm="Produk akan dihapus secara soft delete dan tidak tampil lagi di daftar produk aktif." data-confirm-title="Hapus produk?" data-confirm-button="Ya, hapus" data-confirm-icon="warning">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-error btn-outline btn-xs">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="flex flex-col items-center justify-center py-16 text-center text-base-content/60">
                                    <p class="text-sm font-semibold text-base-content">Belum ada produk</p>
                                    <p class="mt-1 text-xs">Mulai dengan menambahkan produk pertama.</p>
                                    <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm mt-4">Tambah Produk</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $products->links() }}
    </div>
</x-app-layout>
