<div x-data="{ show: true }" x-show="show" x-transition.opacity.duration.200ms x-init="setTimeout(() => show = false, 4000)" class="mb-4 space-y-2">
    @if (session('success'))
        <div class="alert alert-success rounded-xl border border-success/20 shadow-sm">
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-error rounded-xl border border-error/20 shadow-sm">
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if (session('warning'))
        <div class="alert alert-warning rounded-xl border border-warning/20 shadow-sm">
            <span>{{ session('warning') }}</span>
        </div>
    @endif
</div>
