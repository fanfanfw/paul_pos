<x-app-layout>
    <x-slot name="header">User/Kasir</x-slot>

    <div class="space-y-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-base-content">User/Kasir</h2>
                <p class="text-sm text-base-content/60">Kelola akun admin dan kasir tanpa register publik.</p>
            </div>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">Tambah User</a>
        </div>

        <form method="GET" class="rounded-xl border border-base-300 bg-base-100 p-4 shadow-sm">
            <div class="grid gap-3 lg:grid-cols-[1fr_160px_160px_auto]">
                <input type="search" name="search" value="{{ request('search') }}" class="input input-bordered input-sm w-full" placeholder="Cari nama atau email">
                <select name="role" class="select select-bordered select-sm w-full">
                    <option value="">Semua role</option>
                    <option value="admin" @selected(request('role') === 'admin')>Admin</option>
                    <option value="kasir" @selected(request('role') === 'kasir')>Kasir</option>
                </select>
                <select name="status" class="select select-bordered select-sm w-full">
                    <option value="">Semua status</option>
                    <option value="active" @selected(request('status') === 'active')>Aktif</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Nonaktif</option>
                </select>
                <div class="flex gap-2">
                    <button class="btn btn-primary btn-sm">Filter</button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-ghost btn-sm">Reset</a>
                </div>
            </div>
        </form>

        <div class="overflow-x-auto rounded-xl border border-base-300 bg-base-100 shadow-sm">
            <table class="table table-sm">
                <thead class="bg-base-200">
                    <tr>
                        <th class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Nama</th>
                        <th class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Email</th>
                        <th class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Role</th>
                        <th class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Status</th>
                        <th class="text-right text-xs font-semibold uppercase tracking-wide text-base-content/60">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr class="hover:bg-base-200/70">
                            <td class="font-semibold">{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td><span class="badge {{ $user->isAdmin() ? 'badge-primary' : 'badge-secondary' }} badge-sm">{{ ucfirst($user->role) }}</span></td>
                            <td><span class="badge {{ $user->is_active ? 'badge-success' : 'badge-ghost' }} badge-sm">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td>
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-ghost btn-xs">Edit</a>
                                    <form method="POST" action="{{ route('admin.users.toggle', $user) }}" onsubmit="return confirm('Ubah status user ini?');">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-outline btn-xs">{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="flex flex-col items-center justify-center py-16 text-center text-base-content/60">
                                    <p class="text-sm font-semibold text-base-content">Belum ada user</p>
                                    <p class="mt-1 text-xs">Tambahkan admin atau kasir untuk mengakses sistem.</p>
                                    <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm mt-4">Tambah User</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $users->links() }}
    </div>
</x-app-layout>
