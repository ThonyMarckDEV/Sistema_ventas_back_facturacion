<?php

// app/Models/CarritoDetalle.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarritoDetalle extends Model
{
    use HasFactory;

    protected $table = 'carrito_detalle';
    
    protected $primaryKey = 'idDetalle'; // Define la clave primaria correcta

    public $timestamps = false;

    // Permitir asignación masiva para estos campos
    protected $fillable = ['idCarrito', 'idProducto', 'cantidad', 'precio'];
    
    public function carrito()
    {
        return $this->belongsTo(Carrito::class, 'idCarrito');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'idProducto', 'idProducto');
    }

    
}
