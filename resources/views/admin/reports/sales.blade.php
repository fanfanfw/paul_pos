@php
    $money = fn ($value) => 'Rp '.number_format((float) $value, 0, ',', '.');
    $trendLabels = $trend->pluck('label')->values();
    $trendValues = $trend->pluck('total_revenue')->values();
@endphp

<x-app-layout>
    <x-slot name="header">Laporan Penjualan</x-slot>

    <div class="space-y-5">
        <section class="rounded-2xl border border-base-300 bg-base-100 p-5 shadow-sm">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="text-xl font-bold text-base-content">Laporan Penjualan</h2>
                    <p class="text-sm text-base-content/60">Menggunakan transaksi completed dan snapshot item historis.</p>
                </div>
                <a href="{{ route('admin.reports.sales.export', request()->query()) }}" class="btn btn-outline btn-sm">Export CSV</a>
            </div>
            <form method="GET" class="grid gap-3 lg:grid-cols-[repeat(4,minmax(0,1fr))_auto]">
                <input type="date" name="date_from" value="{{ request('date_from', $from->toDateString()) }}" class="input input-bordered input-sm w-full">
                <input type="date" name="date_to" value="{{ request('date_to', $to->toDateString()) }}" class="input input-bordered input-sm w-full">
                <select name="user_id" class="select select-bordered select-sm w-full">
                    <option value="">Semua kasir</option>
                    @foreach ($cashiers as $cashier)
                        <option value="{{ $cashier->id }}" @selected((string) $userId === (string) $cashier->id)>{{ $cashier->name }}</option>
                    @endforeach
                </select>
                <select name="payment_method" class="select select-bordered select-sm w-full">
                    <option value="">Semua bayar</option>
                    @foreach (['cash' => 'Cash', 'transfer' => 'Transfer', 'qris' => 'QRIS'] as $value => $label)
                        <option value="{{ $value }}" @selected($paymentMethod === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <div class="flex gap-2">
                    <button class="btn btn-primary btn-sm">Terapkan</button>
                    <a href="{{ route('admin.reports.sales') }}" class="btn btn-ghost btn-sm">Reset</a>
                </div>
            </form>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            <div class="stat rounded-xl border border-base-300 bg-base-100 shadow-sm"><div class="stat-title text-xs">Total Revenue</div><div class="stat-value money-value text-xl">{{ $money($summary['total_revenue']) }}</div></div>
            <div class="stat rounded-xl border border-base-300 bg-base-100 shadow-sm"><div class="stat-title text-xs">Total Transaksi</div><div class="stat-value money-value text-xl">{{ $summary['total_transactions'] }}</div></div>
            <div class="stat rounded-xl border border-base-300 bg-base-100 shadow-sm"><div class="stat-title text-xs">Rata-rata</div><div class="stat-value money-value text-xl">{{ $money($summary['average_transaction']) }}</div></div>
            <div class="stat rounded-xl border border-base-300 bg-base-100 shadow-sm"><div class="stat-title text-xs">Total Diskon</div><div class="stat-value money-value text-xl">{{ $money($summary['total_discount']) }}</div></div>
            <div class="stat rounded-xl border border-base-300 bg-base-100 shadow-sm"><div class="stat-title text-xs">Estimasi Profit</div><div class="stat-value money-value text-xl">{{ $money($summary['estimated_profit']) }}</div><div class="stat-desc text-xs">Cost null dianggap 0</div></div>
        </section>

        <section class="rounded-2xl border border-base-300 bg-base-100 p-5 shadow-sm">
            <h3 class="font-bold text-base-content">Tren Penjualan Harian</h3>
            <div class="mt-4 h-72"><canvas id="salesReportChart"></canvas></div>
        </section>

        <section class="overflow-x-auto rounded-2xl border border-base-300 bg-base-100 shadow-sm">
            <table class="table table-sm">
                <thead><tr><th>Invoice</th><th>Tanggal</th><th>Kasir</th><th>Item</th><th>Subtotal</th><th>Diskon</th><th>Total</th><th>Bayar</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse ($transactions as $transaction)
                        <tr>
                            <td><a href="{{ route('admin.transactions.show', $transaction) }}" class="link font-semibold">{{ $transaction->invoice_number }}</a></td>
                            <td>{{ $transaction->created_at->translatedFormat('d M Y H:i') }}</td>
                            <td>{{ $transaction->user?->name ?? '-' }}</td>
                            <td>{{ $transaction->items->sum('quantity') }}</td>
                            <td class="money-value">{{ $money($transaction->subtotal) }}</td>
                            <td class="money-value">{{ $money($transaction->discount_amount) }}</td>
                            <td class="money-value font-semibold">{{ $money($transaction->total_amount) }}</td>
                            <td><span class="badge badge-ghost badge-sm uppercase">{{ $transaction->payment_method }}</span></td>
                            <td><span class="badge badge-success badge-sm">{{ $transaction->status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="py-14 text-center text-sm text-base-content/60">Tidak ada transaksi completed pada filter ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        {{ $transactions->links() }}
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const renderChart = () => {
                if (!window.Chart || !document.getElementById('salesReportChart')) return;
                new window.Chart(document.getElementById('salesReportChart'), {
                    type: 'line',
                    data: {
                        labels: @json($trendLabels),
                        datasets: [{ label: 'Penjualan', data: @json($trendValues), borderColor: '#1e40af', backgroundColor: 'rgba(30,64,175,.10)', tension: .25, fill: true }]
                    },
                    options: { maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
                });
            };
            renderChart();
            window.addEventListener('kasirku:charts-ready', renderChart, { once: true });
        });
    </script>
</x-app-layout>
