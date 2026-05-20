<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionCoreTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $kasir;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->kasir = User::factory()->create(['role' => 'kasir', 'is_active' => true]);
        $this->product = $this->createProduct('TRX-001', 'Produk Transaksi', 10000, 10);
    }

    public function test_cash_transaction_succeeds_and_decreases_stock(): void
    {
        $this->actingAs($this->kasir)->post(route('kasir.transactions.store'), [
            'items' => [['product_id' => $this->product->id, 'quantity' => 2]],
            'discount_type' => 'amount',
            'discount_value' => 1000,
            'payment_method' => 'cash',
            'amount_paid' => 25000,
        ])->assertRedirect();

        $transaction = Transaction::query()->firstOrFail();
        $this->assertSame('cash', $transaction->payment_method);
        $this->assertEquals(20000, (float) $transaction->subtotal);
        $this->assertEquals(19000, (float) $transaction->total_amount);
        $this->assertEquals(6000, (float) $transaction->change_amount);
        $this->assertSame(8, $this->product->stock()->first()->quantity);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'type' => 'out',
            'quantity' => 2,
            'reference' => $transaction->invoice_number,
        ]);
    }

    public function test_cash_less_than_total_is_rejected(): void
    {
        $this->actingAs($this->kasir)->from(route('kasir.transactions.create'))->post(route('kasir.transactions.store'), [
            'items' => [['product_id' => $this->product->id, 'quantity' => 2]],
            'payment_method' => 'cash',
            'amount_paid' => 10000,
        ])->assertSessionHasErrors('amount_paid');

        $this->assertSame(0, Transaction::query()->count());
        $this->assertSame(10, $this->product->stock()->first()->quantity);
    }

    public function test_transfer_succeeds_without_manual_amount_paid(): void
    {
        $this->actingAs($this->kasir)->post(route('kasir.transactions.store'), [
            'items' => [['product_id' => $this->product->id, 'quantity' => 1]],
            'payment_method' => 'transfer',
        ])->assertRedirect();

        $transaction = Transaction::query()->firstOrFail();
        $this->assertEquals(10000, (float) $transaction->amount_paid);
        $this->assertEquals(0, (float) $transaction->change_amount);
    }

    public function test_qris_succeeds_without_manual_amount_paid(): void
    {
        $this->actingAs($this->kasir)->post(route('kasir.transactions.store'), [
            'items' => [['product_id' => $this->product->id, 'quantity' => 1]],
            'payment_method' => 'qris',
        ])->assertRedirect();

        $transaction = Transaction::query()->firstOrFail();
        $this->assertEquals(10000, (float) $transaction->amount_paid);
        $this->assertEquals(0, (float) $transaction->change_amount);
    }

    public function test_quantity_exceeding_stock_is_rejected(): void
    {
        $this->actingAs($this->kasir)->post(route('kasir.transactions.store'), [
            'items' => [['product_id' => $this->product->id, 'quantity' => 99]],
            'payment_method' => 'cash',
            'amount_paid' => 990000,
        ])->assertSessionHasErrors('items');
    }

    public function test_inactive_product_is_rejected(): void
    {
        $this->product->update(['is_active' => false]);

        $this->actingAs($this->kasir)->post(route('kasir.transactions.store'), [
            'items' => [['product_id' => $this->product->id, 'quantity' => 1]],
            'payment_method' => 'cash',
            'amount_paid' => 10000,
        ])->assertSessionHasErrors('items');
    }

    public function test_soft_deleted_product_is_rejected(): void
    {
        $this->product->delete();

        $this->actingAs($this->kasir)->post(route('kasir.transactions.store'), [
            'items' => [['product_id' => $this->product->id, 'quantity' => 1]],
            'payment_method' => 'cash',
            'amount_paid' => 10000,
        ])->assertSessionHasErrors('items');
    }

    public function test_transaction_items_store_snapshots(): void
    {
        $this->actingAs($this->kasir)->post(route('kasir.transactions.store'), [
            'items' => [['product_id' => $this->product->id, 'quantity' => 2]],
            'payment_method' => 'cash',
            'amount_paid' => 20000,
        ])->assertRedirect();

        $this->product->update(['name' => 'Nama Baru', 'price' => 12000]);

        $item = TransactionItem::query()->firstOrFail();
        $this->assertSame('Produk Transaksi', $item->product_name);
        $this->assertSame('TRX-001', $item->product_code);
        $this->assertEquals(10000, (float) $item->price);
        $this->assertEquals(7000, (float) $item->cost_price);
        $this->assertEquals(20000, (float) $item->subtotal);
    }

    public function test_invoice_number_is_unique(): void
    {
        $this->actingAs($this->kasir)->post(route('kasir.transactions.store'), [
            'items' => [['product_id' => $this->product->id, 'quantity' => 1]],
            'payment_method' => 'cash',
            'amount_paid' => 10000,
        ])->assertRedirect();

        $this->actingAs($this->kasir)->post(route('kasir.transactions.store'), [
            'items' => [['product_id' => $this->product->id, 'quantity' => 1]],
            'payment_method' => 'cash',
            'amount_paid' => 10000,
        ])->assertRedirect();

        $invoices = Transaction::query()->pluck('invoice_number');
        $this->assertCount(2, $invoices->unique());
        $this->assertStringStartsWith('INV-'.now()->format('Ymd').'-', $invoices->first());
    }

    public function test_product_search_returns_active_products_with_stock(): void
    {
        $emptyProduct = $this->createProduct('TRX-EMPTY', 'Produk Habis', 5000, 0);

        $response = $this->actingAs($this->kasir)->getJson(route('kasir.api.products.search', ['q' => 'TRX']));

        $response->assertOk()->assertJsonFragment([
            'id' => $this->product->id,
            'code' => 'TRX-001',
            'stock' => 10,
        ])->assertJsonFragment([
            'id' => $emptyProduct->id,
            'code' => 'TRX-EMPTY',
            'stock' => 0,
        ]);
    }

    public function test_product_search_is_case_insensitive_and_tolerates_o_instead_of_zero(): void
    {
        $product = $this->createProduct('MKN001', 'Nasi Goreng Instan', 18000, 5);

        $this->actingAs($this->kasir)
            ->getJson(route('kasir.api.products.search', ['q' => 'nasi goreng']))
            ->assertOk()
            ->assertJsonFragment([
                'id' => $product->id,
                'code' => 'MKN001',
                'name' => 'Nasi Goreng Instan',
            ]);

        $this->actingAs($this->kasir)
            ->getJson(route('kasir.api.products.search', ['q' => 'MKNOO1']))
            ->assertOk()
            ->assertJsonFragment([
                'id' => $product->id,
                'code' => 'MKN001',
            ]);
    }

    public function test_kasir_can_access_transaction_create(): void
    {
        $this->actingAs($this->kasir)->get(route('kasir.transactions.create'))->assertOk();
    }

    public function test_transaction_create_uses_relative_product_search_url(): void
    {
        config(['app.url' => 'https://wrong.example.test']);

        $this->actingAs($this->kasir)
            ->get(route('kasir.transactions.create'))
            ->assertOk()
            ->assertSee("searchUrl: '/kasir/api/products/search'", false)
            ->assertDontSee('https://wrong.example.test/kasir/api/products/search', false);
    }

    public function test_kasir_cannot_access_admin_routes(): void
    {
        $this->actingAs($this->kasir)->get(route('admin.products.index'))->assertForbidden();
    }

    public function test_kasir_cannot_access_another_kasir_receipt_placeholder(): void
    {
        $otherKasir = User::factory()->create(['role' => 'kasir', 'is_active' => true]);
        $transaction = Transaction::query()->create([
            'user_id' => $otherKasir->id,
            'invoice_number' => 'INV-'.now()->format('Ymd').'-9998',
            'subtotal' => 10000,
            'discount_value' => 0,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 10000,
            'payment_method' => 'cash',
            'amount_paid' => 10000,
            'change_amount' => 0,
            'status' => 'completed',
        ]);

        $this->actingAs($this->kasir)->get(route('kasir.transactions.receipt', $transaction))->assertForbidden();
    }

    public function test_admin_can_access_any_receipt_placeholder_and_create_transaction(): void
    {
        $this->actingAs($this->admin)->post(route('kasir.transactions.store'), [
            'items' => [['product_id' => $this->product->id, 'quantity' => 1]],
            'payment_method' => 'cash',
            'amount_paid' => 10000,
        ])->assertRedirect();

        $transaction = Transaction::query()->firstOrFail();
        $this->actingAs($this->admin)->get(route('kasir.transactions.receipt', $transaction))->assertOk();
    }

    public function test_zero_stock_product_cannot_be_sold(): void
    {
        $emptyProduct = $this->createProduct('TRX-ZERO', 'Produk Kosong', 5000, 0);

        $this->actingAs($this->kasir)->post(route('kasir.transactions.store'), [
            'items' => [['product_id' => $emptyProduct->id, 'quantity' => 1]],
            'payment_method' => 'cash',
            'amount_paid' => 5000,
        ])->assertSessionHasErrors('items');

        $this->assertSame(0, StockMovement::query()->where('product_id', $emptyProduct->id)->count());
    }

    private function createProduct(string $code, string $name, int $price, int $stock): Product
    {
        $product = Product::query()->create([
            'code' => $code,
            'name' => $name,
            'price' => $price,
            'cost_price' => 7000,
            'is_active' => true,
        ]);

        $product->stock()->create(['quantity' => $stock, 'min_quantity' => 2]);

        return $product;
    }
}
