<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Product\Controllers\ProductController;

Route::get('/', [ProductController::class, 'create'])->name('home');

Route::middleware('role:admin,formulator')->group(function () {
    Route::get('/register', [ProductController::class, 'create'])->name('products.create');
    Route::post('/register', [ProductController::class, 'store'])->name('products.store');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
});

Route::middleware('role:admin,formulator,teknisi,manajer r&d,qa')->group(function () {
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/{product:batch_code}', [ProductController::class, 'show'])->name('products.show');
});