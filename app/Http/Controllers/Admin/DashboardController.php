<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Transaction;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(ReportService $reportService): View
    {
        $todaySummary = $reportService->getSalesSummary(today(), today());
        $trend = $reportService->getDailyTrend(Carbon::today()->subDays(6), Carbon::today());
        $activeProducts = Product::query()->where('is_active', true)->count();
        $lowStockProducts = Stock::query()->where('quantity', '>', 0)->whereColumn('quantity', '<=', 'min_quantity')->count();
        $latestTransactions = Transaction::query()
            ->completed()
            ->with(['user', 'items'])
            ->latest()
            ->limit(5)
            ->get();
        $criticalStocks = Stock::query()
            ->with('product')
            ->where(function ($query): void {
                $query->where('quantity', 0)->orWhereColumn('quantity', '<=', 'min_quantity');
            })
            ->join('products', 'stocks.product_id', '=', 'products.id')
            ->orderBy('stocks.quantity')
            ->orderBy('products.name')
            ->select('stocks.*')
            ->limit(5)
            ->get();

        return view('admin.dashboard.index', compact('todaySummary', 'trend', 'activeProducts', 'lowStockProducts', 'latestTransactions', 'criticalStocks'));
    }
}
