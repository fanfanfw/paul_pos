@php
    $money = fn ($value) => 'Rp '.number_format((float) $value, 0, ',', '.');
    $trendLabels = $trend->pluck('label')->values();
    $trendValues = $trend->pluck('total_revenue')->values();
@endphp

<x-app-layout>
    <x-slot name="header">Dashboard Admin</x-slot>

    <div class="space-y-6">
        <section class="flex flex-col gap-3 rounded-2xl border border-base-300 bg-base-100 p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-primary">Admin</p>
                <h2 class="mt-2 text-xl font-bold text-base-content">Ringkasan Operasional</h2>
                <p class="mt-1 text-sm text-base-content/60">Pantau penjualan, transaksi terbaru, dan stok kritis hari ini.</p>
            </div>
            <a href="{{ route('admin.reports.sales') }}" class="btn btn-primary btn-sm">Lihat Laporan</a>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="stat rounded-xl border border-base-300 bg-base-100 shadow-sm">
                <div class="stat-title text-xs text-base-content/60">Total Penjualan Hari Ini</div>
                <div class="stat-value money-value text-2xl font-bold">{{ $money($todaySummary['total_revenue']) }}</div>
                <div class="stat-desc text-xs">Transaksi completed</div>
            </div>
            <div class="stat rounded-xl border border-base-300 bg-base-100 shadow-sm">
                <div class="stat-title text-xs text-base-content/60">Jumlah Transaksi Hari Ini</div>
                <div class="stat-value money-value text-2xl font-bold">{{ $todaySummary['total_transactions'] }}</div>
                <div class="stat-desc text-xs">Hanya transaksi completed</div>
            </div>
            <div class="stat rounded-xl border border-base-300 bg-base-100 shadow-sm">
                <div class="stat-title text-xs text-base-content/60">Total Produk Aktif</div>
                <div class="stat-value money-value text-2xl font-bold">{{ $activeProducts }}</div>
                <div class="stat-desc text-xs">Produk tersedia di kasir</div>
            </div>
            <div class="stat rounded-xl border border-base-300 bg-base-100 shadow-sm">
                <div class="stat-title text-xs text-base-content/60">Produk Stok Menipis</div>
                <div class="stat-value money-value text-2xl font-bold">{{ $lowStockProducts }}</div>
                <div class="stat-desc text-xs">0 &lt; stok <= minimum</div>
            </div>
        </section>

        <section class="rounded-2xl border border-base-300 bg-base-100 p-5 shadow-sm">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <h3 class="font-bold text-base-content">Penjualan 7 Hari Terakhir</h3>
                    <p class="text-sm text-base-content/60">Total penjualan completed per hari.</p>
                </div>
            </div>
            <div class="h-72"><canvas id="adminSalesChart"></canvas></div>
        </section>

        <section class="grid gap-5 xl:grid-cols-2">
            <div class="rounded-2xl border border-base-300 bg-base-100 p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="font-bold text-base-content">5 Transaksi Terbaru</h3>
                    <a href="{{ route('admin.transactions.index') }}" class="btn btn-ghost btn-xs">Lihat Semua</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="table table-sm">
                        <thead><tr><th>Invoice</th><th>Kasir</th><th>Total</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse ($latestTransactions as $transaction)
                                <tr>
                                    <td><a class="link font-semibold" href="{{ route('admin.transactions.show', $transaction) }}">{{ $transaction->invoice_number }}</a></td>
                                    <td>{{ $transaction->user?->name ?? '-' }}</td>
                                    <td class="money-value">{{ $money($transaction->total_amount) }}</td>
                                    <td><span class="badge badge-success badge-sm">{{ $transaction->status }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-10 text-center text-sm text-base-content/60">Belum ada transaksi.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-2xl border border-base-300 bg-base-100 p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="font-bold text-base-content">5 Stok Kritis</h3>
                    <a href="{{ route('admin.reports.stocks') }}" class="btn btn-ghost btn-xs">Lihat Laporan</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="table table-sm">
                        <thead><tr><th>Produk</th><th>Stok</th><th>Min</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse ($criticalStocks as $stock)
                                @php
                                    $status = $stock->quantity === 0 ? 'habis' : 'menipis';
                                    $badge = $status === 'habis' ? 'badge-error' : 'badge-warning';
                                @endphp
                                <tr>
                                    <td>{{ $stock->product?->name ?? '-' }}</td>
                                    <td class="money-value">{{ $stock->quantity }}</td>
                                    <td>{{ $stock->min_quantity }}</td>
                                    <td><span class="badge {{ $badge }} badge-sm">{{ ucfirst($status) }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-10 text-center text-sm text-base-content/60">Tidak ada stok kritis.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const renderChart = () => {
                if (!window.Chart || !document.getElementById('adminSalesChart')) return;
                new window.Chart(document.getElementById('adminSalesChart'), {
                    type: 'bar',
                    data: {
                        labels: @json($trendLabels),
                        datasets: [{ label: 'Penjualan', data: @json($trendValues), backgroundColor: '#1e40af', borderRadius: 6 }]
                    },
                    options: { maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
                });
            };
            renderChart();
            window.addEventListener('kasirku:charts-ready', renderChart, { once: true });
        });
    </script>
</x-app-layout>
