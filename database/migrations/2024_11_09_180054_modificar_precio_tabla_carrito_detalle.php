<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('carrito_detalle', function (Blueprint $table) {
            $table->decimal('precio', 8, 2)->change();
        });
    }
    
    public function down()
    {
        Schema::table('carrito_detalle', function (Blueprint $table) {
            $table->string('precio')->change();
        });
    }
    
};
