<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoDetalle extends Model
{
    protected $table = 'pedido_detalle';
    protected $primaryKey = 'idDetallePedido';
    public $timestamps = false;

    protected $fillable = [
        'idPedido',
        'idProducto',
        'cantidad',
        'precioUnitario',
        'subtotal',
    ];

    // Relación con el modelo Pedido
    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'idPedido', 'idPedido');
    }

    // Relación con el modelo Producto
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'idProducto', 'idProducto');
    }
}
