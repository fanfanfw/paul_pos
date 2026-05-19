@csrf

<div class="grid gap-4 lg:grid-cols-2">
    <div class="form-control">
        <label class="label" for="name"><span class="label-text font-medium">Nama <span class="text-error">*</span></span></label>
        <input id="name" name="name" value="{{ old('name', $user->name ?? '') }}" class="input input-bordered input-sm w-full" required>
        @error('name')<span class="mt-1 text-xs text-error">{{ $message }}</span>@enderror
    </div>

    <div class="form-control">
        <label class="label" for="email"><span class="label-text font-medium">Email <span class="text-error">*</span></span></label>
        <input id="email" name="email" type="email" value="{{ old('email', $user->email ?? '') }}" class="input input-bordered input-sm w-full" required>
        @error('email')<span class="mt-1 text-xs text-error">{{ $message }}</span>@enderror
    </div>

    <div class="form-control">
        <label class="label" for="password"><span class="label-text font-medium">Password {{ isset($user) ? '(opsional)' : '*' }}</span></label>
        <input id="password" name="password" type="password" class="input input-bordered input-sm w-full" @required(! isset($user))>
        <span class="mt-1 text-xs text-base-content/55">Minimal 8 karakter. Kosongkan saat edit jika tidak ingin mengubah.</span>
        @error('password')<span class="mt-1 text-xs text-error">{{ $message }}</span>@enderror
    </div>

    <div class="form-control">
        <label class="label" for="role"><span class="label-text font-medium">Role <span class="text-error">*</span></span></label>
        <select id="role" name="role" class="select select-bordered select-sm w-full" required>
            <option value="admin" @selected(old('role', $user->role ?? 'kasir') === 'admin')>Admin</option>
            <option value="kasir" @selected(old('role', $user->role ?? 'kasir') === 'kasir')>Kasir</option>
        </select>
        @error('role')<span class="mt-1 text-xs text-error">{{ $message }}</span>@enderror
    </div>

    <label class="flex items-center gap-2 text-sm font-medium">
        <input type="checkbox" name="is_active" value="1" class="checkbox checkbox-primary checkbox-sm" @checked(old('is_active', $user->is_active ?? true))>
        Akun aktif
    </label>
</div>

<div class="mt-6 flex gap-2">
    <button class="btn btn-primary btn-sm">Simpan</button>
    <a href="{{ route('admin.users.index') }}" class="btn btn-ghost btn-sm">Batal</a>
</div>
