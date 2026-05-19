@php $money = fn ($value) => 'Rp '.number_format((float) $value, 0, ',', '.'); @endphp

<x-app-layout>
    <x-slot name="header">Detail Transaksi</x-slot>

    <div class="space-y-5">
        <section class="rounded-2xl border border-base-300 bg-base-100 p-5 shadow-sm">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-primary">Invoice</p>
                    <h2 class="mt-1 text-xl font-bold text-base-content">{{ $transaction->invoice_number }}</h2>
                    <p class="mt-1 text-sm text-base-content/60">{{ $transaction->created_at->translatedFormat('d M Y H:i') }} oleh {{ $transaction->user?->name ?? '-' }}</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('kasir.transactions.receipt', $transaction) }}" class="btn btn-primary btn-sm">Lihat Struk</a>
                    <a href="{{ route('admin.transactions.index') }}" class="btn btn-ghost btn-sm">Kembali</a>
                </div>
            </div>
            <div class="mt-5 grid gap-3 md:grid-cols-3">
                <div class="rounded-xl bg-base-200 p-3"><div class="text-xs text-base-content/60">Metode Bayar</div><div class="mt-1 font-bold uppercase">{{ $transaction->payment_method }}</div></div>
                <div class="rounded-xl bg-base-200 p-3"><div class="text-xs text-base-content/60">Status</div><div class="mt-1 font-bold">{{ ucfirst($transaction->status) }}</div></div>
                <div class="rounded-xl bg-base-200 p-3"><div class="text-xs text-base-content/60">Total</div><div class="money-value mt-1 font-bold">{{ $money($transaction->total_amount) }}</div></div>
            </div>
        </section>

        <section class="overflow-x-auto rounded-2xl border border-base-300 bg-base-100 shadow-sm">
            <table class="table table-sm">
                <thead><tr><th>Produk</th><th>Kode</th><th>Harga</th><th>Qty</th><th>Subtotal</th></tr></thead>
                <tbody>
                    @foreach ($transaction->items as $item)
                        <tr>
                            <td class="font-semibold">{{ $item->product_name }}</td>
                            <td class="product-code text-xs">{{ $item->product_code }}</td>
                            <td class="money-value">{{ $money($item->price) }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td class="money-value font-semibold">{{ $money($item->subtotal) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>

        <section class="ml-auto max-w-md rounded-2xl border border-base-300 bg-base-100 p-5 shadow-sm">
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span>Subtotal</span><span class="money-value">{{ $money($transaction->subtotal) }}</span></div>
                <div class="flex justify-between"><span>Diskon</span><span class="money-value">- {{ $money($transaction->discount_amount) }}</span></div>
                @if ((float) $transaction->tax_amount > 0)
                    <div class="flex justify-between"><span>Pajak</span><span class="money-value">{{ $money($transaction->tax_amount) }}</span></div>
                @endif
                <div class="flex justify-between border-t border-base-300 pt-2 text-lg font-bold"><span>Total</span><span class="money-value">{{ $money($transaction->total_amount) }}</span></div>
                <div class="flex justify-between"><span>Dibayar</span><span class="money-value">{{ $money($transaction->amount_paid) }}</span></div>
                <div class="flex justify-between"><span>Kembalian</span><span class="money-value">{{ $money($transaction->change_amount) }}</span></div>
            </div>
        </section>
    </div>
</x-app-layout>
