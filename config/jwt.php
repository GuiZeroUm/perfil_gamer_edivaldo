<?php

return [

    'issuer' => env('JWT_ISSUER', 'https://auth.local'),

    'audience' => env('JWT_AUDIENCE', 'internal-apis'),

    'public_key_pem' => env('JWT_PUBLIC_KEY_PEM', ''),

];
