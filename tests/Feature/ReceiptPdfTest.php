<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReceiptPdfTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $kasir;
    private Transaction $transaction;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'store.name' => 'Toko Test',
            'store.address' => 'Jl. Receipt No. 5',
            'store.phone' => '0800-111',
            'store.footer' => 'Terima kasih test',
        ]);

        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->kasir = User::factory()->create(['role' => 'kasir', 'is_active' => true, 'name' => 'Kasir Receipt']);
        $product = Product::query()->create([
            'code' => 'OLD-001',
            'name' => 'Produk Lama',
            'price' => 10000,
            'cost_price' => 6000,
            'is_active' => true,
        ]);
        $product->stock()->create(['quantity' => 5, 'min_quantity' => 1]);

        $this->actingAs($this->kasir)->post(route('kasir.transactions.store'), [
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
            'discount_type' => 'amount',
            'discount_value' => 1000,
            'payment_method' => 'cash',
            'amount_paid' => 25000,
            'notes' => 'Catatan receipt',
        ])->assertRedirect();

        $this->transaction = Transaction::query()->with('items')->firstOrFail();
        $product->update(['code' => 'NEW-001', 'name' => 'Produk Baru', 'price' => 99999]);
    }

    public function test_admin_can_view_any_transaction_receipt(): void
    {
        $this->actingAs($this->admin)
            ->get(route('kasir.transactions.receipt', $this->transaction))
            ->assertOk()
            ->assertSee('Toko Test')
            ->assertSee($this->transaction->invoice_number)
            ->assertSee('Kasir Receipt')
            ->assertSee('Produk Lama')
            ->assertSee('OLD-001')
            ->assertSee('Rp 20.000')
            ->assertSee('Rp 19.000')
            ->assertDontSee('Produk Baru')
            ->assertDontSee('NEW-001');
    }

    public function test_kasir_can_view_own_transaction_receipt(): void
    {
        $this->actingAs($this->kasir)
            ->get(route('kasir.transactions.receipt', $this->transaction))
            ->assertOk()
            ->assertSee($this->transaction->invoice_number)
            ->assertSee('Produk Lama');
    }

    public function test_kasir_cannot_view_another_kasir_receipt(): void
    {
        $otherKasir = User::factory()->create(['role' => 'kasir', 'is_active' => true]);

        $this->actingAs($otherKasir)
            ->get(route('kasir.transactions.receipt', $this->transaction))
            ->assertForbidden();
    }

    public function test_receipt_page_contains_print_area_and_buttons_outside_print_area(): void
    {
        $response = $this->actingAs($this->kasir)
            ->get(route('kasir.transactions.receipt', $this->transaction))
            ->assertOk()
            ->assertSee('id="receipt-area"', false)
            ->assertSee('window.print()', false)
            ->assertSee('Cetak Struk')
            ->assertSee('Download PDF')
            ->assertSee('@media print', false)
            ->assertSee('.no-print', false)
            ->assertSee('body * { visibility: hidden', false);

        $html = $response->getContent();
        $receiptStart = strpos($html, 'id="receipt-area"');
        $receiptEnd = strpos($html, '<div class="no-print');

        $this->assertNotFalse($receiptStart);
        $this->assertNotFalse($receiptEnd);
        $this->assertStringNotContainsString('Cetak Struk', substr($html, $receiptStart, $receiptEnd - $receiptStart));
    }

    public function test_admin_can_open_any_transaction_pdf(): void
    {
        $this->actingAs($this->admin)
            ->get(route('kasir.transactions.receipt.pdf', $this->transaction))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_kasir_can_open_own_transaction_pdf(): void
    {
        $this->actingAs($this->kasir)
            ->get(route('kasir.transactions.receipt.pdf', $this->transaction))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_kasir_cannot_open_another_kasir_transaction_pdf(): void
    {
        $otherKasir = User::factory()->create(['role' => 'kasir', 'is_active' => true]);

        $this->actingAs($otherKasir)
            ->get(route('kasir.transactions.receipt.pdf', $this->transaction))
            ->assertForbidden();
    }

    public function test_receipt_routes_require_authentication(): void
    {
        $this->app['auth']->guard()->logout();
        $this->flushSession();

        $this->get(route('kasir.transactions.receipt', $this->transaction))->assertRedirect(route('login'));
        $this->get(route('kasir.transactions.receipt.pdf', $this->transaction))->assertRedirect(route('login'));
    }
}
