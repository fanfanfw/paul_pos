<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Category::query()->orderBy('name')->get();
        $products = Product::query()
            ->with(['category', 'stock'])
            ->when($request->filled('search'), function ($query) use ($request): void {
                $query->searchCatalog((string) $request->string('search'));
            })
            ->when($request->filled('category_id'), function ($query) use ($request): void {
                $query->where('category_id', $request->integer('category_id'));
            })
            ->when($request->filled('status'), function ($query) use ($request): void {
                $query->where('is_active', $request->input('status') === 'active');
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create(): View
    {
        $categories = Category::query()->orderBy('name')->get();

        return view('admin.products.create', compact('categories'));
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($request, $data): void {
            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('products', 'public');
            }

            $initialQuantity = (int) $data['initial_quantity'];
            $minQuantity = (int) $data['min_quantity'];
            unset($data['initial_quantity'], $data['min_quantity']);

            $product = Product::query()->create($data);
            $product->stock()->create([
                'quantity' => $initialQuantity,
                'min_quantity' => $minQuantity,
            ]);

            if ($initialQuantity > 0) {
                StockMovement::query()->create([
                    'product_id' => $product->id,
                    'user_id' => $request->user()->id,
                    'type' => 'in',
                    'quantity' => $initialQuantity,
                    'before_quantity' => 0,
                    'after_quantity' => $initialQuantity,
                    'notes' => 'Stok awal produk.',
                    'reference' => 'PRODUCT-CREATE',
                ]);
            }
        });

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product): View
    {
        $product->load(['category', 'stock']);
        $categories = Category::query()->orderBy('name')->get();

        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($request, $product, $data): void {
            $minQuantity = (int) $data['min_quantity'];
            unset($data['min_quantity']);

            if ($request->hasFile('image')) {
                if ($product->image) {
                    Storage::disk('public')->delete($product->image);
                }

                $data['image'] = $request->file('image')->store('products', 'public');
            }

            $product->update($data);
            $product->stock()->updateOrCreate(
                ['product_id' => $product->id],
                ['min_quantity' => $minQuantity, 'quantity' => $product->stock?->quantity ?? 0]
            );
        });

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil dihapus secara soft delete.');
    }

    public function toggle(Product $product): RedirectResponse
    {
        $product->update(['is_active' => ! $product->is_active]);

        return back()->with('success', 'Status produk berhasil diperbarui.');
    }
}
