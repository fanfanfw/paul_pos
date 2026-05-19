@csrf

<div class="space-y-4">
    <div class="form-control">
        <label class="label" for="name"><span class="label-text font-medium">Nama <span class="text-error">*</span></span></label>
        <input id="name" name="name" value="{{ old('name', $category->name ?? '') }}" class="input input-bordered input-sm w-full" required maxlength="100">
        @error('name')<span class="mt-1 text-xs text-error">{{ $message }}</span>@enderror
    </div>

    <div class="form-control">
        <label class="label" for="description"><span class="label-text font-medium">Deskripsi</span></label>
        <textarea id="description" name="description" class="textarea textarea-bordered min-h-24 w-full">{{ old('description', $category->description ?? '') }}</textarea>
        @error('description')<span class="mt-1 text-xs text-error">{{ $message }}</span>@enderror
    </div>
</div>

<div class="mt-6 flex gap-2">
    <button class="btn btn-primary btn-sm">Simpan</button>
    <a href="{{ route('admin.categories.index') }}" class="btn btn-ghost btn-sm">Batal</a>
</div>
