<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardReportTest extends TestCase
{
    use RefreshDatabase;

    private int $invoiceSequence = 1;

    private User $admin;
    private User $kasir;
    private User $otherKasir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true, 'name' => 'Admin Report']);
        $this->kasir = User::factory()->create(['role' => 'kasir', 'is_active' => true, 'name' => 'Kasir A']);
        $this->otherKasir = User::factory()->create(['role' => 'kasir', 'is_active' => true, 'name' => 'Kasir B']);
    }

    public function test_admin_dashboard_uses_completed_transactions_for_metrics(): void
    {
        $this->createTransaction($this->kasir, total: 100000, status: 'completed', createdAt: now());
        $this->createTransaction($this->kasir, total: 999000, status: 'cancelled', createdAt: now());

        $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Rp 100.000')
            ->assertDontSee('Rp 999.000')
            ->assertSee('adminSalesChart', false)
            ->assertDontSee('cdn.jsdelivr.net', false);
    }

    public function test_kasir_cannot_access_admin_dashboard(): void
    {
        $this->actingAs($this->kasir)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_kasir_dashboard_only_shows_own_transaction_totals(): void
    {
        $this->createTransaction($this->kasir, total: 75000, createdAt: now());
        $this->createTransaction($this->otherKasir, total: 125000, createdAt: now());

        $this->actingAs($this->kasir)
            ->get(route('kasir.dashboard'))
            ->assertOk()
            ->assertSee('Rp 75.000')
            ->assertDontSee('Rp 125.000');
    }

    public function test_admin_transaction_index_and_detail_work_with_snapshot_items(): void
    {
        $transaction = $this->createTransaction($this->kasir, invoice: 'INV-DETAIL', productName: 'Snapshot Item', productCode: 'SNAP-001');

        $this->actingAs($this->admin)
            ->get(route('admin.transactions.index'))
            ->assertOk()
            ->assertSee('INV-DETAIL')
            ->assertSee('Kasir A');

        $this->actingAs($this->admin)
            ->get(route('admin.transactions.show', $transaction))
            ->assertOk()
            ->assertSee('Snapshot Item')
            ->assertSee('SNAP-001');
    }

    public function test_admin_transaction_index_filters_by_inclusive_date_range(): void
    {
        $this->createTransaction($this->kasir, invoice: 'INV-IN-RANGE', createdAt: Carbon::parse('2026-05-10 23:59:00'));
        $this->createTransaction($this->kasir, invoice: 'INV-OUT-RANGE', createdAt: Carbon::parse('2026-05-11 00:01:00'));

        $this->actingAs($this->admin)
            ->get(route('admin.transactions.index', [
                'date_from' => '2026-05-10',
                'date_to' => '2026-05-10',
            ]))
            ->assertOk()
            ->assertSee('INV-IN-RANGE')
            ->assertDontSee('INV-OUT-RANGE');
    }

    public function test_kasir_cannot_access_admin_transaction_routes_and_no_delete_route_exists(): void
    {
        $transaction = $this->createTransaction($this->kasir);

        $this->actingAs($this->kasir)->get(route('admin.transactions.index'))->assertForbidden();
        $this->actingAs($this->kasir)->get(route('admin.transactions.show', $transaction))->assertForbidden();
        $this->actingAs($this->admin)->delete('/admin/transactions/'.$transaction->id)->assertStatus(405);
    }

    public function test_sales_report_filters_and_summary_totals_are_correct(): void
    {
        $this->createTransaction($this->kasir, invoice: 'INV-CASH', subtotal: 100000, discount: 10000, tax: 0, total: 90000, paymentMethod: 'cash', costPrice: 30000, createdAt: Carbon::parse('2026-05-10 10:00:00'));
        $this->createTransaction($this->otherKasir, invoice: 'INV-QRIS', subtotal: 80000, discount: 0, tax: 0, total: 80000, paymentMethod: 'qris', costPrice: 20000, createdAt: Carbon::parse('2026-05-11 10:00:00'));
        $this->createTransaction($this->kasir, invoice: 'INV-APRIL', total: 50000, paymentMethod: 'cash', createdAt: Carbon::parse('2026-04-30 10:00:00'));

        $this->actingAs($this->admin)
            ->get(route('admin.reports.sales', [
                'date_from' => '2026-05-01',
                'date_to' => '2026-05-31',
                'user_id' => $this->kasir->id,
                'payment_method' => 'cash',
            ]))
            ->assertOk()
            ->assertSee('Rp 90.000')
            ->assertSee('Rp 10.000')
            ->assertSee('Rp 70.000')
            ->assertSee('INV-CASH')
            ->assertDontSee('INV-QRIS')
            ->assertDontSee('INV-APRIL')
            ->assertSee('salesReportChart', false)
            ->assertDontSee('cdn.jsdelivr.net', false);
    }

    public function test_csv_export_respects_filters_and_kasir_cannot_export(): void
    {
        $this->createTransaction($this->kasir, invoice: 'INV-EXPORT-YES', total: 40000, paymentMethod: 'transfer', createdAt: Carbon::parse('2026-05-12 10:00:00'));
        $this->createTransaction($this->otherKasir, invoice: 'INV-EXPORT-NO', total: 60000, paymentMethod: 'cash', createdAt: Carbon::parse('2026-05-12 11:00:00'));

        $response = $this->actingAs($this->admin)->get(route('admin.reports.sales.export', [
            'date_from' => '2026-05-01',
            'date_to' => '2026-05-31',
            'payment_method' => 'transfer',
        ]));

        $response->assertOk();
        $content = $response->streamedContent();
        foreach (['No Invoice', 'Tanggal', 'Kasir', 'Subtotal', 'Diskon', 'Pajak', 'Total', 'Metode Bayar', 'Status'] as $column) {
            $this->assertStringContainsString($column, $content);
        }
        $this->assertStringContainsString('INV-EXPORT-YES', $content);
        $this->assertStringNotContainsString('INV-EXPORT-NO', $content);

        $this->actingAs($this->kasir)->get(route('admin.reports.sales.export'))->assertForbidden();
    }

    public function test_stock_report_counts_and_status_filter_work(): void
    {
        $category = Category::query()->create(['name' => 'Kategori Stok']);
        $this->createProductWithStock($category, 'SAFE-001', 'Produk Aman', 10, 3);
        $this->createProductWithStock($category, 'LOW-001', 'Produk Menipis', 2, 5);
        $this->createProductWithStock($category, 'EMPTY-001', 'Produk Habis', 0, 5);

        $this->actingAs($this->admin)
            ->get(route('admin.reports.stocks'))
            ->assertOk()
            ->assertSee('Produk Aman')
            ->assertSee('Produk Menipis')
            ->assertSee('Produk Habis');

        $this->actingAs($this->admin)
            ->get(route('admin.reports.stocks', ['status' => 'menipis']))
            ->assertOk()
            ->assertSee('Produk Menipis')
            ->assertDontSee('Produk Aman')
            ->assertDontSee('Produk Habis');
    }

    public function test_kasir_cannot_access_admin_reports(): void
    {
        $this->actingAs($this->kasir)->get(route('admin.reports.sales'))->assertForbidden();
        $this->actingAs($this->kasir)->get(route('admin.reports.stocks'))->assertForbidden();
    }

    public function test_custom_error_pages_render_clear_indonesian_copy(): void
    {
        $this->actingAs($this->kasir)->get(route('admin.dashboard'))->assertForbidden()->assertSee('Akses tidak diizinkan');
        $this->get('/halaman-tidak-ada')->assertNotFound()->assertSee('Halaman tidak ditemukan');
        $this->assertTrue(view()->exists('errors.500'));
    }

    private function createTransaction(
        User $user,
        string $invoice = 'INV-TEST',
        float $subtotal = 100000,
        float $discount = 0,
        float $tax = 0,
        float $total = 100000,
        string $paymentMethod = 'cash',
        string $status = 'completed',
        ?Carbon $createdAt = null,
        string $productName = 'Produk Snapshot',
        string $productCode = 'PRD-SNAP',
        ?float $costPrice = 25000,
    ): Transaction {
        $createdAt ??= now();
        $transaction = Transaction::query()->create([
            'user_id' => $user->id,
            'invoice_number' => $invoice.'-'.$this->invoiceSequence++,
            'subtotal' => $subtotal,
            'discount_value' => $discount,
            'discount_amount' => $discount,
            'tax_amount' => $tax,
            'total_amount' => $total,
            'payment_method' => $paymentMethod,
            'amount_paid' => $total,
            'change_amount' => 0,
            'status' => $status,
        ]);
        $transaction->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->save();

        TransactionItem::query()->create([
            'transaction_id' => $transaction->id,
            'product_id' => null,
            'product_name' => $productName,
            'product_code' => $productCode,
            'price' => $subtotal,
            'cost_price' => $costPrice,
            'quantity' => 1,
            'subtotal' => $subtotal,
        ]);

        return $transaction;
    }

    private function createProductWithStock(Category $category, string $code, string $name, int $quantity, int $minQuantity): Product
    {
        $product = Product::query()->create([
            'category_id' => $category->id,
            'code' => $code,
            'name' => $name,
            'price' => 1000,
            'is_active' => true,
        ]);
        $product->stock()->create(['quantity' => $quantity, 'min_quantity' => $minQuantity]);

        return $product;
    }
}
