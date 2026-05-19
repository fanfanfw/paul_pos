<x-app-layout>
    <x-slot name="header">Tambah User</x-slot>

    <div class="rounded-xl border border-base-300 bg-base-100 p-5 shadow-sm">
        <h2 class="text-lg font-bold text-base-content">Tambah User</h2>
        <p class="mb-5 text-sm text-base-content/60">Akun admin dan kasir dibuat dari halaman ini.</p>
        <form method="POST" action="{{ route('admin.users.store') }}">
            @include('admin.users._form')
        </form>
    </div>
</x-app-layout>
