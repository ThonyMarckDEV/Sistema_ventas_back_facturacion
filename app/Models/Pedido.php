<?php

// app/Models/Pedido.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;
    protected $primaryKey = 'idPedido';

    protected $table = 'pedidos';

    public $timestamps = false;

    protected $fillable = [
        'idUsuario',
        'total',
        'estado',
    ];

     // Relación con el modelo Usuario
     public function usuario()
     {
         return $this->belongsTo(Usuario::class, 'idUsuario', 'idUsuario');
     }
 

    public function carrito()
    {
        return $this->belongsTo(Carrito::class, 'idCarrito');
    }

    // Relación con Pagos
    public function pagos()
    {
        return $this->hasMany(Pago::class, 'idPedido', 'idPedido');
    }

    public function detalles()
    {
        return $this->hasMany(PedidoDetalle::class, 'idPedido', 'idPedido');
    }

     // Relación con DetalleDireccionPedido
     public function detalleDireccionPedido()
     {
         return $this->hasOne(DetalleDireccionPedido::class, 'idPedido', 'idPedido');
     }

}
