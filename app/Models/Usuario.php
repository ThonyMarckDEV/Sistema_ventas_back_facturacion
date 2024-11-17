<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Usuario extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $table = 'usuarios';
    protected $primaryKey = 'idUsuario'; 
    public $timestamps = false;

    protected $fillable = [
        'username', 'rol', 'nombres', 'apellidos', 'dni', 'correo', 'edad', 'nacimiento', 'sexo', 'direccion', 'telefono', 'departamento', 'password', 'status', 'perfil',
    ];

    protected $hidden = ['password'];

    // JWT: Identificador del token
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        // Asegúrate de actualizar el estado antes de emitir el token
        $this->update(['status' => 'loggedOn']);
        
        $carrito = $this->carrito()->first();

        return [
            'idUsuario' => $this->idUsuario,
            'dni' => $this->dni,
            'nombres' => $this->nombres,
            'username' => $this->username, // Agregar username al JWT
            'correo' => $this->correo,
            'estado' => $this->status, 
            'rol' => $this->rol,
            'idCarrito' => $carrito ? $carrito->idCarrito : null, // Agrega idCarrito al JWT
        ];
    }
    // Relación con ActividadUsuario
    public function activity()
    {
        return $this->hasOne(ActividadUsuario::class, 'idUsuario'); // Cambiado a ActividadUsuario
    }

    public function carrito()
    {
        return $this->hasOne(Carrito::class, 'idUsuario', 'idUsuario');
    }

     // Relaciones
     public function pedidos()
     {
         return $this->hasMany(Pedido::class, 'idUsuario', 'idUsuario');
     }
}
