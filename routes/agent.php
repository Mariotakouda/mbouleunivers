<?php

use App\Http\Controllers\Agent\ScannerController;
use App\Http\Controllers\Agent\ScanController;
use App\Http\Controllers\Auth\AgentLoginController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AgentLoginController::class, 'create'])->name('login');
Route::post('/login', [AgentLoginController::class, 'store'])->name('login.store');
Route::post('/logout', [AgentLoginController::class, 'destroy'])->name('logout');

Route::middleware(['auth', 'agent'])->group(function () {
    Route::get('/scanner', [ScannerController::class, 'index'])->name('scanner.index');
    Route::post('/scanner/verify', [ScannerController::class, 'verify'])->name('scanner.verify');

    Route::get('/scans', [ScanController::class, 'index'])->name('scans.index');
});
