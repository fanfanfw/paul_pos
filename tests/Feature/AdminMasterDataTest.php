<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminMasterDataTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    }

    public function test_admin_can_access_master_data_routes(): void
    {
        $this->actingAs($this->admin)->get('/admin/categories')->assertOk();
        $this->actingAs($this->admin)->get('/admin/products')->assertOk();
        $this->actingAs($this->admin)->get('/admin/users')->assertOk();
        $this->actingAs($this->admin)->get('/admin/stocks')->assertOk();
    }

    public function test_kasir_cannot_access_admin_routes(): void
    {
        $kasir = User::factory()->create(['role' => 'kasir']);

        $this->actingAs($kasir)->get('/admin/categories')->assertForbidden();
    }

    public function test_category_delete_is_blocked_when_category_has_products(): void
    {
        $category = Category::query()->create(['name' => 'Makanan']);
        Product::query()->create([
            'category_id' => $category->id,
            'code' => 'PRD-TEST',
            'name' => 'Produk Test',
            'price' => 1000,
            'is_active' => true,
        ])->stock()->create(['quantity' => 1, 'min_quantity' => 1]);

        $this->actingAs($this->admin)
            ->delete(route('admin.categories.destroy', $category))
            ->assertRedirect();

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_product_create_edit_toggle_and_soft_delete_work(): void
    {
        $category = Category::query()->create(['name' => 'Minuman']);

        $this->actingAs($this->admin)->post(route('admin.products.store'), [
            'category_id' => $category->id,
            'code' => '',
            'name' => 'Air Test',
            'price' => 5000,
            'cost_price' => 3000,
            'is_active' => '1',
            'initial_quantity' => 7,
            'min_quantity' => 2,
        ])->assertRedirect(route('admin.products.index'));

        $product = Product::query()->where('name', 'Air Test')->firstOrFail();
        $this->assertStringStartsWith('PRD-', $product->code);
        $this->assertSame(1, Stock::query()->where('product_id', $product->id)->count());

        $this->actingAs($this->admin)->put(route('admin.products.update', $product), [
            'category_id' => $category->id,
            'code' => 'PRD-EDIT',
            'name' => 'Air Edit',
            'price' => 6000,
            'cost_price' => 3500,
            'is_active' => '1',
            'min_quantity' => 4,
        ])->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', ['id' => $product->id, 'code' => 'PRD-EDIT', 'name' => 'Air Edit']);
        $this->actingAs($this->admin)->patch(route('admin.products.toggle', $product))->assertRedirect();
        $this->assertFalse($product->fresh()->is_active);
        $this->actingAs($this->admin)->delete(route('admin.products.destroy', $product))->assertRedirect(route('admin.products.index'));
        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_product_image_upload_and_replacement_work(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin)->post(route('admin.products.store'), [
            'code' => 'IMG-001',
            'name' => 'Produk Gambar',
            'price' => 5000,
            'cost_price' => 3000,
            'is_active' => '1',
            'initial_quantity' => 3,
            'min_quantity' => 1,
            'image' => UploadedFile::fake()->image('produk.jpg'),
        ])->assertRedirect(route('admin.products.index'));

        $product = Product::query()->where('code', 'IMG-001')->firstOrFail();
        Storage::disk('public')->assertExists($product->image);

        $oldImage = $product->image;

        $this->actingAs($this->admin)->put(route('admin.products.update', $product), [
            'code' => 'IMG-001',
            'name' => 'Produk Gambar Edit',
            'price' => 6000,
            'cost_price' => 3500,
            'is_active' => '1',
            'min_quantity' => 2,
            'image' => UploadedFile::fake()->image('produk-baru.png'),
        ])->assertRedirect(route('admin.products.index'));

        $product->refresh();
        Storage::disk('public')->assertMissing($oldImage);
        Storage::disk('public')->assertExists($product->image);
    }

    public function test_user_create_edit_optional_password_and_active_admin_guard_work(): void
    {
        $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'Kasir Baru',
            'email' => 'kasirbaru@example.com',
            'password' => 'password123',
            'role' => 'kasir',
            'is_active' => '1',
        ])->assertRedirect(route('admin.users.index'));

        $user = User::query()->where('email', 'kasirbaru@example.com')->firstOrFail();
        $oldPassword = $user->password;

        $this->actingAs($this->admin)->put(route('admin.users.update', $user), [
            'name' => 'Kasir Edit',
            'email' => 'kasiredit@example.com',
            'password' => '',
            'role' => 'kasir',
            'is_active' => '1',
        ])->assertRedirect(route('admin.users.index'));

        $this->assertSame($oldPassword, $user->fresh()->password);
        $this->actingAs($this->admin)->patch(route('admin.users.toggle', $user))->assertRedirect();
        $this->assertFalse($user->fresh()->is_active);

        $this->actingAs($this->admin)->patch(route('admin.users.toggle', $this->admin))->assertRedirect();
        $this->assertTrue($this->admin->fresh()->is_active);
    }

    public function test_stock_adjustments_create_movements_and_never_go_negative(): void
    {
        $product = Product::query()->create([
            'code' => 'STK-001',
            'name' => 'Produk Stok',
            'price' => 1000,
            'is_active' => true,
        ]);
        $stock = $product->stock()->create(['quantity' => 10, 'min_quantity' => 2]);

        $this->actingAs($this->admin)->post(route('admin.stocks.update', $stock), [
            'type' => 'in',
            'quantity' => 5,
            'notes' => 'Tambah',
        ])->assertRedirect(route('admin.stocks.index'));
        $this->assertSame(15, $stock->fresh()->quantity);

        $this->actingAs($this->admin)->post(route('admin.stocks.update', $stock), [
            'type' => 'out',
            'quantity' => 3,
        ])->assertRedirect(route('admin.stocks.index'));
        $this->assertSame(12, $stock->fresh()->quantity);

        $this->actingAs($this->admin)->post(route('admin.stocks.update', $stock), [
            'type' => 'adjustment',
            'quantity' => 4,
        ])->assertRedirect(route('admin.stocks.index'));
        $this->assertSame(4, $stock->fresh()->quantity);

        $this->actingAs($this->admin)->from(route('admin.stocks.adjust', $stock))->post(route('admin.stocks.update', $stock), [
            'type' => 'out',
            'quantity' => 99,
        ])->assertSessionHasErrors('quantity');

        $this->assertSame(3, StockMovement::query()->where('product_id', $product->id)->count());
        $this->actingAs($this->admin)->get(route('admin.stocks.movements'))->assertOk()->assertSee('Produk Stok');
    }
}
