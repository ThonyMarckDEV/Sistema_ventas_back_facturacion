<?php


return [

 

    'secret' => env('JWT_SECRET'),

 
    'keys' => [

   

        'public' => env('JWT_PUBLIC_KEY'),

     

        'private' => env('JWT_PRIVATE_KEY'),


        'passphrase' => env('JWT_PASSPHRASE'),

    ],

    
    'ttl' => env('JWT_TTL', 5),

    'refresh_ttl' => env('JWT_REFRESH_TTL', 20160),

    'algo' => env('JWT_ALGO', Tymon\JWTAuth\Providers\JWT\Provider::ALGO_HS256),


    'required_claims' => [
        'iss',
        'iat',
        'exp',
        'nbf',
        'sub',
        'jti',
    ],

  

    'persistent_claims' => [
        // 'foo',
        // 'bar',
    ],

  

    'lock_subject' => true,

  

    'leeway' => env('JWT_LEEWAY', 0),

  
    'blacklist_enabled' => env('JWT_BLACKLIST_ENABLED', false),


    'blacklist_grace_period' => env('JWT_BLACKLIST_GRACE_PERIOD', 60), // 60 segundos de periodo de gracia


    'decrypt_cookies' => false,

  
    'providers' => [

   

        'jwt' => Tymon\JWTAuth\Providers\JWT\Lcobucci::class,


        'auth' => Tymon\JWTAuth\Providers\Auth\Illuminate::class,


        'storage' => Tymon\JWTAuth\Providers\Storage\Illuminate::class,

    ],

    

];
