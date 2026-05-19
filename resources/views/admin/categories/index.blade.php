<x-app-layout>
    <x-slot name="header">Kategori</x-slot>

    <div class="space-y-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-base-content">Kategori</h2>
                <p class="text-sm text-base-content/60">Kelola pengelompokan produk toko.</p>
            </div>
            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm">Tambah Kategori</a>
        </div>

        <form method="GET" class="filter-card rounded-xl p-4">
            <div class="flex flex-col gap-3 sm:flex-row">
                <input type="search" name="search" value="{{ request('search') }}" class="input input-bordered input-sm w-full" placeholder="Cari nama kategori">
                <div class="flex gap-2">
                    <button class="btn btn-primary btn-sm">Cari</button>
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-ghost btn-sm">Reset</a>
                </div>
            </div>
        </form>

        <div class="overflow-x-auto rounded-xl border border-base-300 bg-base-100 shadow-sm">
            <table class="table table-sm">
                <thead class="bg-base-200">
                    <tr>
                        <th class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Nama</th>
                        <th class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Deskripsi</th>
                        <th class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Produk</th>
                        <th class="text-right text-xs font-semibold uppercase tracking-wide text-base-content/60">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr class="hover:bg-base-200/70">
                            <td class="font-semibold">{{ $category->name }}</td>
                            <td class="text-base-content/65">{{ $category->description ?: '-' }}</td>
                            <td><span class="badge badge-ghost badge-sm">{{ $category->products_count }} produk</span></td>
                            <td>
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-ghost btn-xs">Edit</a>
                                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" data-confirm="Kategori yang masih digunakan produk akan ditolak oleh sistem." data-confirm-title="Hapus kategori?" data-confirm-button="Ya, hapus" data-confirm-icon="warning">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-error btn-outline btn-xs">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="flex flex-col items-center justify-center py-16 text-center text-base-content/60">
                                    <p class="text-sm font-semibold text-base-content">Belum ada kategori</p>
                                    <p class="mt-1 text-xs">Tambahkan kategori untuk merapikan katalog produk.</p>
                                    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm mt-4">Tambah Kategori</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $categories->links() }}
    </div>
</x-app-layout>
