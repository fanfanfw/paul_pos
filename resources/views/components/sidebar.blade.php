@php
    $user = auth()->user();
    $role = $user->role ?? 'kasir';
    $adminItems = [
        ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard'],
        ['label' => 'Produk', 'route' => 'admin.products.index', 'active' => 'admin.products.*'],
        ['label' => 'Kategori', 'route' => 'admin.categories.index', 'active' => 'admin.categories.*'],
        ['label' => 'Stok', 'route' => 'admin.stocks.index', 'active' => 'admin.stocks.*'],
        ['label' => 'User/Kasir', 'route' => 'admin.users.index', 'active' => 'admin.users.*'],
        ['label' => 'Transaksi', 'route' => null, 'active' => 'admin.transactions.*'],
        ['label' => 'Laporan Penjualan', 'route' => null, 'active' => 'admin.reports.sales.*'],
        ['label' => 'Laporan Stok', 'route' => null, 'active' => 'admin.reports.stocks.*'],
    ];
    $kasirItems = [
        ['label' => 'Dashboard', 'route' => 'kasir.dashboard', 'active' => 'kasir.dashboard'],
        ['label' => 'Transaksi Baru', 'route' => 'kasir.transactions.create', 'active' => 'kasir.transactions.create'],
        ['label' => 'Riwayat Saya', 'route' => null, 'active' => 'kasir.transactions.*'],
    ];
    $items = $role === 'admin' ? $adminItems : $kasirItems;
@endphp

<aside
    class="fixed inset-y-0 left-0 z-40 w-64 border-r border-base-300 bg-base-100 shadow-sm transition-transform duration-200 lg:translate-x-0"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
>
    <div class="flex h-full flex-col">
        <div class="flex h-16 items-center justify-between border-b border-base-300 px-5">
            <a href="{{ $role === 'admin' ? route('admin.dashboard') : route('kasir.dashboard') }}" class="leading-tight">
                <div class="text-lg font-bold tracking-tight text-base-content">{{ config('store.name') }}</div>
                <div class="text-xs font-medium text-base-content/60">Point of Sale</div>
            </a>

            <button type="button" class="btn btn-ghost btn-sm btn-square lg:hidden" @click="sidebarOpen = false" aria-label="Tutup menu">
                <span class="text-xl leading-none">&times;</span>
            </button>
        </div>

        <nav class="flex-1 space-y-2 overflow-y-auto px-3 py-5">
            <div class="px-2 text-[11px] font-bold uppercase tracking-[0.18em] text-base-content/45">
                {{ $role === 'admin' ? 'Admin' : 'Kasir' }}
            </div>

            <div class="space-y-1">
                @foreach ($items as $item)
                    @php $isActive = request()->routeIs($item['active']); @endphp

                    @if ($item['route'])
                        <a
                            href="{{ route($item['route']) }}"
                            class="flex items-center rounded-lg px-3 py-2.5 text-sm font-semibold transition {{ $isActive ? 'bg-primary text-primary-content shadow-sm' : 'text-base-content/70 hover:bg-base-200 hover:text-base-content' }}"
                        >
                            {{ $item['label'] }}
                        </a>
                    @else
                        <span class="flex cursor-not-allowed items-center justify-between rounded-lg px-3 py-2.5 text-sm font-semibold text-base-content/35">
                            {{ $item['label'] }}
                            <span class="badge badge-ghost badge-sm">Nanti</span>
                        </span>
                    @endif
                @endforeach
            </div>
        </nav>

        <div class="border-t border-base-300 p-4">
            <div class="mb-3 rounded-xl bg-base-200 p-3">
                <div class="truncate text-sm font-semibold text-base-content">{{ $user->name ?? 'Pengguna' }}</div>
                <div class="mt-1 flex items-center justify-between gap-2">
                    <span class="truncate text-xs text-base-content/60">{{ $user->email ?? '-' }}</span>
                    <span class="badge {{ $role === 'admin' ? 'badge-primary' : 'badge-secondary' }} badge-sm">{{ ucfirst($role) }}</span>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline btn-sm w-full">Keluar</button>
            </form>
        </div>
    </div>
</aside>

<div
    x-cloak
    x-show="sidebarOpen"
    x-transition.opacity
    class="fixed inset-0 z-30 bg-neutral/30 lg:hidden"
    @click="sidebarOpen = false"
></div>
