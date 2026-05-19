<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\StockController;
use App\Http\Controllers\Admin\TransactionController as AdminTransactionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Kasir\DashboardController as KasirDashboardController;
use App\Http\Controllers\Kasir\TransactionController as KasirTransactionController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    return auth()->user()->isAdmin()
        ? redirect()->route('admin.dashboard')
        : redirect()->route('kasir.dashboard');
});

Route::get('/dashboard', function () {
    $role = auth()->user()->role ?? 'kasir';

    return $role === 'admin'
        ? redirect()->route('admin.dashboard')
        : redirect()->route('kasir.dashboard');
})->middleware('auth')->name('dashboard');

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');

    Route::resource('categories', CategoryController::class)->except('show');
    Route::patch('products/{product}/toggle', [ProductController::class, 'toggle'])->name('products.toggle');
    Route::resource('products', ProductController::class)->except('show');
    Route::patch('users/{user}/toggle', [UserController::class, 'toggle'])->name('users.toggle');
    Route::resource('users', UserController::class)->except(['show', 'destroy']);
    Route::get('stocks', [StockController::class, 'index'])->name('stocks.index');
    Route::get('stocks/movements', [StockController::class, 'movements'])->name('stocks.movements');
    Route::get('stocks/{stock}/adjust', [StockController::class, 'adjust'])->name('stocks.adjust');
    Route::post('stocks/{stock}/adjust', [StockController::class, 'update'])->name('stocks.update');
    Route::get('transactions', [AdminTransactionController::class, 'index'])->name('transactions.index');
    Route::get('transactions/{transaction}', [AdminTransactionController::class, 'show'])->name('transactions.show');
    Route::get('reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
    Route::get('reports/sales/export', [ReportController::class, 'exportSales'])->name('reports.sales.export');
    Route::get('reports/stocks', [ReportController::class, 'stocks'])->name('reports.stocks');
});

Route::middleware(['auth', 'role:admin,kasir'])->prefix('kasir')->name('kasir.')->group(function () {
    Route::get('/dashboard', KasirDashboardController::class)->name('dashboard');

    Route::get('/transactions/create', [KasirTransactionController::class, 'create'])->name('transactions.create');
    Route::post('/transactions', [KasirTransactionController::class, 'store'])->name('transactions.store');
    Route::get('/transactions/{transaction}/receipt', [KasirTransactionController::class, 'receipt'])->name('transactions.receipt');
    Route::get('/transactions/{transaction}/receipt/pdf', [KasirTransactionController::class, 'receiptPdf'])->name('transactions.receipt.pdf');
    Route::get('/api/products/search', [KasirTransactionController::class, 'searchProducts'])->name('api.products.search');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

require __DIR__.'/auth.php';
