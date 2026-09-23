<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(): View
    {
        $payments = Payment::with('order')->latest()->paginate(20);

        return view('admin.payments.index', compact('payments'));
    }

    public function show(Payment $payment): View
    {
        return view('admin.payments.show', [
            'payment' => $payment->load('order'),
        ]);
    }
}
