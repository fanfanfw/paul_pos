<?php

return [
    'name' => env('STORE_NAME', 'KasirKu'),
    'address' => env('STORE_ADDRESS', 'Jl. Contoh No. 1'),
    'phone' => env('STORE_PHONE', '0812-3456-7890'),
    'receipt_footer' => env('STORE_RECEIPT_FOOTER', 'Terima kasih atas kunjungan Anda!'),
    'tax_rate' => (float) env('STORE_TAX_RATE', 0),
];
