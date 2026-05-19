<x-app-layout>
    <x-slot name="header">Edit Produk</x-slot>

    <div class="rounded-xl border border-base-300 bg-base-100 p-5 shadow-sm">
        <h2 class="text-lg font-bold text-base-content">Edit Produk</h2>
        <p class="mb-5 text-sm text-base-content/60">Perbarui informasi produk dan minimum stok.</p>
        <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
            @method('PUT')
            @include('admin.products._form')
        </form>
    </div>
</x-app-layout>
