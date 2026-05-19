<x-guest-layout>
    <section class="overflow-hidden rounded-3xl border border-base-300 bg-base-100 shadow-sm">
        <div class="border-b border-base-300 bg-base-200/60 p-6 text-center sm:p-8">
            <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-primary text-lg font-bold text-primary-content shadow-sm">
                K
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-base-content">KasirKu</h1>
            <p class="mt-1 text-sm text-base-content/60">Masuk ke workspace toko</p>
        </div>

        <div class="p-6 sm:p-8">
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <div class="form-control">
                    <label class="label" for="email">
                        <span class="label-text text-sm font-medium text-base-content">Email</span>
                    </label>
                    <input id="email" class="input input-bordered w-full focus:input-primary" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div class="form-control">
                    <label class="label" for="password">
                        <span class="label-text text-sm font-medium text-base-content">Password</span>
                    </label>
                    <input id="password" class="input input-bordered w-full focus:input-primary" type="password" name="password" required autocomplete="current-password">
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <label for="remember_me" class="flex items-center gap-2 text-sm text-base-content/70">
                    <input id="remember_me" type="checkbox" class="checkbox checkbox-primary checkbox-sm" name="remember">
                    Ingat saya
                </label>

                <button type="submit" class="btn btn-primary w-full">Masuk</button>
            </form>
        </div>
    </section>
</x-guest-layout>
