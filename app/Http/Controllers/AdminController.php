<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Carrito;
use App\Models\Pedido;
use App\Models\Pago;
use App\Models\DetalleDireccionPedido;
use Illuminate\Http\Request;
use App\Mail\NotificacionPagoCompletado;
use App\Mail\NotificacionPedidoEliminado;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use FPDF;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;


class AdminController extends Controller
{
        public function register(Request $request)
    {
        // Validación de los datos
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:usuarios',
            'rol' => 'required|string|max:255',
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'dni' => 'required|string|size:8|unique:usuarios',
            'correo' => 'required|string|email|max:255|unique:usuarios',
            'edad' => 'nullable|integer|between:0,150',
            'nacimiento' => 'nullable|date|before:today',
            'sexo' => 'nullable|string|in:masculino,femenino,otro',
            'direccion' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:9',
            'departamento' => 'nullable|string|max:255',
            'password' => 'required|string|min:6|confirmed',
            'perfil' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $messages = [
                'username' => 'El nombre de usuario ya está en uso o es inválido.',
                'dni' => 'El DNI ya está registrado o es inválido.',
                'correo' => 'El correo electrónico ya está en uso o es inválido.',
                'edad' => 'La edad ingresada no es válida.',
                'nacimiento' => 'La fecha de nacimiento debe ser anterior a hoy.',
                'sexo' => 'El valor de sexo es inválido. Debe ser masculino, femenino u otro.',
                'telefono' => 'El teléfono ingresado es inválido.',
            ];

            $message = 'Error en la validación de los datos: ';
            foreach ($messages as $field => $errorMessage) {
                if ($errors->has($field)) {
                    $message .= $errorMessage . ' ';
                }
            }

            return response()->json([
                'success' => false,
                'message' => trim($message),
                'errors' => $errors
            ], 400);
        }

        try {
            // Creación del usuario con status "loggedOff"
            $user = Usuario::create([
                'username' => $request->username,
                'rol' => $request->rol,
                'nombres' => $request->nombres,
                'apellidos' => $request->apellidos,
                'dni' => $request->dni,
                'correo' => $request->correo,
                'edad' => $request->edad,
                'nacimiento' => $request->nacimiento,
                'sexo' => $request->sexo,
                'direccion' => $request->direccion,
                'telefono' => $request->telefono,
                'departamento' => $request->departamento,
                'password' => bcrypt($request->password),
                'status' => 'loggedOff',
                'perfil' => $request->perfil,
            ]);

            // Crear el carrito solo si el rol es "cliente"
            if ($request->rol === 'cliente') {
                Carrito::create([
                    'idUsuario' => $user->idUsuario
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Usuario registrado exitosamente'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar el usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    
    // Listar usuarios
    public function listarUsuarios()
    {
        $usuarios = Usuario::select('idUsuario', 'username', 'rol', 'correo')
                    ->where('rol', '!=', 'admin') // Excluir usuarios con rol "admin"
                    ->get();
        return response()->json(['success' => true, 'data' => $usuarios]);
    }

    // Eliminar usuario
    public function eliminarUsuario($id)
    {
        $usuario = Usuario::find($id);
        if ($usuario) {
            $usuario->delete();
            return response()->json(['success' => true, 'message' => 'Usuario eliminado correctamente']);
        }
        return response()->json(['success' => false, 'message' => 'Usuario no encontrado'], 404);
    }

    // Actualizar usuario
    public function actualizarUsuario(Request $request, $id)
    {
        $usuario = Usuario::find($id);
        if ($usuario) {
            $usuario->update($request->only('username', 'rol', 'correo'));
            return response()->json(['success' => true, 'message' => 'Usuario actualizado correctamente']);
        }
        return response()->json(['success' => false, 'message' => 'Usuario no encontrado'], 404);
    }


     // Obtener todas las categorías
     public function listarCategorias()
     {
         $categorias = Categoria::all();
         return response()->json(['success' => true, 'data' => $categorias], 200);
     }


// Listar todos los productos con el nombre de la categoría y URL completa de la imagen
public function listarProductos()
{
    $productos = Producto::with('categoria:idCategoria,nombreCategoria')->get();

    // Mapeo para agregar el nombre de la categoría y la URL completa de la imagen
    $productos = $productos->map(function ($producto) {
        return [
            'idProducto' => $producto->idProducto,
            'nombreProducto' => $producto->nombreProducto,
            'descripcion' => $producto->descripcion,
            'precio' => $producto->precio,
            'stock' => $producto->stock,
            'imagen' => $producto->imagen ? url("storage/{$producto->imagen}") : null, // URL completa de la imagen
            'idCategoria' => $producto->idCategoria,
            'nombreCategoria' => $producto->categoria ? $producto->categoria->nombreCategoria : null,
        ];
    });

    return response()->json(['success' => true, 'data' => $productos], 200);
}

    // Crear un nuevo producto
    public function agregarProducto(Request $request)
    {
        // Validar los datos de entrada, incluyendo el tipo de archivo de imagen
        $request->validate([
            'nombreProducto' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric',
            'stock' => 'required|integer',
            'imagen' => 'nullable|mimes:jpeg,jpg,png,gif|max:2048', // Solo formatos de imagen permitidos
            'idCategoria' => 'required|exists:categorias,idCategoria',
        ]);

        // Crear un nuevo producto sin la imagen
        $productoData = $request->except('imagen');

        // Guardar la imagen si se proporciona
        if ($request->hasFile('imagen')) {
            $path = $request->file('imagen')->store('imagenes', 'public');
            $productoData['imagen'] = $path;
        }

        // Crear el producto con los datos obtenidos
        $producto = Producto::create($productoData);

        return response()->json([
            'success' => true, 
            'message' => 'Producto creado exitosamente', 
            'data' => $producto
        ], 201);
    }

        // Actualizar un producto
        public function actualizarProducto(Request $request, $id)
        {
            // Validación de los datos entrantes, incluyendo los tipos de archivo de imagen
            $request->validate([
                'nombreProducto' => 'required|string|max:255',
                'descripcion' => 'nullable|string',
                'precio' => 'required|numeric',
                'stock' => 'required|integer',
                'imagen' => 'nullable|mimes:jpeg,jpg,png,gif|max:2048', // Solo formatos de imagen permitidos
                'idCategoria' => 'required|exists:categorias,idCategoria',
            ]);

            // Buscar el producto por ID
            $producto = Producto::findOrFail($id);

            // Procesar la nueva imagen si se proporciona
            if ($request->hasFile('imagen')) {
                // Eliminar la imagen anterior si existe
                if ($producto->imagen && Storage::disk('public')->exists($producto->imagen)) {
                    Storage::disk('public')->delete($producto->imagen);
                }

                // Guardar la nueva imagen y actualizar la ruta en el producto
                $path = $request->file('imagen')->store('imagenes', 'public');
                $producto->imagen = $path;
            }

            // Actualizar otros campos del producto
            $producto->nombreProducto = $request->nombreProducto;
            $producto->descripcion = $request->descripcion;
            $producto->precio = $request->precio;
            $producto->stock = $request->stock;
            $producto->idCategoria = $request->idCategoria;
            
            // Guardar los cambios
            $producto->save();

            return response()->json([
                'success' => true, 
                'message' => 'Producto actualizado exitosamente', 
                'data' => $producto
            ], 200);
        }


    // Eliminar un producto
    public function eliminarProducto($id)
    {
        $producto = Producto::findOrFail($id);
        $producto->delete();
        return response()->json(['success' => true, 'message' => 'Producto eliminado exitosamente'], 200);
    }


    public function agregarCategoria(Request $request)
    {
        $request->validate([
            'nombreCategoria' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:500',
        ]);

        $categoria = Categoria::create([
            'nombreCategoria' => $request->nombreCategoria,
            'descripcion' => $request->descripcion,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Categoría agregada exitosamente',
            'data' => $categoria
        ]);
    }

      // Método para actualizar una categoría
      public function actualizarCategoria(Request $request, $id)
      {
          $request->validate([
              'nombreCategoria' => 'required|string|max:255',
              'descripcion' => 'nullable|string|max:500',
          ]);
  
          $categoria = Categoria::find($id);
          if (!$categoria) {
              return response()->json([
                  'success' => false,
                  'message' => 'Categoría no encontrada'
              ], 404);
          }
  
          $categoria->update([
              'nombreCategoria' => $request->nombreCategoria,
              'descripcion' => $request->descripcion,
          ]);
  
          return response()->json([
              'success' => true,
              'message' => 'Categoría actualizada exitosamente',
              'data' => $categoria
          ]);
      }
  
      // Método para eliminar una categoría
      public function eliminarCategoria($id)
      {
          $categoria = Categoria::find($id);
          if (!$categoria) {
              return response()->json([
                  'success' => false,
                  'message' => 'Categoría no encontrada'
              ], 404);
          }
  
          $categoria->delete();
  
          return response()->json([
              'success' => true,
              'message' => 'Categoría eliminada exitosamente'
          ]);
      }
    

      public function getAllOrders()
    {
        $orders = Pedido::with(['usuario', 'pagos', 'detalles.producto'])
            ->where('estado', '<>', 'completado')
            ->get();

        return response()->json(['success' => true, 'orders' => $orders]);
    }

    
    public function updatePaymentStatus(Request $request, $idPedido)
    {
        // Validar los datos recibidos
        $request->validate([
            'estado_pago' => 'required|string',
            'metodo_pago' => 'nullable|string', // El método de pago puede ser opcional
        ]);

        $estado_pago = $request->input('estado_pago');
        $metodo_pago = $request->input('metodo_pago');

        // Buscar el pago asociado al pedido
        $pago = Pago::where('idPedido', $idPedido)->first();

        if (!$pago) {
            return response()->json(['success' => false, 'message' => 'Pago no encontrado para este pedido'], 404);
        }

        // Verificar si el estado del pago ya está marcado como completado
        if ($pago->estado_pago === 'completado') {
            return response()->json(['success' => false, 'message' => 'Este pago ya ha sido completado previamente'], 400);
        }

        // Actualizar el estado del pago y el método de pago
        $pago->estado_pago = $estado_pago;
        if ($metodo_pago) {
            $pago->metodo_pago = $metodo_pago; // Actualiza el método de pago si se proporciona
        }
        $pago->save();

        // Buscar el pedido asociado
        $pedido = Pedido::with('detalles')->find($idPedido);

        if (!$pedido) {
            return response()->json(['success' => false, 'message' => 'Pedido no encontrado'], 404);
        }

        // Solo actualizar el estado del pedido si el pago es "completado"
        if ($estado_pago === 'completado') {
            // Verificar si el estado del pedido ya está en "aprobando" o "completado"
            if ($pedido->estado === 'aprobando' || $pedido->estado === 'completado') {
                return response()->json(['success' => false, 'message' => 'El pedido ya ha sido procesado previamente'], 400);
            }

            $pedido->estado = 'aprobando'; // Cambiar el estado a "aprobando"
            $pedido->save();

            // Lógica adicional: descontar el stock de productos
            foreach ($pedido->detalles as $detalle) {
                $producto = Producto::find($detalle->idProducto);
                if ($producto) {
                    $producto->stock -= $detalle->cantidad;
                    $producto->save();
                }
            }

            // Obtener el usuario asociado al pedido
            $usuario = Usuario::find($pedido->idUsuario);
            if ($usuario) {
                $nombreCompleto = $usuario->nombres . ' ' . $usuario->apellidos;

                // Preparar detalles para la boleta
                $detallesPedido = [];
                $total = 0;

                foreach ($pedido->detalles as $detalle) {
                    $producto = Producto::find($detalle->idProducto);
                    $detallesPedido[] = [
                        'producto' => $producto ? $producto->nombreProducto : 'Producto no encontrado',
                        'cantidad' => $detalle->cantidad,
                        'subtotal' => $detalle->subtotal,
                    ];
                    $total += $detalle->subtotal;
                }

                // Generar la boleta en PDF
                $pdfPath = "boletas/{$idPedido}/{$usuario->idUsuario}.pdf";
                $this->generateBoletaPDF($pdfPath, $nombreCompleto, $detallesPedido, $total);

                // Enviar el correo con la boleta adjunta
                Mail::to($usuario->correo)->send(new NotificacionPagoCompletado(
                    $nombreCompleto,
                    $detallesPedido,
                    $total,
                    public_path($pdfPath)
                ));
            }
        }

        // Enviar una respuesta de éxito
        return response()->json(['success' => true, 'message' => 'Estado de pago y pedido actualizados correctamente']);
    }
    
    private function generateBoletaPDF($pdfPath, $nombreCompleto, $detallesPedido, $total)
    {
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        
        // Título
        $pdf->Cell(0, 10, "Boleta de Pago", 0, 1, 'C');
        $pdf->Ln(10);
    
        // Información del cliente
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, "Cliente: {$nombreCompleto}", 0, 1);
        $pdf->Ln(5);
    
        // Detalles del pedido
        $pdf->Cell(0, 10, "Detalles del Pedido:", 0, 1);
        $pdf->SetFont('Arial', '', 10);
        foreach ($detallesPedido as $detalle) {
            $pdf->Cell(0, 10, "Producto: {$detalle['producto']}, Cantidad: {$detalle['cantidad']}, Subtotal: S/. {$detalle['subtotal']}", 0, 1);
        }
    
        $pdf->Ln(10);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, "Total: S/. {$total}", 0, 1);
    
        // Guardar el PDF en public/boletas
        $publicPath = public_path($pdfPath);
        $directory = dirname($publicPath);
    
        // Crear el directorio si no existe
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }
    
        // Guardar el archivo PDF
        $pdf->Output($publicPath, 'F');
    }
    


    public function updateOrderStatus(Request $request, $idPedido)
    {
        $estado = $request->input('estado');

        $pedido = Pedido::find($idPedido);

        if (!$pedido) {
            return response()->json(['success' => false, 'message' => 'Pedido no encontrado'], 404);
        }

        $pedido->estado = $estado;
        $pedido->save();

        return response()->json(['success' => true, 'message' => 'Estado del pedido actualizado']);
    }

    public function verComprobante($userId, $pagoId, $filename)
    {
        $path = storage_path("pagos/comprobante/{$userId}/{$pagoId}/{$filename}");

        if (!File::exists($path)) {
            abort(404);
        }

        $file = File::get($path);
        $type = File::mimeType($path);

        return Response::make($file, 200)->header("Content-Type", $type);
    }


        public function deleteOrder($idPedido)
        {
            $pedido = Pedido::find($idPedido);

            if (!$pedido) {
                return response()->json(['success' => false, 'message' => 'Pedido no encontrado'], 404);
            }

            // Obtener el usuario asociado al pedido
            $usuario = Usuario::find($pedido->idUsuario);
            if ($usuario) {
                $nombreCompleto = $usuario->nombres . ' ' . $usuario->apellidos;
            }

            // Eliminar los detalles del pedido
            $pedido->detalles()->delete();

            // Eliminar los pagos asociados
            $pedido->pagos()->delete();

            // Eliminar el pedido
            $pedido->delete();

            // Enviar el correo de notificación al usuario
            if ($usuario) {
                Mail::to($usuario->correo)->send(new NotificacionPedidoEliminado(
                    $nombreCompleto,
                    $idPedido
                ));
            }

            return response()->json(['success' => true, 'message' => 'Pedido eliminado correctamente']);
        }


    public function obtenerDireccionPedido($idPedido)
    {
        $direccion = DetalleDireccionPedido::where('idPedido', $idPedido)
            ->with('detalleDireccion') // Cargar datos de relación detalle_direccion
            ->first();

        if ($direccion) {
            return response()->json([
                'success' => true,
                'direccion' => [
                    'region' => $direccion->detalleDireccion->region,
                    'provincia' => $direccion->detalleDireccion->provincia,
                    'direccion' => $direccion->detalleDireccion->direccion,
                    'latitud' => $direccion->detalleDireccion->latitud,
                    'longitud' => $direccion->detalleDireccion->longitud,
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Dirección no encontrada para el pedido'
        ]);
    }


    //FUNCIONES PARA REPORTES

    public function totalIngresos()
    {
        $totalVentas = DB::table('pagos')
            ->where('estado_pago', 'completado')
            ->sum('monto');

        return response()->json(['totalVentas' => $totalVentas], 200);
    }


    public function totalPedidosCompletados()
    {
        $totalPedidos = DB::table('pedidos')
            ->where('estado', 'completado')
            ->count();

        return response()->json(['totalPedidos' => $totalPedidos], 200);
    }


    public function totalClientes()
    {
        $totalClientes = DB::table('usuarios')
            ->where('rol', 'cliente')
            ->count();

        return response()->json(['totalClientes' => $totalClientes], 200);
    }

    public function totalProductos()
    {
        $totalProductos = DB::table('productos')->count();

        return response()->json(['totalProductos' => $totalProductos], 200);
    }

    public function productosBajoStock()
    {
        $productos = DB::table('productos')
            ->where('stock', '<', 10)
            ->select('nombreProducto', 'stock')
            ->get();

        return response()->json(['productosBajoStock' => $productos], 200);
    }

    public function obtenerPagosCompletados()
    {
        $cantidadPagosCompletados = Pago::where('estado_pago', 'completado')->count();
        
        return response()->json([
            'cantidadPagosCompletados' => $cantidadPagosCompletados,
        ]);
    }


    public function obtenerCantidadPedidosAdmin(Request $request)
    {
        // Filtra los pedidos que no estén en estado 'completado'
        $cantidadPedidos = DB::table('pedidos')
            ->whereIn('estado', ['pendiente', 'aprobando', 'en preparacion', 'enviado'])
            ->count();

        return response()->json(['success' => true, 'cantidad' => $cantidadPedidos]);
    }



    
}
