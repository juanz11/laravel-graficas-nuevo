<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SaleController;
use Illuminate\Support\Facades\Route;

// Rutas protegidas (requieren inicio de sesión)
Route::middleware('auth')->group(function () {
    Route::get('/', [SaleController::class, 'index'])->name('dashboard');
    Route::post('/sales/import', [SaleController::class, 'import'])->name('sales.import');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// Rutas de invitados (no autenticados)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});



