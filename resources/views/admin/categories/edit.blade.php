<x-app-layout>
    <x-slot name="header">Edit Kategori</x-slot>

    <div class="max-w-2xl rounded-xl border border-base-300 bg-base-100 p-5 shadow-sm">
        <h2 class="text-lg font-bold text-base-content">Edit Kategori</h2>
        <p class="mb-5 text-sm text-base-content/60">Perbarui nama atau deskripsi kategori.</p>
        <form method="POST" action="{{ route('admin.categories.update', $category) }}">
            @method('PUT')
            @include('admin.categories._form')
        </form>
    </div>
</x-app-layout>
