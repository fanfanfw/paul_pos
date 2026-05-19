<x-guest-layout>
    <section class="rounded-2xl border border-base-300 bg-base-100 p-6 shadow-sm sm:p-8">
        <div class="mb-7 text-center">
            <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-primary text-lg font-bold text-primary-content shadow-sm">
                K
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-base-content">KasirKu</h1>
            <p class="mt-1 text-sm text-base-content/60">Point of Sale</p>
        </div>

        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <div class="form-control">
                <label class="label" for="email">
                    <span class="label-text text-sm font-medium text-base-content">Email</span>
                </label>
                <input id="email" class="input input-bordered input-sm w-full focus:input-primary" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="form-control">
                <label class="label" for="password">
                    <span class="label-text text-sm font-medium text-base-content">Password</span>
                </label>
                <input id="password" class="input input-bordered input-sm w-full focus:input-primary" type="password" name="password" required autocomplete="current-password">
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <label for="remember_me" class="flex items-center gap-2 text-sm text-base-content/70">
                <input id="remember_me" type="checkbox" class="checkbox checkbox-primary checkbox-sm" name="remember">
                Ingat saya
            </label>

            <button type="submit" class="btn btn-primary btn-sm w-full">Masuk</button>
        </form>

        <div class="mt-6 rounded-xl bg-base-200 p-4 text-xs leading-relaxed text-base-content/65">
            <div class="font-semibold text-base-content">Akun demo disiapkan di Phase 2</div>
            <div class="mt-1">Admin dan kasir akan dibuat melalui seeder serta manajemen user admin.</div>
        </div>
    </section>
</x-guest-layout>
