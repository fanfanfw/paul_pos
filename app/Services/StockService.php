<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockService
{
    public function decrementStock(Product $product, int $quantity, string $reference, User $user): void
    {
        DB::transaction(function () use ($product, $quantity, $reference, $user): void {
            if ($quantity < 1) {
                throw ValidationException::withMessages(['quantity' => 'Jumlah stok keluar harus minimal 1.']);
            }

            $stock = $this->lockedStockFor($product);
            $before = $stock->quantity;
            $after = $before - $quantity;

            if ($after < 0) {
                throw ValidationException::withMessages(['quantity' => 'Stok tidak mencukupi.']);
            }

            $this->saveMovement($stock, $user, 'out', $quantity, $before, $after, null, $reference);
        });
    }

    public function incrementStock(Product $product, int $quantity, string $notes, User $user): void
    {
        DB::transaction(function () use ($product, $quantity, $notes, $user): void {
            if ($quantity < 1) {
                throw ValidationException::withMessages(['quantity' => 'Jumlah stok masuk harus minimal 1.']);
            }

            $stock = $this->lockedStockFor($product);
            $before = $stock->quantity;
            $after = $before + $quantity;

            $this->saveMovement($stock, $user, 'in', $quantity, $before, $after, $notes, null);
        });
    }

    public function adjustStock(Product $product, int $newQuantity, string $notes, User $user, ?string $reference = null): void
    {
        DB::transaction(function () use ($product, $newQuantity, $notes, $user, $reference): void {
            if ($newQuantity < 0) {
                throw ValidationException::withMessages(['quantity' => 'Stok tidak boleh minus.']);
            }

            $stock = $this->lockedStockFor($product);
            $before = $stock->quantity;

            if ($newQuantity === $before) {
                throw ValidationException::withMessages(['quantity' => 'Jumlah stok baru sama dengan stok saat ini.']);
            }

            $this->saveMovement($stock, $user, 'adjustment', abs($newQuantity - $before), $before, $newQuantity, $notes, $reference);
        });
    }

    private function lockedStockFor(Product $product): Stock
    {
        $stockCount = Stock::query()->where('product_id', $product->id)->count();

        if ($stockCount !== 1) {
            throw ValidationException::withMessages(['product_id' => 'Produk harus memiliki tepat satu record stok.']);
        }

        return Stock::query()
            ->where('product_id', $product->id)
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function saveMovement(
        Stock $stock,
        User $user,
        string $type,
        int $quantity,
        int $before,
        int $after,
        ?string $notes,
        ?string $reference
    ): void {
        if ($quantity < 1) {
            throw ValidationException::withMessages(['quantity' => 'Jumlah pergerakan stok harus lebih dari 0.']);
        }

        $stock->update(['quantity' => $after]);

        StockMovement::query()->create([
            'product_id' => $stock->product_id,
            'user_id' => $user->id,
            'type' => $type,
            'quantity' => $quantity,
            'before_quantity' => $before,
            'after_quantity' => $after,
            'notes' => $notes,
            'reference' => $reference,
        ]);
    }
}
