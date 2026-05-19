<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class StockServiceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Product $product;
    private StockService $stockService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->product = Product::query()->create([
            'code' => 'STK-SVC',
            'name' => 'Produk Service',
            'price' => 10000,
            'cost_price' => 7000,
            'is_active' => true,
        ]);
        $this->product->stock()->create(['quantity' => 10, 'min_quantity' => 2]);
        $this->stockService = app(StockService::class);
    }

    public function test_increment_works_and_creates_positive_movement(): void
    {
        $this->stockService->incrementStock($this->product, 5, 'Tambah stok', $this->user);

        $this->assertSame(15, $this->product->stock()->first()->quantity);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'type' => 'in',
            'quantity' => 5,
            'before_quantity' => 10,
            'after_quantity' => 15,
        ]);
    }

    public function test_decrement_works_and_creates_positive_movement(): void
    {
        $this->stockService->decrementStock($this->product, 4, 'INV-TEST', $this->user);

        $this->assertSame(6, $this->product->stock()->first()->quantity);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'type' => 'out',
            'quantity' => 4,
            'before_quantity' => 10,
            'after_quantity' => 6,
            'reference' => 'INV-TEST',
        ]);
    }

    public function test_decrement_rejects_insufficient_stock(): void
    {
        $this->expectException(ValidationException::class);

        $this->stockService->decrementStock($this->product, 11, 'INV-FAIL', $this->user);
    }

    public function test_adjustment_sets_exact_quantity_and_creates_positive_delta_movement(): void
    {
        $this->stockService->adjustStock($this->product, 3, 'Set akhir', $this->user, 'ADJ-001');

        $this->assertSame(3, $this->product->stock()->first()->quantity);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'type' => 'adjustment',
            'quantity' => 7,
            'before_quantity' => 10,
            'after_quantity' => 3,
            'reference' => 'ADJ-001',
        ]);
    }

    public function test_adjustment_rejects_no_op_and_does_not_create_movement(): void
    {
        try {
            $this->stockService->adjustStock($this->product, 10, 'Sama', $this->user);
            $this->fail('No-op adjustment should be rejected.');
        } catch (ValidationException) {
            $this->assertSame(10, $this->product->stock()->first()->quantity);
            $this->assertSame(0, StockMovement::query()->where('product_id', $this->product->id)->count());
        }
    }
}
