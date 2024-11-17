<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role  // Este es el tercer parámetro que es el rol que pasas desde la ruta
     * @return mixed
     */
    public function handle($request, Closure $next, $role)
    {
        try {
            // Obtener el token JWT y decodificarlo
            $token = JWTAuth::parseToken();
            $payload = $token->getPayload();

            // Obtener el rol del token
            $userRole = $payload->get('rol');  // Asegúrate de que 'rol' existe en el payload de tu token JWT

            // Verificar si el rol del token coincide con el rol requerido
            if ($userRole !== $role) {
                return response()->json(['error' => 'Acceso denegado: No tienes el rol adecuado.'], 403);
            }

        } catch (Exception $e) {
            return response()->json(['error' => 'Token inválido o no proporcionado.'], 401);
        }

        // Si el rol es correcto, continuar con la solicitud
        return $next($request);
    }
}
