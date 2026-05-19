<x-app-layout>
    <x-slot name="header">Struk Sementara</x-slot>

    <div class="mx-auto max-w-2xl space-y-5">
        <div class="rounded-2xl border border-base-300 bg-base-100 p-5 shadow-sm">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-primary">Transaksi Berhasil</p>
                    <h2 class="mt-1 text-xl font-bold text-base-content">{{ $transaction->invoice_number }}</h2>
                    <p class="mt-1 text-sm text-base-content/60">Struk final dan PDF akan dibuat pada Phase 5.</p>
                </div>
                <span class="badge badge-success">{{ ucfirst($transaction->status) }}</span>
            </div>

            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                <div class="rounded-xl bg-base-200 p-3">
                    <div class="text-xs text-base-content/60">Kasir</div>
                    <div class="mt-1 font-semibold">{{ $transaction->user->name }}</div>
                </div>
                <div class="rounded-xl bg-base-200 p-3">
                    <div class="text-xs text-base-content/60">Metode Bayar</div>
                    <div class="mt-1 font-semibold uppercase">{{ $transaction->payment_method }}</div>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-base-300 bg-base-100 p-5 shadow-sm">
            <h3 class="font-bold text-base-content">Item</h3>
            <div class="mt-3 divide-y divide-base-300">
                @foreach ($transaction->items as $item)
                    <div class="flex items-start justify-between gap-3 py-3">
                        <div>
                            <div class="font-semibold">{{ $item->product_name }}</div>
                            <div class="product-code text-xs text-base-content/50">{{ $item->product_code }} x {{ $item->quantity }}</div>
                        </div>
                        <div class="money-value font-bold">Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}</div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 space-y-2 rounded-xl bg-base-200 p-4 text-sm">
                <div class="flex justify-between"><span>Subtotal</span><span class="money-value">Rp {{ number_format((float) $transaction->subtotal, 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span>Diskon</span><span class="money-value">- Rp {{ number_format((float) $transaction->discount_amount, 0, ',', '.') }}</span></div>
                @if ((float) $transaction->tax_amount > 0)
                    <div class="flex justify-between"><span>Pajak</span><span class="money-value">Rp {{ number_format((float) $transaction->tax_amount, 0, ',', '.') }}</span></div>
                @endif
                <div class="flex justify-between border-t border-base-300 pt-2 text-lg font-bold"><span>Total</span><span class="money-value">Rp {{ number_format((float) $transaction->total_amount, 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span>Dibayar</span><span class="money-value">Rp {{ number_format((float) $transaction->amount_paid, 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span>Kembalian</span><span class="money-value">Rp {{ number_format((float) $transaction->change_amount, 0, ',', '.') }}</span></div>
            </div>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('kasir.transactions.create') }}" class="btn btn-primary btn-sm">Transaksi Baru</a>
            <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('kasir.dashboard') }}" class="btn btn-ghost btn-sm">Kembali ke Dashboard</a>
        </div>
    </div>
</x-app-layout>
