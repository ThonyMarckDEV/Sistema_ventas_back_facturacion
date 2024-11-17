<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carrito extends Model
{
    use HasFactory;
    protected $primaryKey = 'idCarrito'; // Define la clave primaria correcta

    protected $table = 'carrito';

    // Permite asignación masiva de idUsuario
    protected $fillable = ['idUsuario'];

    public $timestamps = false;

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'idUsuario');
    }

    public function detalles()
    {
        return $this->hasMany(CarritoDetalle::class, 'idCarrito', 'idCarrito');
    }
}
