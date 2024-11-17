<?php

// database/migrations/xxxx_xx_xx_create_detalle_direccion_pedido_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CrearTablaDetalleDireccionPedido extends Migration
{
    public function up()
    {
        Schema::create('detalle_direccion_pedido', function (Blueprint $table) {
            $table->id('idDetalleDireccionPedido');
            $table->unsignedBigInteger('idPedido');
            $table->unsignedBigInteger('idDireccion');

             // Define las claves foráneas correctamente
            $table->foreign('idPedido')->references('idPedido')->on('pedidos')->onDelete('cascade');
            $table->foreign('idDireccion')->references('idDireccion')->on('detalle_direcciones')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('detalle_direccion_pedido');
    }
}