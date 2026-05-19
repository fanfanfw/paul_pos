<x-app-layout>
    <x-slot name="header">Penyesuaian Stok</x-slot>

    <div class="grid gap-5 lg:grid-cols-[1fr_380px]">
        <div class="rounded-xl border border-base-300 bg-base-100 p-5 shadow-sm">
            <h2 class="text-lg font-bold text-base-content">{{ $stock->product->name }}</h2>
            <p class="product-code mt-1 text-xs text-base-content/55">{{ $stock->product->code }}</p>

            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                <div class="rounded-xl bg-base-200 p-4">
                    <div class="text-xs text-base-content/60">Stok saat ini</div>
                    <div class="money-value mt-1 text-2xl font-bold">{{ $stock->quantity }}</div>
                </div>
                <div class="rounded-xl bg-base-200 p-4">
                    <div class="text-xs text-base-content/60">Minimum stok</div>
                    <div class="money-value mt-1 text-2xl font-bold">{{ $stock->min_quantity }}</div>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.stocks.update', $stock) }}" class="rounded-xl border border-base-300 bg-base-100 p-5 shadow-sm" data-confirm="Setiap perubahan stok akan dicatat di riwayat stok." data-confirm-title="Simpan penyesuaian stok?" data-confirm-button="Ya, simpan">
            @csrf
            <h3 class="text-base font-bold text-base-content">Form Penyesuaian</h3>
            <p class="mb-4 text-sm text-base-content/60">Setiap perubahan akan dicatat di riwayat stok.</p>

            <div class="space-y-4">
                <div class="form-control">
                    <label class="label" for="type"><span class="label-text font-medium">Tipe <span class="text-error">*</span></span></label>
                    <select id="type" name="type" class="select select-bordered select-sm w-full" required>
                        <option value="in" @selected(old('type') === 'in')>Masuk</option>
                        <option value="out" @selected(old('type') === 'out')>Keluar</option>
                        <option value="adjustment" @selected(old('type') === 'adjustment')>Set jumlah akhir</option>
                    </select>
                    @error('type')<span class="mt-1 text-xs text-error">{{ $message }}</span>@enderror
                </div>

                <div class="form-control">
                    <label class="label" for="quantity"><span class="label-text font-medium">Jumlah <span class="text-error">*</span></span></label>
                    <input id="quantity" name="quantity" type="number" min="0" value="{{ old('quantity') }}" class="input input-bordered input-sm w-full" required>
                    @error('quantity')<span class="mt-1 text-xs text-error">{{ $message }}</span>@enderror
                </div>

                <div class="form-control">
                    <label class="label" for="reference"><span class="label-text font-medium">Referensi</span></label>
                    <input id="reference" name="reference" value="{{ old('reference') }}" class="input input-bordered input-sm w-full" placeholder="Opsional">
                    @error('reference')<span class="mt-1 text-xs text-error">{{ $message }}</span>@enderror
                </div>

                <div class="form-control">
                    <label class="label" for="notes"><span class="label-text font-medium">Catatan</span></label>
                    <textarea id="notes" name="notes" class="textarea textarea-bordered min-h-24 w-full">{{ old('notes') }}</textarea>
                    @error('notes')<span class="mt-1 text-xs text-error">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="mt-6 flex gap-2">
                <button class="btn btn-primary btn-sm">Simpan</button>
                <a href="{{ route('admin.stocks.index') }}" class="btn btn-ghost btn-sm">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>
