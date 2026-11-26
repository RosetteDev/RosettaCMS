<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default payment gateway
    |--------------------------------------------------------------------------
    |
    | The foundation ships the manual gateway (orders are recorded with a
    | pending payment status). A paid module implements the PaymentGateway
    | contract (app/Services/Payments/PaymentGateway.php) and sets this to
    | its class name to take over payment handling without touching
    | checkout or order code.
    |
    */
    'default' => env('PAYMENTS_DEFAULT', \App\Services\Payments\ManualPaymentGateway::class),
];
