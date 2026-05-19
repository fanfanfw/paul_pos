<x-app-layout>
    <x-slot name="header">Tambah Produk</x-slot>

    <div class="rounded-xl border border-base-300 bg-base-100 p-5 shadow-sm">
        <h2 class="text-lg font-bold text-base-content">Tambah Produk</h2>
        <p class="mb-5 text-sm text-base-content/60">Produk baru otomatis mendapat satu record stok.</p>
        <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
            @include('admin.products._form')
        </form>
    </div>
</x-app-layout>
