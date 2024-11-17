<?php

// app/Models/Pago.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;

    protected $table = 'pagos';
    
    protected $primaryKey = 'idPago'; // Especifica la clave primaria

    public $timestamps = false;

    protected $fillable = [
        'idPedido',
        'monto',
        'metodo_pago',
        'estado_pago',
        'fecha_pago',
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'idPedido', 'idPedido');
    }

}
