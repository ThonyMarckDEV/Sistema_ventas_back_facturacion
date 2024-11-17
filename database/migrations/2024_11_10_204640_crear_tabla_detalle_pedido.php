<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CrearTablaDetallePedido extends Migration
{
    public function up()
    {
        Schema::create('pedido_detalle', function (Blueprint $table) {
            $table->id('idDetallePedido');
            $table->unsignedBigInteger('idPedido');
            $table->unsignedBigInteger('idProducto');
            $table->integer('cantidad');
            $table->decimal('precioUnitario', 8, 2);
            $table->decimal('subtotal', 8, 2);

            // Claves foráneas
            $table->foreign('idPedido')->references('idPedido')->on('pedidos')->onDelete('cascade');
            $table->foreign('idProducto')->references('idProducto')->on('productos');

        });
    }

    public function down()
    {
        Schema::dropIfExists('pedido_detalle');
    }
}
