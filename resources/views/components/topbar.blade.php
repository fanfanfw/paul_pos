@props(['title' => null])

@php
    $user = auth()->user();
    $role = $user->role ?? 'kasir';
    $pageTitle = $title ? trim((string) $title) : 'Dashboard';
@endphp

<header class="sticky top-0 z-20 flex h-14 items-center justify-between border-b border-base-300 bg-base-100 px-4 lg:px-6">
    <div class="flex items-center gap-3">
        <button type="button" class="btn btn-ghost btn-sm btn-square lg:hidden" @click="sidebarOpen = true" aria-label="Buka menu">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        <div>
            <h1 class="text-base font-bold text-base-content">{{ $pageTitle }}</h1>
            <p class="hidden text-xs text-base-content/55 sm:block">{{ config('store.name') }} / {{ ucfirst($role) }}</p>
        </div>
    </div>

    <div class="flex items-center gap-3">
        @if ($role === 'admin')
            <span class="hidden rounded-full bg-warning/10 px-3 py-1 text-xs font-semibold text-warning sm:inline-flex">
                Stok menipis: fase berikutnya
            </span>
        @endif

        <div class="hidden text-right sm:block">
            <div class="text-sm font-semibold leading-tight text-base-content">{{ $user->name ?? 'Pengguna' }}</div>
            <div class="text-xs text-base-content/55">{{ ucfirst($role) }}</div>
        </div>

        <span class="badge {{ $role === 'admin' ? 'badge-primary' : 'badge-secondary' }} badge-sm">{{ ucfirst($role) }}</span>
    </div>
</header>
