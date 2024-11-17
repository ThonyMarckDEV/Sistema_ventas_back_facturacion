<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_categorias_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CrearTablaCategorias extends Migration
{
    public function up()
    {
        Schema::create('categorias', function (Blueprint $table) {
            $table->bigIncrements('idCategoria'); // Clave primaria con bigIncrements (unsignedBigInteger)
            $table->string('nombreCategoria');
            $table->text('descripcion')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('categorias');
    }
}
