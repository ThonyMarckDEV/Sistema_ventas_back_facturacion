<?php

use App\Http\Controllers\AdminController;

use App\Http\Controllers\ClienteController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

//RUTAS

//================================================================================================
        //RUTAS  AUTH

        //RUTA PARA QUE LOS USUAIOS SE LOGEEN POR EL CONTROLLADOR AUTHCONTROLLER
        Route::post('login', [AuthController::class, 'login']);

        Route::post('logout', [AuthController::class, 'logout']);

        Route::post('refresh-token', [AuthController::class, 'refreshToken']);

        Route::post('update-activity', [AuthController::class, 'updateLastActivity']);

        Route::get('listarUsuarios', [AdminController::class, 'listarUsuarios']);

        Route::get('/listarCategorias', [AdminController::class, 'listarCategorias']);

        Route::get('/listarProductos', [AdminController::class, 'listarProductos']);

        Route::post('/registerUser', [ClienteController::class, 'registerUser']);

        Route::post('/check-status', [AuthController::class, 'checkStatus']);

        Route::post('/send-message', [AuthController::class, 'sendContactEmail']);

        Route::post('/send-verification-codeUser', [AuthController::class, 'sendVerificationCodeUser']);
        Route::post('/verify-codeUser', [AuthController::class, 'verifyCodeUser']);
        Route::post('/change-passwordUser', [AuthController::class, 'changePasswordUser']);

        Route::post('/webhook/mercadopago', [PaymentController::class, 'recibirPago']);
//================================================================================================


//================================================================================================
    //RUTAS PROTEGIDAS A
    // RUTAS PARA ADMINISTRADOR VALIDADA POR MIDDLEWARE AUTH (PARA TOKEN JWT) Y CHECKROLE (PARA VALIDAR ROL DEL TOKEN)
    Route::middleware(['auth.jwt', 'checkRoleMW:admin'])->group(function () { 
        Route::post('register', [AdminController::class, 'register']);

        Route::put('/actualizarUsuario/{id}', [AdminController::class, 'actualizarUsuario']);
        Route::delete('/eliminarUsuario/{id}', [AdminController::class, 'eliminarUsuario']);

        Route::post('/agregarProducto', [AdminController::class, 'agregarProducto']);
        Route::post('/actualizarProducto/{id}', [AdminController::class, 'actualizarProducto']);
        Route::delete('/eliminarProducto/{id}', [AdminController::class, 'eliminarProducto']);

        Route::post('/agregarCategoria', [AdminController::class, 'agregarCategoria']);
        Route::put('/actualizarCategoria/{id}', [AdminController::class, 'actualizarCategoria']);
        Route::delete('/eliminarCategoria/{id}', [AdminController::class, 'eliminarCategoria']);
   
        Route::get('/admin/pedidos', [AdminController::class, 'getAllOrders']);

        Route::put('/admin/pedidos/{idPedido}', [AdminController::class, 'updateOrderStatus']);
        Route::delete('/admin/pedidos/{idPedido}', [AdminController::class, 'deleteOrder']);
        Route::get('/pagos/comprobante/{userId}/{pagoId}/{filename}', [AdminController::class, 'verComprobante']);

        Route::get('/obtenerDireccionPedido/{idPedido}', [AdminController::class, 'obtenerDireccionPedido']);


        Route::get('/reportes/total-ingresos', [AdminController::class, 'totalIngresos']);
        Route::get('/reportes/total-pedidos-completados', [AdminController::class, 'totalPedidosCompletados']);
        Route::get('/reportes/total-clientes', [AdminController::class, 'totalClientes']);
        Route::get('/reportes/total-productos', [AdminController::class, 'totalProductos']);
        Route::get('/reportes/productos-bajo-stock', [AdminController::class, 'productosBajoStock']);
        Route::get('/reportes/pagos-completados', [AdminController::class, 'obtenerPagosCompletados']);
        Route::get('/reportes/pedidos-por-mes', [AdminController::class, 'pedidosPorMes']);
        Route::get('/reportes/ingresos-por-mes', [AdminController::class, 'ingresosPorMes']);


        Route::post('/admin/pedidos/cantidad', [AdminController::class, 'obtenerCantidadPedidosAdmin']);
    });





    // RUTAS PARA CLIENTE VALIDADA POR MIDDLEWARE AUTH (PARA TOKEN JWT) Y CHECKROLE (PARA VALIDAR ROL DEL TOKEN)
    Route::middleware(['auth.jwt', 'checkRoleMW:cliente'])->group(function () {
        Route::get('perfilCliente', [ClienteController::class, 'perfilCliente']);
        Route::post('uploadProfileImageCliente/{idUsuario}', [ClienteController::class, 'uploadProfileImageCliente']);
        Route::put('updateCliente/{idUsuario}', [ClienteController::class, 'updateCliente']);
        Route::get('productos', [ClienteController::class, 'listarProductos']);

        // Ruta para agregar un producto al carrito
        Route::post('agregarCarrito', [ClienteController::class, 'agregarAlCarrito']);
        Route::get('carrito', [ClienteController::class, 'listarCarrito']); // Listar productos en el carrito
        Route::put('carrito_detalle/{idProducto}', [ClienteController::class, 'actualizarCantidad']); // Actualizar cantidad de producto
        Route::delete('carrito_detalle/{idProducto}', [ClienteController::class, 'eliminarProducto']); // Eliminar producto del carrito
        
        Route::post('/pedido', [ClienteController::class, 'crearPedido']);
        // Ruta para listar pedidos de un usuario
        Route::get('/pedidos/{idUsuario}', [ClienteController::class, 'listarPedidos']);
        // Ruta para procesar el pago de un pedido
        Route::post('/procesar-pago/{idPedido}', [ClienteController::class, 'procesarPago']);
    
        Route::get('/carrito/cantidad', [ClienteController::class, 'obtenerCantidadCarrito']);
        Route::post('/pedidos/cantidad', [ClienteController::class, 'obtenerCantidadPedidos']);


        Route::get('/listarDireccion/{idUsuario}', [ClienteController::class, 'listarDireccion']);
        Route::post('/agregarDireccion', [ClienteController::class, 'agregarDireccion']);
        Route::put('/actualizarDireccion/{id}', [ClienteController::class, 'actualizarDireccion']);
        Route::delete('/eliminarDireccion/{id}', [ClienteController::class, 'eliminarDireccion']);
        Route::put('/setDireccionUsando/{idDireccion}', [ClienteController::class, 'setDireccionUsando']);

        Route::get('/obtenerDireccionPedidoUser/{idPedido}', [AdminController::class, 'obtenerDireccionPedido']);

        Route::post('/enviarCodigo/{idUsuario}', [ClienteController::class, 'enviarCodigo']);
        Route::post('/verificarCodigo/{idUsuario}', [ClienteController::class, 'verificarCodigo']);
        Route::post('/cambiarContrasena', [ClienteController::class, 'cambiarContrasena']);

        Route::delete('/cancelarPedido', [ClienteController::class, 'cancelarPedido']);

        Route::post('/payment/preference', [PaymentController::class, 'createPreference']);

       // Route::put('/cliente/pagos/{idPago}', [AdminController::class, 'updatePaymentStatus']);
    });

//================================================================================================

