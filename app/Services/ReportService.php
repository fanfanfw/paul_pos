<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportService
{
    public function getSalesSummary(Carbon $from, Carbon $to, ?int $userId = null, ?string $paymentMethod = null): array
    {
        $query = $this->completedSalesQuery($from, $to, $userId, $paymentMethod);

        $totalTransactions = (clone $query)->count();
        $totalRevenue = (float) (clone $query)->sum('total_amount');
        $totalDiscount = (float) (clone $query)->sum('discount_amount');
        $estimatedProfit = $this->estimatedProfit($from, $to, $userId, $paymentMethod);

        return [
            'total_revenue' => $totalRevenue,
            'total_transactions' => $totalTransactions,
            'average_transaction' => $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0,
            'total_discount' => $totalDiscount,
            'estimated_profit' => $estimatedProfit,
        ];
    }

    public function getDailyTrend(Carbon $from, Carbon $to, ?int $userId = null, ?string $paymentMethod = null): Collection
    {
        $rows = $this->completedSalesQuery($from, $to, $userId, $paymentMethod)
            ->selectRaw('DATE(created_at) as sale_date, SUM(total_amount) as total_revenue, COUNT(*) as total_transactions')
            ->groupBy('sale_date')
            ->orderBy('sale_date')
            ->get()
            ->keyBy(fn (Transaction $transaction): string => Carbon::parse($transaction->sale_date)->toDateString());

        $days = collect();
        for ($date = $from->copy()->startOfDay(); $date->lte($to->copy()->startOfDay()); $date->addDay()) {
            $days->push($date->copy());
        }

        return $days->map(function (Carbon $date) use ($rows): array {
                $key = $date->toDateString();
                $row = $rows->get($key);

                return [
                    'date' => $key,
                    'label' => $date->translatedFormat('d M'),
                    'total_revenue' => (float) ($row->total_revenue ?? 0),
                    'total_transactions' => (int) ($row->total_transactions ?? 0),
                ];
            });
    }

    public function getStockSummary(): array
    {
        $totalProducts = Product::query()->count();
        $lowStock = Product::query()
            ->whereHas('stock', fn (Builder $query) => $query->where('quantity', '>', 0)->whereColumn('quantity', '<=', 'min_quantity'))
            ->count();
        $outOfStock = Product::query()
            ->whereHas('stock', fn (Builder $query) => $query->where('quantity', 0))
            ->count();

        return [
            'total_products' => $totalProducts,
            'low_stock' => $lowStock,
            'out_of_stock' => $outOfStock,
        ];
    }

    public function exportSalesToCsv(Carbon $from, Carbon $to, ?int $userId = null, ?string $paymentMethod = null): StreamedResponse
    {
        $fileName = 'laporan-penjualan-'.$from->toDateString().'-'.$to->toDateString().'.csv';

        return response()->streamDownload(function () use ($from, $to, $userId, $paymentMethod): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['No Invoice', 'Tanggal', 'Kasir', 'Subtotal', 'Diskon', 'Pajak', 'Total', 'Metode Bayar', 'Status']);

            $this->completedSalesQuery($from, $to, $userId, $paymentMethod)
                ->with('user')
                ->orderBy('created_at')
                ->chunk(200, function (Collection $transactions) use ($handle): void {
                    foreach ($transactions as $transaction) {
                        fputcsv($handle, [
                            $transaction->invoice_number,
                            $transaction->created_at->format('Y-m-d H:i:s'),
                            $transaction->user?->name ?? '-',
                            (float) $transaction->subtotal,
                            (float) $transaction->discount_amount,
                            (float) $transaction->tax_amount,
                            (float) $transaction->total_amount,
                            $transaction->payment_method,
                            $transaction->status,
                        ]);
                    }
                });

            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function completedSalesQuery(Carbon $from, Carbon $to, ?int $userId = null, ?string $paymentMethod = null): Builder
    {
        return Transaction::query()
            ->completed()
            ->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->when($userId, fn (Builder $query) => $query->where('user_id', $userId))
            ->when($paymentMethod, fn (Builder $query) => $query->where('payment_method', $paymentMethod));
    }

    private function estimatedProfit(Carbon $from, Carbon $to, ?int $userId = null, ?string $paymentMethod = null): float
    {
        $row = DB::table('transaction_items')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->where('transactions.status', 'completed')
            ->whereBetween('transactions.created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->when($userId, fn ($query) => $query->where('transactions.user_id', $userId))
            ->when($paymentMethod, fn ($query) => $query->where('transactions.payment_method', $paymentMethod))
            ->selectRaw('SUM(transaction_items.subtotal - (COALESCE(transaction_items.cost_price, 0) * transaction_items.quantity)) as estimated_profit')
            ->first();

        return (float) ($row->estimated_profit ?? 0);
    }
}
