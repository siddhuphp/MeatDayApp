<?php

return [
    /*
    |--------------------------------------------------------------------------
    | PayU Money Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for PayU Money payment gateway.
    |
    */

    'mode' => env('PAYU_MODE', 'test'), // test or live
    
    'key' => env('PAYU_KEY', ''),
    'salt' => env('PAYU_SALT', ''),
    
    'test' => [
        'key' => env('PAYU_TEST_KEY', 'gtKFFx'),
        'salt' => env('PAYU_TEST_SALT', 'eCwWELxi'),
        'action' => 'https://test.payu.in/_payment',
        'verify_url' => 'https://test.payu.in/merchant/postservice.php?form=2',
    ],
    
    'live' => [
        'key' => env('PAYU_LIVE_KEY', ''),
        'salt' => env('PAYU_LIVE_SALT', ''),
        'action' => 'https://secure.payu.in/_payment',
        'verify_url' => 'https://info.payu.in/merchant/postservice.php?form=2',
    ],
    
    'success_url' => env('PAYU_SUCCESS_URL', 'https://demo.meatday.shop/payment-success'),
    'failure_url' => env('PAYU_FAILURE_URL', 'https://demo.meatday.shop/payment-failure'),
];
