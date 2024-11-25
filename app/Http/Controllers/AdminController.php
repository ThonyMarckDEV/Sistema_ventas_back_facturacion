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
    // FUNCION PARA REGISTRAR UN USUARIO
    public function register(Request $request)
    {
        $messages = [
            'username.required' => 'El nombre de usuario es obligatorio.',
            'username.unique' => 'El nombre de usuario ya está en uso.',
            'rol.required' => 'El rol es obligatorio.',
            'nombres.required' => 'El nombre es obligatorio.',
            'apellidos.required' => 'Los apellidos son obligatorios.',
            'apellidos.regex' => 'Debe ingresar al menos dos apellidos separados por un espacio.',
            'dni.required' => 'El DNI es obligatorio.',
            'dni.size' => 'El DNI debe tener exactamente 8 caracteres.',
            'dni.unique' => 'El DNI ya está registrado.',
            'correo.required' => 'El correo es obligatorio.',
            'correo.email' => 'El correo debe tener un formato válido.',
            'correo.unique' => 'El correo ya está registrado.',
            'edad.integer' => 'La edad debe ser un número entero.',
            'edad.between' => 'La edad debe ser mayor a 18.',
            'nacimiento.date' => 'La fecha de nacimiento debe ser una fecha válida.',
            'nacimiento.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
            'telefono.size' => 'El numero e telefono debe de ser de 9 digitos.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.regex' => 'La contraseña debe incluir al menos una mayúscula y un símbolo.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ];

        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:usuarios',
            'rol' => 'required|string|max:255',
            'nombres' => 'required|string|max:255',
            'apellidos' => [
                'required',
                'regex:/^[a-zA-ZÀ-ÿ]+(\s[a-zA-ZÀ-ÿ]+)+$/'
            ],
            'dni' => 'required|string|size:8|unique:usuarios',
            'correo' => 'required|string|email|max:255|unique:usuarios',
            'edad' => 'nullable|integer|between:18,100',
            'nacimiento' => 'nullable|date|before:today',
            'telefono' => 'nullable|string|size:9|regex:/^\d{9}$/',
            'departamento' => 'nullable|string|max:255',
           'password' => [
                'required',
                'string',
                'min:8',
                'max:255',
                'regex:/^(?=.*[A-Z])(?=.*[!@#$%^&*(),.?":{}|<>])[A-Za-z\d!@#$%^&*(),.?":{}|<>]{8,}$/',
            ]
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 400);
        }

        try {
            $user = Usuario::create([
                'username' => $request->username,
                'rol' => $request->rol,
                'nombres' => $request->nombres,
                'apellidos' => $request->apellidos,
                'dni' => $request->dni,
                'correo' => $request->correo,
                'edad' => $request->edad ?? null,
                'nacimiento' => $request->nacimiento ?? null,
                'telefono' => $request->telefono ?? null,
                'departamento' => $request->departamento ?? null,
                'password' => bcrypt($request->password),
                'status' => 'loggedOff',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Usuario registrado exitosamente',
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar el usuario',
                'error' => $e->getMessage(),
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


    public function pedidosPorMes()
    {
        $pedidos = Pedido::selectRaw('MONTH(fecha_pedido) as mes, COUNT(*) as cantidad')
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        return response()->json($pedidos);
    }

    public function ingresosPorMes()
    {
        $ingresos = Pago::where('estado_pago', 'completado')
            ->selectRaw('MONTH(fecha_pago) as mes, SUM(monto) as total_ingresos')
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        return response()->json($ingresos);
    }
    
}
