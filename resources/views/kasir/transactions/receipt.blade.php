@php
    $money = fn ($value) => 'Rp '.number_format((float) $value, 0, ',', '.');
    $dashboardRoute = auth()->user()->isAdmin() ? route('admin.dashboard') : route('kasir.dashboard');
@endphp

<x-app-layout>
    <x-slot name="header">Struk Pembayaran</x-slot>

    <style>
        .receipt-shell { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace; }
        .receipt-row { display: flex; justify-content: space-between; gap: 12px; }
        .receipt-line { border-top: 1px dashed #9ca3af; margin: 10px 0; }

        @media print {
            @page { size: 80mm auto; margin: 4mm; }
            body { background: #fff !important; }
            body * { visibility: hidden !important; }
            #receipt-area, #receipt-area * { visibility: visible !important; }
            #receipt-area {
                position: absolute !important;
                inset: 0 auto auto 0 !important;
                width: 80mm !important;
                margin: 0 !important;
                padding: 0 !important;
                border: 0 !important;
                box-shadow: none !important;
                color: #000 !important;
                background: #fff !important;
                font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace !important;
                font-size: 10.5px !important;
                line-height: 1.35 !important;
            }
            .no-print, .no-print * { display: none !important; visibility: hidden !important; }
        }
    </style>

    <div class="mx-auto max-w-md space-y-4">
        <div id="receipt-area" class="receipt-shell rounded-xl border border-base-300 bg-white p-5 text-sm text-neutral shadow-sm">
            <div class="text-center">
                <h2 class="text-base font-black uppercase tracking-wide">{{ config('store.name') }}</h2>
                <p class="mt-1 text-[11px] leading-snug">{{ config('store.address') }}</p>
                <p class="text-[11px]">Telp: {{ config('store.phone') }}</p>
            </div>

            <div class="receipt-line"></div>

            <div class="space-y-1 text-[11px]">
                <div class="receipt-row"><span>No Invoice</span><span class="text-right font-bold">{{ $transaction->invoice_number }}</span></div>
                <div class="receipt-row"><span>Tanggal</span><span class="text-right">{{ $transaction->created_at->translatedFormat('d M Y H:i') }}</span></div>
                <div class="receipt-row"><span>Kasir</span><span class="text-right">{{ $transaction->user->name }}</span></div>
            </div>

            <div class="receipt-line"></div>

            <div class="space-y-3">
                @foreach ($transaction->items as $item)
                    <div>
                        <div class="font-bold leading-tight">{{ $item->product_name }}</div>
                        <div class="receipt-row mt-1 text-[11px]">
                            <span>{{ $item->product_code }} | {{ $item->quantity }} x {{ $money($item->price) }}</span>
                            <span class="font-bold">{{ $money($item->subtotal) }}</span>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="receipt-line"></div>

            <div class="space-y-1 text-[11px]">
                <div class="receipt-row"><span>Subtotal</span><span>{{ $money($transaction->subtotal) }}</span></div>
                @if ((float) $transaction->discount_amount > 0)
                    <div class="receipt-row"><span>Diskon</span><span>- {{ $money($transaction->discount_amount) }}</span></div>
                @endif
                @if ((float) $transaction->tax_amount > 0)
                    <div class="receipt-row"><span>Pajak</span><span>{{ $money($transaction->tax_amount) }}</span></div>
                @endif
                <div class="receipt-row border-t border-dashed border-neutral/40 pt-2 text-sm font-black"><span>Total</span><span>{{ $money($transaction->total_amount) }}</span></div>
                <div class="receipt-row"><span>Metode Bayar</span><span class="uppercase">{{ $transaction->payment_method }}</span></div>
                <div class="receipt-row"><span>Dibayar</span><span>{{ $money($transaction->amount_paid) }}</span></div>
                <div class="receipt-row"><span>Kembalian</span><span>{{ $money($transaction->change_amount) }}</span></div>
            </div>

            @if ($transaction->notes)
                <div class="receipt-line"></div>
                <p class="text-[11px]">Catatan: {{ $transaction->notes }}</p>
            @endif

            <div class="receipt-line"></div>

            <p class="text-center text-[11px] leading-snug">{{ config('store.footer') }}</p>
        </div>

        <div class="no-print grid gap-2 sm:grid-cols-2">
            <button type="button" onclick="window.print()" class="btn btn-primary btn-sm">Cetak Struk</button>
            <a href="{{ route('kasir.transactions.receipt.pdf', $transaction) }}" class="btn btn-outline btn-sm">Download PDF</a>
            <a href="{{ route('kasir.transactions.create') }}" class="btn btn-secondary btn-sm">Transaksi Baru</a>
            <a href="{{ $dashboardRoute }}" class="btn btn-ghost btn-sm">Kembali ke Dashboard</a>
        </div>
    </div>
</x-app-layout>
