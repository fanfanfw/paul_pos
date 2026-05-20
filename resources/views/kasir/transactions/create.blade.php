<x-app-layout>
    <x-slot name="header">Transaksi Baru</x-slot>

    <div
        x-data="transactionPage({
            searchUrl: '{{ route('kasir.api.products.search', [], false) }}',
            storeUrl: '{{ route('kasir.transactions.store') }}',
            csrf: '{{ csrf_token() }}',
            taxRate: {{ $taxRate }},
        })"
        x-init="init()"
        class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_420px]"
    >
        <section class="space-y-4">
            <div class="panel-card rounded-2xl p-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-primary">Katalog Kasir</p>
                        <h2 class="mt-1 text-xl font-bold text-base-content">Pilih Produk</h2>
                        <p class="text-sm text-base-content/60">Cari nama atau kode produk, lalu tambahkan ke keranjang.</p>
                    </div>
                    <div class="form-control w-full sm:max-w-sm">
                        <label class="label" for="product-search"><span class="label-text font-medium">Cari produk</span></label>
                        <input
                            id="product-search"
                            type="search"
                            x-model="query"
                            @input.debounce.300ms="searchProducts()"
                            class="input input-bordered w-full"
                            placeholder="Contoh: kopi atau MNM001"
                        >
                    </div>
                </div>
            </div>

            <div x-show="loading" class="panel-card rounded-xl p-6 text-sm text-base-content/60" aria-live="polite">
                Memuat produk...
            </div>

            <div x-show="!loading && products.length === 0" class="panel-card rounded-xl p-10 text-center">
                <p class="font-semibold text-base-content">Produk tidak ditemukan</p>
                <p class="mt-1 text-sm text-base-content/60">Coba kata kunci lain atau cek status produk di admin.</p>
            </div>

            <div x-show="!loading && products.length > 0" class="grid gap-3 sm:grid-cols-2 2xl:grid-cols-3">
                <template x-for="product in products" :key="product.id">
                    <button
                        type="button"
                        @click="addToCart(product)"
                        :disabled="product.stock < 1"
                        class="product-tile card min-h-36 text-left transition hover:-translate-y-0.5 hover:border-primary hover:shadow-md disabled:cursor-not-allowed disabled:bg-base-200 disabled:opacity-60"
                    >
                        <div class="card-body gap-3 p-4">
                            <div class="flex items-start gap-3">
                                <div class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-base-200 text-xs font-bold text-base-content/40">
                                    <template x-if="product.image_url">
                                        <img :src="product.image_url" :alt="product.name" class="h-full w-full object-cover">
                                    </template>
                                    <template x-if="!product.image_url">
                                        <span>IMG</span>
                                    </template>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="line-clamp-2 font-bold text-base-content" x-text="product.name"></div>
                                    <div class="product-code mt-1 text-xs text-base-content/50" x-text="product.code"></div>
                                </div>
                            </div>
                            <div class="flex items-end justify-between gap-2">
                                <div class="money-value text-lg font-bold" x-text="formatCurrency(product.price)"></div>
                                <span class="badge badge-sm" :class="product.stock > 0 ? 'badge-success' : 'badge-error'" x-text="product.stock > 0 ? 'Stok ' + product.stock : 'Habis'"></span>
                            </div>
                        </div>
                    </button>
                </template>
            </div>
        </section>

        <aside class="space-y-4 xl:sticky xl:top-20 xl:self-start">
            <form method="POST" action="{{ route('kasir.transactions.store') }}" @submit.prevent="submitTransaction" class="panel-card rounded-2xl p-4">
                @csrf
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-secondary">Keranjang</p>
                        <h2 class="mt-1 text-xl font-bold text-base-content">Pembayaran</h2>
                    </div>
                    <span class="badge badge-primary" x-text="cart.length + ' item'"></span>
                </div>

                @if ($errors->any())
                    <div class="alert alert-error mt-4 text-sm">
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <div class="mt-4 max-h-[42vh] space-y-3 overflow-y-auto pr-1 xl:max-h-[34vh]">
                    <template x-if="cart.length === 0">
                        <div class="rounded-xl bg-base-200 p-5 text-center text-sm text-base-content/60">
                            Keranjang masih kosong.
                        </div>
                    </template>

                    <template x-for="(item, index) in cart" :key="item.id">
                        <div class="rounded-xl border border-base-300 p-3">
                            <input type="hidden" :name="`items[${index}][product_id]`" :value="item.id">
                            <input type="hidden" :name="`items[${index}][quantity]`" :value="item.quantity">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <div class="font-semibold text-base-content" x-text="item.name"></div>
                                    <div class="product-code mt-1 text-xs text-base-content/50" x-text="item.code"></div>
                                </div>
                                <button type="button" class="btn btn-ghost btn-xs text-error" @click="removeItem(item.id)">Hapus</button>
                            </div>
                            <div class="mt-3 flex items-center justify-between gap-3">
                                <div class="money-value text-sm font-semibold" x-text="formatCurrency(item.price)"></div>
                                <div class="join">
                                    <button type="button" class="btn join-item btn-xs" @click="decreaseQty(item.id)">-</button>
                                    <span class="join-item flex h-6 min-w-10 items-center justify-center border-y border-base-300 px-3 text-sm font-bold" x-text="item.quantity"></span>
                                    <button type="button" class="btn join-item btn-xs" @click="increaseQty(item.id)" :disabled="item.quantity >= item.stock">+</button>
                                </div>
                            </div>
                            <div class="mt-2 flex items-center justify-between text-sm">
                                <span class="text-base-content/60">Subtotal</span>
                                <span class="money-value font-bold" x-text="formatCurrency(item.price * item.quantity)"></span>
                            </div>
                            <p class="mt-1 text-xs text-base-content/50" x-text="'Stok tersedia: ' + item.stock"></p>
                        </div>
                    </template>
                </div>

                <div class="divider my-4"></div>

                <div class="space-y-3">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-base-content/60">Subtotal</span>
                        <span class="money-value font-semibold" x-text="formatCurrency(subtotal())"></span>
                    </div>

                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-[120px_1fr]">
                        <select name="discount_type" x-model="discountType" class="select select-bordered select-sm w-full">
                            <option value="">Tanpa diskon</option>
                            <option value="amount">Nominal</option>
                            <option value="percent">Persen</option>
                        </select>
                        <input name="discount_value" type="number" min="0" step="0.01" x-model.number="discountValue" class="input input-bordered input-sm w-full" placeholder="Nilai diskon" :disabled="!discountType">
                    </div>
                    <p x-show="discountAmount() > subtotal()" class="text-xs text-error">Diskon tidak boleh melebihi subtotal.</p>

                    <div class="flex items-center justify-between text-sm">
                        <span class="text-base-content/60">Diskon</span>
                        <span class="money-value font-semibold" x-text="'- ' + formatCurrency(discountAmount())"></span>
                    </div>

                    <div x-show="taxRate > 0" class="flex items-center justify-between text-sm">
                        <span class="text-base-content/60">Pajak</span>
                        <span class="money-value font-semibold" x-text="formatCurrency(taxAmount())"></span>
                    </div>

                    <div class="rounded-xl bg-base-200 p-4">
                        <div class="text-sm text-base-content/60">Total</div>
                        <div class="money-value mt-1 text-3xl font-bold tracking-tight text-base-content" x-text="formatCurrency(total())"></div>
                    </div>
                </div>

                <div class="mt-4 space-y-3">
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Metode Bayar</span></label>
                        <select name="payment_method" x-model="paymentMethod" class="select select-bordered w-full" required>
                            <option value="cash">Cash</option>
                            <option value="transfer">Transfer</option>
                            <option value="qris">QRIS</option>
                        </select>
                    </div>

                    <div x-show="paymentMethod === 'cash'" class="form-control">
                        <label class="label"><span class="label-text font-medium">Uang Diterima</span></label>
                        <input name="amount_paid" type="number" min="0" step="0.01" x-model.number="amountPaid" class="input input-bordered w-full" placeholder="0">
                        <div class="mt-2 rounded-xl bg-base-200 p-3">
                            <div class="text-xs text-base-content/60">Kembalian</div>
                            <div class="money-value text-2xl font-bold" :class="change() < 0 ? 'text-error' : 'text-success'" x-text="formatCurrency(Math.max(change(), 0))"></div>
                        </div>
                        <p x-show="change() < 0" class="mt-1 text-xs text-error">Uang diterima kurang dari total transaksi.</p>
                    </div>

                    <div x-cloak x-show="paymentMethod !== 'cash'" class="rounded-xl bg-base-200 p-3 text-sm text-base-content/70">
                        Pembayaran dianggap pas sesuai total transaksi.
                    </div>

                    <textarea name="notes" x-model="notes" class="textarea textarea-bordered min-h-20 w-full" placeholder="Catatan transaksi (opsional)"></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-lg mt-4 w-full" :class="submitting ? 'loading' : ''" :disabled="!canSubmit() || submitting">
                    <span x-text="submitting ? 'Memproses...' : 'Proses Transaksi'"></span>
                </button>
                <p class="mt-2 text-center text-xs text-base-content/55">Total akan divalidasi ulang oleh server sebelum transaksi disimpan.</p>
            </form>
        </aside>
    </div>

    <script>
        function transactionPage(config) {
            return {
                products: [],
                cart: [],
                query: '',
                loading: false,
                submitting: false,
                discountType: '',
                discountValue: 0,
                paymentMethod: 'cash',
                amountPaid: 0,
                notes: '',
                taxRate: config.taxRate,
                init() {
                    this.searchProducts();
                },
                async searchProducts() {
                    this.loading = true;
                    try {
                        const response = await fetch(config.searchUrl + '?q=' + encodeURIComponent(this.query), {
                            headers: { 'Accept': 'application/json' },
                        });
                        this.products = await response.json();
                    } finally {
                        this.loading = false;
                    }
                },
                addToCart(product) {
                    if (product.stock < 1) return;
                    const existing = this.cart.find((item) => item.id === product.id);
                    if (existing) {
                        if (existing.quantity < existing.stock) existing.quantity++;
                        return;
                    }
                    this.cart.push({ ...product, quantity: 1 });
                },
                removeItem(productId) {
                    this.cart = this.cart.filter((item) => item.id !== productId);
                },
                increaseQty(productId) {
                    const item = this.cart.find((item) => item.id === productId);
                    if (item && item.quantity < item.stock) item.quantity++;
                },
                decreaseQty(productId) {
                    const item = this.cart.find((item) => item.id === productId);
                    if (!item) return;
                    if (item.quantity <= 1) this.removeItem(productId);
                    else item.quantity--;
                },
                subtotal() {
                    return this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
                },
                discountAmount() {
                    const value = Number(this.discountValue || 0);
                    if (!this.discountType || value <= 0) return 0;
                    if (this.discountType === 'percent') return this.subtotal() * (value / 100);
                    return value;
                },
                taxAmount() {
                    if (this.taxRate <= 0) return 0;
                    return Math.max(this.subtotal() - this.discountAmount(), 0) * (this.taxRate / 100);
                },
                total() {
                    return Math.max(this.subtotal() - this.discountAmount() + this.taxAmount(), 0);
                },
                change() {
                    if (this.paymentMethod !== 'cash') return 0;
                    return Number(this.amountPaid || 0) - this.total();
                },
                canSubmit() {
                    if (this.cart.length < 1 || this.discountAmount() > this.subtotal()) return false;
                    return this.paymentMethod !== 'cash' || this.change() >= 0;
                },
                async submitTransaction(event) {
                    if (!this.canSubmit()) return;

                    const result = await window.KasirkuSwal.confirm({
                        title: 'Proses transaksi?',
                        text: `Total pembayaran ${this.formatCurrency(this.total())}. Pastikan item dan nominal sudah benar.`,
                        icon: 'question',
                        confirmButtonText: 'Ya, proses transaksi',
                    });

                    if (!result.isConfirmed) return;

                    this.submitting = true;
                    event.target.submit();
                },
                formatCurrency(value) {
                    return new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        maximumFractionDigits: 0,
                    }).format(value || 0);
                },
            }
        }
    </script>
</x-app-layout>
