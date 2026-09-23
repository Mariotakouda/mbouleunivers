<?php

use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\EventController;
use App\Http\Controllers\Public\CheckoutController;
use App\Http\Controllers\Public\PaymentController;
use App\Http\Controllers\Public\TicketController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');

Route::get('/checkout/{event}', [CheckoutController::class, 'create'])->name('checkout.create');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');


// Les routes fixes (success, failed, webhook) doivent être déclarées AVANT /payment/{order:reference},
// sinon « success » et « failed » seraient pris pour une référence de commande (erreur 404).
Route::get('/payment/success', [PaymentController::class, 'success'])->name('payment.success');
Route::get('/payment/failed', [PaymentController::class, 'failed'])->name('payment.failed');
Route::post('/payment/webhook', [PaymentController::class, 'webhook'])->name('payment.webhook'); // notification serveur FedaPay

Route::get('/payment/{order:reference}', [PaymentController::class, 'show'])->name('payment.show');
Route::post('/payment/{order:reference}/initiate', [PaymentController::class, 'initiate'])->name('payment.initiate');
Route::post('/payment/{order:reference}/manual', [PaymentController::class, 'manual'])->name('payment.manual');

Route::get('/ticket/{order:reference}', [TicketController::class, 'show'])->name('ticket.show');
Route::get('/ticket/{ticket:ticket_number}/download', [TicketController::class, 'download'])->name('ticket.download');
