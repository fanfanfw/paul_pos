<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdjustStockRequest;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockController extends Controller
{
    public function index(Request $request): View
    {
        $stocks = Stock::query()
            ->with('product')
            ->whereHas('product', function ($query) use ($request): void {
                $query->when($request->filled('search'), function ($query) use ($request): void {
                    $query->searchCatalog((string) $request->string('search'));
                });
            })
            ->when($request->input('status') === 'aman', function ($query): void {
                $query->whereColumn('quantity', '>', 'min_quantity');
            })
            ->when($request->input('status') === 'menipis', function ($query): void {
                $query->where('quantity', '>', 0)->whereColumn('quantity', '<=', 'min_quantity');
            })
            ->when($request->input('status') === 'habis', function ($query): void {
                $query->where('quantity', 0);
            })
            ->join('products', 'stocks.product_id', '=', 'products.id')
            ->orderBy('products.name')
            ->select('stocks.*')
            ->paginate(15)
            ->withQueryString();

        return view('admin.stocks.index', compact('stocks'));
    }

    public function adjust(Stock $stock): View
    {
        $stock->load('product');

        return view('admin.stocks.adjust', compact('stock'));
    }

    public function update(AdjustStockRequest $request, Stock $stock, StockService $stockService): RedirectResponse
    {
        $stock->load('product');
        $quantity = (int) $request->integer('quantity');

        match ($request->input('type')) {
            'in' => $stockService->incrementStock($stock->product, $quantity, (string) $request->input('notes'), $request->user()),
            'out' => $stockService->decrementStock($stock->product, $quantity, (string) $request->input('reference'), $request->user(), $request->input('notes')),
            default => $stockService->adjustStock($stock->product, $quantity, (string) $request->input('notes'), $request->user(), $request->input('reference')),
        };

        return redirect()->route('admin.stocks.index')->with('success', 'Stok berhasil disesuaikan.');
    }

    public function movements(Request $request): View
    {
        $products = Product::query()->orderBy('name')->get();
        $movements = StockMovement::query()
            ->with(['product', 'user'])
            ->when($request->filled('product_id'), function ($query) use ($request): void {
                $query->where('product_id', $request->integer('product_id'));
            })
            ->when($request->filled('type'), function ($query) use ($request): void {
                $query->where('type', $request->input('type'));
            })
            ->when($request->filled('date_from'), function ($query) use ($request): void {
                $query->whereDate('created_at', '>=', $request->date('date_from'));
            })
            ->when($request->filled('date_to'), function ($query) use ($request): void {
                $query->whereDate('created_at', '<=', $request->date('date_to'));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.stocks.movements', compact('movements', 'products'));
    }
}
