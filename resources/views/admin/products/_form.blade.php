@csrf

<div class="grid gap-4 lg:grid-cols-2">
    <div class="form-control">
        <label class="label" for="code"><span class="label-text font-medium">Kode Produk</span></label>
        <input id="code" name="code" value="{{ old('code', $product->code ?? '') }}" class="input input-bordered input-sm w-full product-code uppercase" maxlength="50" placeholder="Auto jika kosong">
        @error('code')<span class="mt-1 text-xs text-error">{{ $message }}</span>@enderror
    </div>

    <div class="form-control">
        <label class="label" for="name"><span class="label-text font-medium">Nama <span class="text-error">*</span></span></label>
        <input id="name" name="name" value="{{ old('name', $product->name ?? '') }}" class="input input-bordered input-sm w-full" required maxlength="150">
        @error('name')<span class="mt-1 text-xs text-error">{{ $message }}</span>@enderror
    </div>

    <div class="form-control">
        <label class="label" for="category_id"><span class="label-text font-medium">Kategori</span></label>
        <select id="category_id" name="category_id" class="select select-bordered select-sm w-full">
            <option value="">Tanpa kategori</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) old('category_id', $product->category_id ?? '') === (string) $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        @error('category_id')<span class="mt-1 text-xs text-error">{{ $message }}</span>@enderror
    </div>

    <div class="form-control">
        <label class="label" for="image"><span class="label-text font-medium">Gambar Produk</span></label>
        <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" class="file-input file-input-bordered file-input-sm w-full">
        <span class="mt-1 text-xs text-base-content/55">JPG, PNG, atau WebP maksimal 2MB.</span>
        @error('image')<span class="mt-1 text-xs text-error">{{ $message }}</span>@enderror
    </div>

    <div class="form-control">
        <label class="label" for="price"><span class="label-text font-medium">Harga Jual <span class="text-error">*</span></span></label>
        <input id="price" name="price" type="number" min="0" step="0.01" value="{{ old('price', $product->price ?? '') }}" class="input input-bordered input-sm w-full" required>
        @error('price')<span class="mt-1 text-xs text-error">{{ $message }}</span>@enderror
    </div>

    <div class="form-control">
        <label class="label" for="cost_price"><span class="label-text font-medium">Harga Modal</span></label>
        <input id="cost_price" name="cost_price" type="number" min="0" step="0.01" value="{{ old('cost_price', $product->cost_price ?? '') }}" class="input input-bordered input-sm w-full">
        @error('cost_price')<span class="mt-1 text-xs text-error">{{ $message }}</span>@enderror
    </div>

    @if (! isset($product))
        <div class="form-control">
            <label class="label" for="initial_quantity"><span class="label-text font-medium">Stok Awal <span class="text-error">*</span></span></label>
            <input id="initial_quantity" name="initial_quantity" type="number" min="0" value="{{ old('initial_quantity', 0) }}" class="input input-bordered input-sm w-full" required>
            @error('initial_quantity')<span class="mt-1 text-xs text-error">{{ $message }}</span>@enderror
        </div>
    @endif

    <div class="form-control">
        <label class="label" for="min_quantity"><span class="label-text font-medium">Minimum Stok <span class="text-error">*</span></span></label>
        <input id="min_quantity" name="min_quantity" type="number" min="0" value="{{ old('min_quantity', $product->stock?->min_quantity ?? 5) }}" class="input input-bordered input-sm w-full" required>
        @error('min_quantity')<span class="mt-1 text-xs text-error">{{ $message }}</span>@enderror
    </div>

    <div class="form-control lg:col-span-2">
        <label class="label" for="description"><span class="label-text font-medium">Deskripsi</span></label>
        <textarea id="description" name="description" class="textarea textarea-bordered min-h-24 w-full">{{ old('description', $product->description ?? '') }}</textarea>
        @error('description')<span class="mt-1 text-xs text-error">{{ $message }}</span>@enderror
    </div>

    <label class="flex items-center gap-2 text-sm font-medium">
        <input type="checkbox" name="is_active" value="1" class="checkbox checkbox-primary checkbox-sm" @checked(old('is_active', $product->is_active ?? true))>
        Produk aktif
    </label>
</div>

<div class="mt-6 flex gap-2">
    <button class="btn btn-primary btn-sm">Simpan</button>
    <a href="{{ route('admin.products.index') }}" class="btn btn-ghost btn-sm">Batal</a>
</div>
