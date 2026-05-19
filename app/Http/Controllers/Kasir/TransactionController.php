<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kasir\StoreTransactionRequest;
use App\Models\Product;
use App\Models\Transaction;
use App\Services\TransactionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function create(): View
    {
        return view('kasir.transactions.create', [
            'taxRate' => (float) config('store.tax_rate', 0),
        ]);
    }

    public function store(StoreTransactionRequest $request, TransactionService $transactionService): RedirectResponse
    {
        $transaction = $transactionService->createTransaction($request->validated(), $request->user());

        return redirect()
            ->route('kasir.transactions.receipt', $transaction)
            ->with('success', 'Transaksi berhasil disimpan.');
    }

    public function searchProducts(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('q', ''));

        $products = Product::query()
            ->with('stock')
            ->where('is_active', true)
            ->when($search !== '', function ($query) use ($search): void {
                $query->searchCatalog($search);
            })
            ->leftJoin('stocks', 'products.id', '=', 'stocks.product_id')
            ->orderByRaw('CASE WHEN COALESCE(stocks.quantity, 0) > 0 THEN 0 ELSE 1 END')
            ->orderBy('products.name')
            ->select('products.*')
            ->limit(20)
            ->get()
            ->map(fn (Product $product): array => [
                'id' => $product->id,
                'code' => $product->code,
                'name' => $product->name,
                'price' => (float) $product->price,
                'stock' => $product->stockQuantity(),
                'image_url' => $product->imageUrl(),
            ]);

        return response()->json($products);
    }

    public function receipt(Transaction $transaction): View
    {
        $this->authorizeReceipt($transaction);
        $transaction->load(['user', 'items']);

        return view('kasir.transactions.receipt', compact('transaction'));
    }

    public function receiptPdf(Transaction $transaction): Response
    {
        $this->authorizeReceipt($transaction);
        $transaction->load(['user', 'items']);

        $pdf = Pdf::loadView('kasir.transactions.receipt-pdf', compact('transaction'))
            ->setPaper([0, 0, 226.77, 595.28], 'portrait');

        return $pdf->stream($transaction->invoice_number.'.pdf');
    }

    private function authorizeReceipt(Transaction $transaction): void
    {
        $user = request()->user();

        if (! $user->isAdmin() && $transaction->user_id !== $user->id) {
            abort(403);
        }
    }
}
