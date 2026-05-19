<x-app-layout>
    <x-slot name="header">Tambah Kategori</x-slot>

    <div class="max-w-2xl rounded-xl border border-base-300 bg-base-100 p-5 shadow-sm">
        <h2 class="text-lg font-bold text-base-content">Tambah Kategori</h2>
        <p class="mb-5 text-sm text-base-content/60">Buat kategori baru untuk produk toko.</p>
        <form method="POST" action="{{ route('admin.categories.store') }}">
            @include('admin.categories._form')
        </form>
    </div>
</x-app-layout>
