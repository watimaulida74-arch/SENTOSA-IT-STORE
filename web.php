<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReportController;

/*
|--------------------------------------------------------------------------
| Web Routes - Integrated Laravel Project (UTS Web Programming 2)
| Inventory & Sales Management System
|--------------------------------------------------------------------------
| Hak Akses:
| - 'admin' : Kelola user/role, master data penuh, batalkan transaksi, laporan
| - 'staf'  : Transaksi pembelian (stock-in) & penjualan (stock-out), master data
| - 'user'  : Katalog produk & riwayat pesanan/pembelian sendiri
*/

// Redirect root to dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ==========================================
// PROTECTED ROUTES (HARUS LOGIN)
// ==========================================
Route::middleware(['auth'])->group(function () {

    // 1. Dashboard (Bisa diakses Admin, Staf, User)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 2. Modul Produk
    // - Index & Show: Bisa diakses Admin, Staf, dan User
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

    // - Create, Store, Edit, Update: Diizinkan untuk Admin dan Staf
    Route::middleware(['role:admin,staf'])->group(function () {
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    });

    // - Destroy: Khusus Admin
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])
        ->middleware('role:admin')
        ->name('products.destroy');

    // 3. Modul Kategori (Master Data)
    Route::get('/categories', [CategoryController::class, 'index'])
        ->middleware('role:admin,staf')
        ->name('categories.index');

    Route::middleware(['role:admin'])->group(function () {
        Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    });

    // 4. Modul Supplier (Pemasok)
    Route::get('/suppliers', [SupplierController::class, 'index'])
        ->middleware('role:admin,staf')
        ->name('suppliers.index');

    Route::middleware(['role:admin'])->group(function () {
        Route::get('/suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create');
        Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
        Route::get('/suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
        Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
        Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');
    });

    // 5. Modul Transaksi Pembelian (Purchase / Stock-in)
    // Diizinkan untuk Admin dan Staf
    Route::middleware(['role:admin,staf'])->group(function () {
        Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index');
        Route::get('/purchases/create', [PurchaseController::class, 'create'])->name('purchases.create');
        Route::post('/purchases', [PurchaseController::class, 'store'])->name('purchases.store');
        Route::get('/purchases/{purchase}', [PurchaseController::class, 'show'])->name('purchases.show');
    });

    // 6. Modul Transaksi Penjualan (Sales / Stock-out)
    // Admin, Staf, & User bisa membuat & melihat riwayat
    Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
    Route::get('/sales/create', [SaleController::class, 'create'])->name('sales.create');
    Route::post('/sales', [SaleController::class, 'store'])->name('sales.store');
    Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('sales.show');

    // Pembatalan Transaksi Penjualan (Khusus Admin) dengan auto restore stock
    Route::post('/sales/{sale}/cancel', [SaleController::class, 'cancel'])
        ->middleware('role:admin')
        ->name('sales.cancel');

    // 7. Modul Manajemen User & Role (KHUSUS ADMIN)
    Route::middleware(['role:admin'])->group(function () {
        Route::resource('users', UserController::class);
    });

    // 8. Modul Laporan (Admin & Staf)
    Route::get('/reports', [ReportController::class, 'index'])
        ->middleware('role:admin,staf')
        ->name('reports.index');
});
