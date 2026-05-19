@php $money = fn ($value) => 'Rp '.number_format((float) $value, 0, ',', '.'); @endphp

<x-app-layout>
    <x-slot name="header">Dashboard Kasir</x-slot>

    <div class="space-y-6">
        <section class="rounded-2xl border border-base-300 bg-base-100 p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-secondary">Kasir</p>
            <h2 class="mt-2 text-xl font-bold text-base-content">Selamat bekerja, {{ auth()->user()->name }}</h2>
            <p class="mt-1 text-sm text-base-content/60">Ringkasan penjualan pribadi hari ini.</p>
            <a href="{{ route('kasir.transactions.create') }}" class="btn btn-primary btn-lg mt-5">Mulai Transaksi Baru</a>
        </section>

        <section class="grid gap-4 md:grid-cols-2">
            <div class="stat rounded-xl border border-base-300 bg-base-100 shadow-sm">
                <div class="stat-title text-xs text-base-content/60">Penjualanku Hari Ini</div>
                <div class="stat-value money-value text-2xl font-bold">{{ $money($todaySummary['total_revenue']) }}</div>
                <div class="stat-desc text-xs">Transaksi completed milikmu</div>
            </div>
            <div class="stat rounded-xl border border-base-300 bg-base-100 shadow-sm">
                <div class="stat-title text-xs text-base-content/60">Jumlah Transaksi Hari Ini</div>
                <div class="stat-value money-value text-2xl font-bold">{{ $todaySummary['total_transactions'] }}</div>
                <div class="stat-desc text-xs">Tidak termasuk transaksi kasir lain</div>
            </div>
        </section>

        <section class="rounded-2xl border border-base-300 bg-base-100 p-5 shadow-sm">
            <h3 class="font-bold text-base-content">10 Transaksi Terakhir Saya</h3>
            <div class="mt-4 overflow-x-auto">
                <table class="table table-sm">
                    <thead><tr><th>Invoice</th><th>Tanggal</th><th>Item</th><th>Total</th><th>Bayar</th></tr></thead>
                    <tbody>
                        @forelse ($latestTransactions as $transaction)
                            <tr>
                                <td><a class="link font-semibold" href="{{ route('kasir.transactions.receipt', $transaction) }}">{{ $transaction->invoice_number }}</a></td>
                                <td>{{ $transaction->created_at->translatedFormat('d M Y H:i') }}</td>
                                <td>{{ $transaction->items->sum('quantity') }}</td>
                                <td class="money-value">{{ $money($transaction->total_amount) }}</td>
                                <td><span class="badge badge-ghost badge-sm uppercase">{{ $transaction->payment_method }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-12 text-center text-sm text-base-content/60">Belum ada transaksi milikmu.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
