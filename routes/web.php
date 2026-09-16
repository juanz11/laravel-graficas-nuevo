<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SaleController;
use Illuminate\Support\Facades\Route;

// Rutas protegidas (requieren inicio de sesión)
Route::middleware('auth')->group(function () {
    Route::get('/', [SaleController::class, 'index'])->name('dashboard');
    Route::post('/sales/import', [SaleController::class, 'import'])->name('sales.import');
    Route::get('/compare', [SaleController::class, 'showCompare'])->name('compare');
    Route::post('/compare', [SaleController::class, 'compare'])->name('compare.run');
    Route::get('/manual-entry', [SaleController::class, 'showManualEntry'])->name('manual-entry');
    Route::post('/manual-entry', [SaleController::class, 'storeManualEntry'])->name('manual-entry.store');
    Route::get('/manual-entry/{date}/edit', [SaleController::class, 'editMonth'])->name('manual-entry.edit');
    Route::post('/manual-entry/{date}/update', [SaleController::class, 'updateMonth'])->name('manual-entry.update');
    Route::post('/sales/update-rate', [SaleController::class, 'updateRate'])->name('sales.update-rate');
    
    // Rutas para gestionar ventas
    Route::get('/sales', [SaleController::class, 'list'])->name('sales.list');
    Route::get('/sales/{id}/edit', [SaleController::class, 'editJson'])->name('sales.edit-json');
    Route::post('/sales/{id}/update', [SaleController::class, 'update'])->name('sales.update');
    Route::delete('/sales/{id}', [SaleController::class, 'destroy'])->name('sales.destroy');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// Rutas de invitados (no autenticados)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});



