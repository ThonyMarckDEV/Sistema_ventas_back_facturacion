<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_pedidos_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CrearTablaPedidos extends Migration
{
    public function up()
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id('idPedido')->unsigned();;
            $table->unsignedBigInteger('idUsuario');
            $table->decimal('total', 10, 2);
            $table->enum('estado', ['pendiente', 'aprobando', 'en preparacion', 'enviado', 'completado'])->default('pendiente');

            // Claves foráneas
            $table->foreign('idUsuario')->references('idUsuario')->on('usuarios')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pedidos');
    }
}
