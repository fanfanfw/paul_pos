<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransactionService
{
    public function __construct(private readonly StockService $stockService)
    {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createTransaction(array $data, User $kasir): Transaction
    {
        $this->validateUser($kasir);

        for ($attempt = 1; $attempt <= 3; $attempt++) {
            try {
                return DB::transaction(function () use ($data, $kasir): Transaction {
                    $items = $this->normalizeItems($data['items'] ?? []);
                    $products = $this->productsForItems($items);
                    $subtotal = $this->calculateSubtotal($items, $products);
                    $discountType = $data['discount_type'] ?? null;
                    $discountValue = (float) ($data['discount_value'] ?? 0);
                    $discountAmount = $this->calculateDiscount($discountType, $discountValue, $subtotal);
                    $taxAmount = $this->calculateTax($subtotal - $discountAmount);
                    $totalAmount = $subtotal - $discountAmount + $taxAmount;
                    $paymentMethod = (string) ($data['payment_method'] ?? '');
                    [$amountPaid, $changeAmount] = $this->calculatePayment($paymentMethod, (float) ($data['amount_paid'] ?? 0), $totalAmount);
                    $invoiceNumber = $this->generateInvoiceNumber();

                    $transaction = Transaction::query()->create([
                        'user_id' => $kasir->id,
                        'invoice_number' => $invoiceNumber,
                        'subtotal' => $subtotal,
                        'discount_type' => $discountType,
                        'discount_value' => $discountValue,
                        'discount_amount' => $discountAmount,
                        'tax_amount' => $taxAmount,
                        'total_amount' => $totalAmount,
                        'payment_method' => $paymentMethod,
                        'amount_paid' => $amountPaid,
                        'change_amount' => $changeAmount,
                        'status' => 'completed',
                        'notes' => $data['notes'] ?? null,
                    ]);

                    foreach ($items as $productId => $quantity) {
                        $product = $products[$productId];

                        $transaction->items()->create([
                            'product_id' => $product->id,
                            'product_name' => $product->name,
                            'product_code' => $product->code,
                            'price' => $product->price,
                            'cost_price' => $product->cost_price,
                            'quantity' => $quantity,
                            'subtotal' => (float) $product->price * $quantity,
                        ]);

                        $this->stockService->decrementStock($product, $quantity, $invoiceNumber, $kasir);
                    }

                    return $transaction->load(['user', 'items']);
                });
            } catch (QueryException $exception) {
                if ($attempt === 3 || ! $this->isUniqueConstraintViolation($exception)) {
                    throw $exception;
                }
            }
        }

        throw ValidationException::withMessages(['invoice_number' => 'Gagal membuat nomor invoice unik.']);
    }

    private function validateUser(User $user): void
    {
        if (! $user->is_active || ! in_array($user->role, ['admin', 'kasir'], true)) {
            throw ValidationException::withMessages(['user_id' => 'User tidak aktif atau tidak berwenang membuat transaksi.']);
        }
    }

    /**
     * @param  mixed  $items
     * @return array<int, int>
     */
    private function normalizeItems(mixed $items): array
    {
        if (! is_array($items) || $items === []) {
            throw ValidationException::withMessages(['items' => 'Keranjang wajib berisi minimal satu produk.']);
        }

        $normalized = [];

        foreach ($items as $index => $item) {
            $productId = (int) ($item['product_id'] ?? 0);
            $quantity = (int) ($item['quantity'] ?? 0);

            if ($productId < 1) {
                throw ValidationException::withMessages(["items.$index.product_id" => 'Produk wajib dipilih.']);
            }

            if ($quantity < 1) {
                throw ValidationException::withMessages(["items.$index.quantity" => 'Jumlah produk minimal 1.']);
            }

            $normalized[$productId] = ($normalized[$productId] ?? 0) + $quantity;
        }

        return $normalized;
    }

    /**
     * @param  array<int, int>  $items
     * @return array<int, Product>
     */
    private function productsForItems(array $items): array
    {
        $products = Product::query()
            ->with('stock')
            ->whereIn('id', array_keys($items))
            ->get()
            ->keyBy('id');

        $validated = [];

        foreach ($items as $productId => $quantity) {
            $product = $products->get($productId);

            if (! $product) {
                throw ValidationException::withMessages(['items' => 'Produk tidak ditemukan.']);
            }

            if (! $product->is_active) {
                throw ValidationException::withMessages(['items' => "Produk {$product->name} sedang tidak aktif."]);
            }

            if (! $product->stock) {
                throw ValidationException::withMessages(['items' => "Produk {$product->name} belum memiliki stok."]);
            }

            if ($product->stock->quantity < $quantity) {
                throw ValidationException::withMessages(['items' => "Stok {$product->name} tidak mencukupi."]);
            }

            $validated[$productId] = $product;
        }

        return $validated;
    }

    /**
     * @param  array<int, int>  $items
     * @param  array<int, Product>  $products
     */
    private function calculateSubtotal(array $items, array $products): float
    {
        $subtotal = 0;

        foreach ($items as $productId => $quantity) {
            $subtotal += (float) $products[$productId]->price * $quantity;
        }

        return round($subtotal, 2);
    }

    private function calculateDiscount(?string $discountType, float $discountValue, float $subtotal): float
    {
        if ($discountValue < 0) {
            throw ValidationException::withMessages(['discount_value' => 'Diskon tidak boleh minus.']);
        }

        $discountAmount = match ($discountType) {
            null, '' => 0,
            'amount' => $discountValue,
            'percent' => $subtotal * ($discountValue / 100),
            default => throw ValidationException::withMessages(['discount_type' => 'Tipe diskon tidak valid.']),
        };

        $discountAmount = round($discountAmount, 2);

        if ($discountAmount > $subtotal) {
            throw ValidationException::withMessages(['discount_value' => 'Diskon tidak boleh melebihi subtotal.']);
        }

        return $discountAmount;
    }

    private function calculateTax(float $taxableAmount): float
    {
        $taxRate = (float) config('store.tax_rate', 0);

        if ($taxRate <= 0) {
            return 0;
        }

        return round($taxableAmount * ($taxRate / 100), 2);
    }

    /**
     * @return array{0: float, 1: float}
     */
    private function calculatePayment(string $paymentMethod, float $amountPaid, float $totalAmount): array
    {
        if (! in_array($paymentMethod, ['cash', 'transfer', 'qris'], true)) {
            throw ValidationException::withMessages(['payment_method' => 'Metode pembayaran tidak valid.']);
        }

        if ($paymentMethod === 'cash') {
            if ($amountPaid < $totalAmount) {
                throw ValidationException::withMessages(['amount_paid' => 'Uang diterima kurang dari total transaksi.']);
            }

            return [round($amountPaid, 2), round($amountPaid - $totalAmount, 2)];
        }

        return [round($totalAmount, 2), 0];
    }

    private function generateInvoiceNumber(): string
    {
        $prefix = 'INV-'.now()->format('Ymd').'-';
        $lastInvoice = Transaction::query()
            ->where('invoice_number', 'like', $prefix.'%')
            ->lockForUpdate()
            ->orderByDesc('invoice_number')
            ->value('invoice_number');

        $nextSequence = $lastInvoice ? ((int) substr($lastInvoice, -4)) + 1 : 1;

        return $prefix.str_pad((string) $nextSequence, 4, '0', STR_PAD_LEFT);
    }

    private function isUniqueConstraintViolation(QueryException $exception): bool
    {
        return in_array($exception->getCode(), ['23000', '23505'], true);
    }
}
