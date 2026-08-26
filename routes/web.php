<?php

use App\Http\Controllers\CashierController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\OrderTrackingController;
use App\Http\Controllers\OutletController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes (Guest)
|--------------------------------------------------------------------------
*/

Route::inertia('/', 'Welcome')->name('home');

// Tracking Order Page (Guest Access)
Route::get('/tracking', [OrderTrackingController::class, 'index'])->name('tracking.index');
Route::get('/tracking/{invoice}', [OrderTrackingController::class, 'show'])->name('tracking.show');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard & Common Pages
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::get('transactions', [TransactionController::class, 'index'])->name('transactions.index');


    Route::prefix('expenses')->group(function () {
        // Customers (Akses untuk semua User Terautentikasi)
        Route::get('/', [ExpenseController::class, 'index'])->name('expenses.index');
        Route::post('/', [ExpenseController::class, 'store'])->name('expenses.store');
        Route::get('/print/pdf', [ExpenseController::class, 'printPdf'])->name('expenses.print');

        Route::put('/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
        Route::delete('/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');
    });



    // Customers (Akses untuk semua User Terautentikasi)
    Route::prefix('customers')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->name('customers.index');
        Route::post('/', [CustomerController::class, 'store'])->name('customers.store');
        Route::put('/{customer}', [CustomerController::class, 'update'])->name('customers.update');
        Route::delete('/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Owner-Only Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['isOwner'])->group(function () {

        // Users Management
        Route::get('users', [UserController::class, 'index'])->name('users.index');

        Route::prefix('users/owner')->group(function () {
            Route::post('/', [UserController::class, 'storeOwner'])->name('owner.store');
            Route::patch('/{user}', [UserController::class, 'updateOwner'])->name('owner.update');
            Route::delete('/{user}', [UserController::class, 'destroyOwner'])->name('owner.destroy');
        });

        Route::prefix('users/admin')->group(function () {
            Route::post('/', [UserController::class, 'storeAdmin'])->name('admin.store');
            Route::patch('/{user}', [UserController::class, 'updateAdmin'])->name('admin.update');
            Route::delete('/{user}', [UserController::class, 'destroyAdmin'])->name('admin.destroy');
        });

        // Outlets Management
        Route::prefix('outlets')->group(function () {
            Route::get('/', [OutletController::class, 'index'])->name('outlets.index');
            Route::post('/', [OutletController::class, 'store'])->name('outlets.store');
            Route::put('/{outlet}', [OutletController::class, 'update'])->name('outlets.update');
            Route::delete('/{outlet}', [OutletController::class, 'destroy'])->name('outlets.destroy');
        });

        // Services Management
        Route::prefix('services')->group(function () {
            Route::get('/', [ServiceController::class, 'index'])->name('services.index');
            Route::post('/', [ServiceController::class, 'store'])->name('services.store');
            Route::put('/{service}', [ServiceController::class, 'update'])->name('services.update');
            Route::delete('/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Admin-Only Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth', 'isAdmin'])->group(function () {
        Route::prefix('cashier')->group(function () {
            Route::get('/', [CashierController::class, 'index'])->name('cashier.index');
            Route::post('/', [CashierController::class, 'store'])->name('cashier.store');

            Route::post('/', [CashierController::class, 'store'])->name('cashier.store');
        });
    });

    Route::middleware(['auth'])->group(function () {
        // 1. Route untuk mengedit info dasar sepatu (Brand, Warna, Size, Kondisi)
        Route::patch('/transactions/{transaction}/shoe-detail', [TransactionController::class, 'updateShoeDetail'])
            ->name('transactions.update-shoe-detail');

        // 2. Route untuk memperbarui status stepper pengerjaan sepatu
        Route::patch('/transactions/{transaction}/shoe-status', [TransactionController::class, 'updateStatus'])
            ->name('transactions.update-shoe-status');

        Route::get('/transactions/{transaction}/print-pdf', [TransactionController::class, 'printPdf'])
            ->name('transactions.print-pdf');
    });
});
require __DIR__ . '/settings.php';
