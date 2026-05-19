<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\StockController;
use App\Http\Controllers\Admin\UserController;
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
    Route::get('/dashboard', function () {
        return view('dashboard.admin');
    })->name('dashboard');

    Route::resource('categories', CategoryController::class)->except('show');
    Route::patch('products/{product}/toggle', [ProductController::class, 'toggle'])->name('products.toggle');
    Route::resource('products', ProductController::class)->except('show');
    Route::patch('users/{user}/toggle', [UserController::class, 'toggle'])->name('users.toggle');
    Route::resource('users', UserController::class)->except(['show', 'destroy']);
    Route::get('stocks', [StockController::class, 'index'])->name('stocks.index');
    Route::get('stocks/movements', [StockController::class, 'movements'])->name('stocks.movements');
    Route::get('stocks/{stock}/adjust', [StockController::class, 'adjust'])->name('stocks.adjust');
    Route::post('stocks/{stock}/adjust', [StockController::class, 'update'])->name('stocks.update');
});

Route::middleware(['auth', 'role:admin,kasir'])->prefix('kasir')->name('kasir.')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard.kasir');
    })->name('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

require __DIR__.'/auth.php';
