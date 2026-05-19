@php
    $money = fn ($value) => 'Rp '.number_format((float) $value, 0, ',', '.');
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $transaction->invoice_number }}</title>
    <style>
        @page { margin: 10px; }
        body {
            margin: 0;
            color: #111827;
            font-family: DejaVu Sans Mono, monospace;
            font-size: 10px;
            line-height: 1.35;
        }
        .receipt { width: 100%; }
        .center { text-align: center; }
        .store { font-size: 13px; font-weight: bold; text-transform: uppercase; }
        .line { border-top: 1px dashed #6b7280; margin: 8px 0; }
        .row { clear: both; width: 100%; }
        .row:after { clear: both; content: ""; display: table; }
        .left { float: left; max-width: 58%; }
        .right { float: right; max-width: 42%; text-align: right; }
        .item-name { font-weight: bold; margin-bottom: 2px; }
        .total { border-top: 1px dashed #6b7280; font-size: 12px; font-weight: bold; margin-top: 6px; padding-top: 6px; }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="center">
            <div class="store">{{ config('store.name') }}</div>
            <div>{{ config('store.address') }}</div>
            <div>Telp: {{ config('store.phone') }}</div>
        </div>

        <div class="line"></div>

        <div class="row"><span class="left">No Invoice</span><span class="right">{{ $transaction->invoice_number }}</span></div>
        <div class="row"><span class="left">Tanggal</span><span class="right">{{ $transaction->created_at->translatedFormat('d M Y H:i') }}</span></div>
        <div class="row"><span class="left">Kasir</span><span class="right">{{ $transaction->user->name }}</span></div>

        <div class="line"></div>

        @foreach ($transaction->items as $item)
            <div class="item">
                <div class="item-name">{{ $item->product_name }}</div>
                <div class="row">
                    <span class="left">{{ $item->product_code }} | {{ $item->quantity }} x {{ $money($item->price) }}</span>
                    <span class="right">{{ $money($item->subtotal) }}</span>
                </div>
            </div>
            <div style="height: 6px;"></div>
        @endforeach

        <div class="line"></div>

        <div class="row"><span class="left">Subtotal</span><span class="right">{{ $money($transaction->subtotal) }}</span></div>
        @if ((float) $transaction->discount_amount > 0)
            <div class="row"><span class="left">Diskon</span><span class="right">- {{ $money($transaction->discount_amount) }}</span></div>
        @endif
        @if ((float) $transaction->tax_amount > 0)
            <div class="row"><span class="left">Pajak</span><span class="right">{{ $money($transaction->tax_amount) }}</span></div>
        @endif
        <div class="row total"><span class="left">Total</span><span class="right">{{ $money($transaction->total_amount) }}</span></div>
        <div class="row"><span class="left">Metode Bayar</span><span class="right">{{ strtoupper($transaction->payment_method) }}</span></div>
        <div class="row"><span class="left">Dibayar</span><span class="right">{{ $money($transaction->amount_paid) }}</span></div>
        <div class="row"><span class="left">Kembalian</span><span class="right">{{ $money($transaction->change_amount) }}</span></div>

        @if ($transaction->notes)
            <div class="line"></div>
            <div>Catatan: {{ $transaction->notes }}</div>
        @endif

        <div class="line"></div>
        <div class="center">{{ config('store.footer') }}</div>
    </div>
</body>
</html>
