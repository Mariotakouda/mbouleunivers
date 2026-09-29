<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Commande via WhatsApp
    |--------------------------------------------------------------------------
    | Le client choisit ses billets, la commande est enregistrée côté admin,
    | puis un message prérempli s'ouvre dans WhatsApp. Le paiement (Mobile Money,
    | espèces...) se règle directement avec l'organisateur et est confirmé à la main.
    */

    // Durée pendant laquelle les places sont gardées en attendant la confirmation.
    'hold_hours' => (int) env('ORDER_HOLD_HOURS', 24),

    // Anti-abus : nombre max de commandes en attente pour un même numéro de téléphone.
    'max_pending_per_phone' => (int) env('ORDER_MAX_PENDING_PER_PHONE', 2),

    // Anti-abus : nombre max de commandes envoyées depuis une même adresse IP, par tranche de 10 minutes.
    'max_orders_per_ip' => (int) env('ORDER_MAX_PER_IP', 5),

    // Email de l'organisateur prévenu à chaque nouvelle commande (facultatif).
    'notify_email' => env('ADMIN_NOTIFY_EMAIL'),

    // Modes d'encaissement proposés à l'admin au moment de confirmer un paiement.
    'payment_methods' => [
        'flooz' => 'Flooz (Moov Money)',
        'tmoney' => 'T-Money (Togocom)',
        'cash' => 'Espèces',
        'other' => 'Autre',
    ],
];
