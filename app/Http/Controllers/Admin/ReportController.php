<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function sales(Request $request, ReportService $reportService): View
    {
        [$from, $to, $userId, $paymentMethod] = $this->salesFilters($request);
        $cashiers = User::query()->whereIn('role', ['admin', 'kasir'])->orderBy('name')->get();
        $summary = $reportService->getSalesSummary($from, $to, $userId, $paymentMethod);
        $trend = $reportService->getDailyTrend($from, $to, $userId, $paymentMethod);
        $transactions = $reportService->completedSalesQuery($from, $to, $userId, $paymentMethod)
            ->with(['user', 'items'])
            ->withCount('items')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.reports.sales', compact('from', 'to', 'userId', 'paymentMethod', 'cashiers', 'summary', 'trend', 'transactions'));
    }

    public function exportSales(Request $request, ReportService $reportService): StreamedResponse
    {
        [$from, $to, $userId, $paymentMethod] = $this->salesFilters($request);

        return $reportService->exportSalesToCsv($from, $to, $userId, $paymentMethod);
    }

    public function stocks(Request $request, ReportService $reportService): View
    {
        $data = $request->validate([
            'status' => ['nullable', 'in:all,aman,menipis,habis'],
        ]);
        $status = $data['status'] ?? 'all';
        $summary = $reportService->getStockSummary();
        $products = Product::query()
            ->with(['category', 'stock'])
            ->whereHas('stock')
            ->when($status === 'aman', fn ($query) => $query->whereHas('stock', fn ($query) => $query->whereColumn('quantity', '>', 'min_quantity')))
            ->when($status === 'menipis', fn ($query) => $query->whereHas('stock', fn ($query) => $query->where('quantity', '>', 0)->whereColumn('quantity', '<=', 'min_quantity')))
            ->when($status === 'habis', fn ($query) => $query->whereHas('stock', fn ($query) => $query->where('quantity', 0)))
            ->join('stocks', 'products.id', '=', 'stocks.product_id')
            ->orderBy('products.name')
            ->select('products.*')
            ->paginate(15)
            ->withQueryString();

        return view('admin.reports.stocks', compact('summary', 'products', 'status'));
    }

    private function salesFilters(Request $request): array
    {
        $data = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'payment_method' => ['nullable', 'in:cash,transfer,qris'],
        ]);

        return [
            isset($data['date_from']) ? Carbon::parse($data['date_from']) : now()->startOfMonth(),
            isset($data['date_to']) ? Carbon::parse($data['date_to']) : now()->endOfMonth(),
            isset($data['user_id']) ? (int) $data['user_id'] : null,
            $data['payment_method'] ?? null,
        ];
    }
}
