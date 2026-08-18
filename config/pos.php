<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Store / Receipt Identity
    |--------------------------------------------------------------------------
    */

    'store' => [
        'name' => env('POS_STORE_NAME', env('APP_NAME', 'My Store')),
        'address' => env('POS_STORE_ADDRESS', ''),
        'phone' => env('POS_STORE_PHONE', ''),
        'footer' => env('POS_STORE_FOOTER', 'Terima kasih atas kunjungan Anda'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Receipt Printer (thermal ESC/POS over network, printed via QZ Tray)
    |--------------------------------------------------------------------------
    */

    'printer' => [
        'host' => env('POS_PRINTER_HOST', '192.168.1.10'),
        'port' => (int) env('POS_PRINTER_PORT', 9100),
    ],

];
