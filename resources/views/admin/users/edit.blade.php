<x-app-layout>
    <x-slot name="header">Edit User</x-slot>

    <div class="rounded-xl border border-base-300 bg-base-100 p-5 shadow-sm">
        <h2 class="text-lg font-bold text-base-content">Edit User</h2>
        <p class="mb-5 text-sm text-base-content/60">Perbarui data akun tanpa menghapus riwayat transaksi atau stok.</p>
        <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @method('PUT')
            @include('admin.users._form')
        </form>
    </div>
</x-app-layout>
