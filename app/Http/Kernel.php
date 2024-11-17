<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * Las middleware que se aplican a grupos de rutas.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'api' => [
            \App\Http\Middleware\CorsMiddleware::class, // Middleware CORS personalizado
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * Las middleware de rutas individuales.
     *
     * @var array
     */
    protected $routeMiddleware = [
    
    ];
}
