<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClienteController; // Importar el controlador

Route::get('/', function () {
    return view('welcome');
    
});


Route::get('/boleta/{idPedido}', [ClienteController::class, 'showBoleta']);
