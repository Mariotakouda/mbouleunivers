<?php

use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\EventController;
use App\Http\Controllers\Public\CheckoutController;
use App\Http\Controllers\Public\OrderController;
use App\Http\Controllers\Public\TicketController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');
Route::get('/events/{event}/affiche', [EventController::class, 'poster'])->name('events.poster');

Route::get('/checkout/{event}', [CheckoutController::class, 'create'])->name('checkout.create');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');


// Suivi de commande : le client y retrouve son récapitulatif, le bouton WhatsApp et, une fois
// le paiement confirmé par l'organisateur, le lien vers ses billets.
Route::get('/commande/{order:reference}', [OrderController::class, 'show'])->name('order.show');
Route::get('/commande/{order:reference}/whatsapp', [OrderController::class, 'whatsapp'])->name('order.whatsapp');

Route::get('/ticket/{order:reference}', [TicketController::class, 'show'])->name('ticket.show');
Route::get('/ticket/{ticket:ticket_number}/download', [TicketController::class, 'download'])->name('ticket.download');
