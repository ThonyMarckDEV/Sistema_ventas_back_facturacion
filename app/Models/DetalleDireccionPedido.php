<?php

// app/Models/DetalleDireccionPedido.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleDireccionPedido extends Model
{
    use HasFactory;
    protected $primaryKey = 'idDetalleDireccionPedido';

     public $timestamps = false;

    protected $table = 'detalle_direccion_pedido';
    protected $fillable = ['idPedido', 'idDireccion'];

     // Relación con Pedido
     public function pedido()
     {
         return $this->belongsTo(Pedido::class, 'idPedido', 'idPedido');
     }
 
     // Relación con DetalleDireccion
     public function direccion()
     {
         return $this->belongsTo(DetalleDireccion::class, 'idDireccion', 'idDireccion');
     }

     public function detalleDireccion()
    {
        return $this->belongsTo(DetalleDireccion::class, 'idDireccion');
    }
}