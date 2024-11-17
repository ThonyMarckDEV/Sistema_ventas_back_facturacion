<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CrearTablaDetalleDireccion extends Migration
{
    public function up()
    {
        Schema::create('detalle_direcciones', function (Blueprint $table) {
            $table->id('idDireccion');
            $table->unsignedBigInteger('idUsuario');
            $table->string('region');
            $table->string('provincia');
            $table->string('direccion');
            $table->string('estado')->default('no usando');
            $table->decimal('latitud', 10, 8);
            $table->decimal('longitud', 11, 8);

            // Definición de la clave foránea
            $table->foreign('idUsuario')
                  ->references('idUsuario')
                  ->on('usuarios')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('detalle_direcciones');
    }
}