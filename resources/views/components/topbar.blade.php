@props(['title' => null])

@php
    $user = auth()->user();
    $role = $user->role ?? 'kasir';
    $pageTitle = $title ? trim((string) $title) : 'Dashboard';
    $lowStockCount = $role === 'admin'
        ? \App\Models\Stock::query()->where('quantity', '>', 0)->whereColumn('quantity', '<=', 'min_quantity')->count()
        : 0;
@endphp

<header class="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-base-300 bg-base-100/95 px-4 shadow-sm backdrop-blur lg:px-6">
    <div class="flex items-center gap-3">
        <button type="button" class="btn btn-ghost btn-sm btn-square lg:hidden" @click="sidebarOpen = true" aria-label="Buka menu">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        <div>
            <h1 class="text-base font-bold leading-tight text-base-content sm:text-lg">{{ $pageTitle }}</h1>
            <p class="hidden text-xs text-base-content/55 sm:block">{{ config('store.name') }} / {{ ucfirst($role) }}</p>
        </div>
    </div>

    <div class="flex items-center gap-3">
        @if ($role === 'admin')
            <a href="{{ route('admin.reports.stocks', ['status' => 'menipis']) }}" class="hidden rounded-full border border-warning/20 bg-warning/10 px-3 py-1 text-xs font-semibold text-warning transition hover:bg-warning/15 sm:inline-flex">
                Stok menipis: {{ $lowStockCount }}
            </a>
        @endif

        <div class="hidden text-right sm:block">
            <div class="text-sm font-semibold leading-tight text-base-content">{{ $user->name ?? 'Pengguna' }}</div>
            <div class="text-xs text-base-content/55">{{ ucfirst($role) }}</div>
        </div>

        <span class="badge {{ $role === 'admin' ? 'badge-primary' : 'badge-secondary' }} badge-sm">{{ ucfirst($role) }}</span>
    </div>
</header>
