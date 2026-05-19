@php $money = fn ($value) => 'Rp '.number_format((float) $value, 0, ',', '.'); @endphp

<x-app-layout>
    <x-slot name="header">Transaksi</x-slot>

    <div class="space-y-5">
        <section class="filter-card rounded-2xl p-5">
            <div class="mb-4">
                <h2 class="text-xl font-bold text-base-content">Daftar Transaksi</h2>
                <p class="text-sm text-base-content/60">Lihat transaksi tanpa edit, hapus, refund, atau split payment.</p>
            </div>
            <form method="GET" class="grid gap-3 lg:grid-cols-7">
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="input input-bordered input-sm w-full">
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="input input-bordered input-sm w-full">
                <select name="user_id" class="select select-bordered select-sm w-full">
                    <option value="">Semua kasir</option>
                    @foreach ($cashiers as $cashier)
                        <option value="{{ $cashier->id }}" @selected((string) request('user_id') === (string) $cashier->id)>{{ $cashier->name }}</option>
                    @endforeach
                </select>
                <select name="payment_method" class="select select-bordered select-sm w-full">
                    <option value="">Semua bayar</option>
                    @foreach (['cash' => 'Cash', 'transfer' => 'Transfer', 'qris' => 'QRIS'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('payment_method') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="status" class="select select-bordered select-sm w-full">
                    <option value="">Semua status</option>
                    <option value="completed" @selected(request('status') === 'completed')>Completed</option>
                    <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
                </select>
                <input name="search" value="{{ request('search') }}" class="input input-bordered input-sm w-full" placeholder="Cari invoice">
                <div class="flex gap-2">
                    <button class="btn btn-primary btn-sm">Filter</button>
                    <a href="{{ route('admin.transactions.index') }}" class="btn btn-ghost btn-sm">Reset</a>
                </div>
            </form>
        </section>

        <section class="overflow-x-auto rounded-2xl border border-base-300 bg-base-100/90 shadow-sm">
            <table class="table table-sm">
                <thead><tr><th>Invoice</th><th>Kasir</th><th>Tanggal</th><th>Item</th><th>Total</th><th>Bayar</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse ($transactions as $transaction)
                        <tr class="hover:bg-base-200/70">
                            <td class="font-semibold">{{ $transaction->invoice_number }}</td>
                            <td>{{ $transaction->user?->name ?? '-' }}</td>
                            <td>{{ $transaction->created_at->translatedFormat('d M Y H:i') }}</td>
                            <td>{{ $transaction->items->sum('quantity') }}</td>
                            <td class="money-value">{{ $money($transaction->total_amount) }}</td>
                            <td><span class="badge badge-ghost badge-sm uppercase">{{ $transaction->payment_method }}</span></td>
                            <td><span class="badge badge-success badge-sm">{{ $transaction->status }}</span></td>
                            <td class="text-right"><a href="{{ route('admin.transactions.show', $transaction) }}" class="btn btn-primary btn-xs">Detail</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="py-14 text-center text-sm text-base-content/60">Tidak ada transaksi sesuai filter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        {{ $transactions->links() }}
    </div>
</x-app-layout>
