<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CrearTablaDetalleCarrito extends Migration
{
    public function up()
    {
        Schema::create('carrito_detalle', function (Blueprint $table) {
            $table->id('idDetalle');
            $table->unsignedBigInteger('idCarrito'); // Mismo tipo que en la tabla `carrito`
            $table->unsignedBigInteger('idProducto'); // Mismo tipo que en la tabla `productos`
            $table->integer('cantidad');
            $table->decimal('precio', 10, 2); // Define el tipo de campo para el precio con precisión decimal
            
            // Claves foráneas
            $table->foreign('idCarrito')
                ->references('idCarrito')
                ->on('carrito')
                ->onDelete('cascade'); // Activar cascada en eliminación

            $table->foreign('idProducto')
                ->references('idProducto')
                ->on('productos')
                ->onDelete('cascade'); // Activar cascada en eliminación
        });
    }

    public function down()
    {
        Schema::dropIfExists('carrito_detalle');
    }
}
