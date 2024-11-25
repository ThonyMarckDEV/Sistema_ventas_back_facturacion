<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;
use App\Models\Pedido;
use App\Models\Pago;
use App\Models\Usuario;
use App\Models\Producto;
use App\Mail\NotificacionPagoCompletado;
use Illuminate\Support\Facades\Mail;
use FPDF;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;

class PaymentController extends Controller
{


    public function __construct()
    {
        // Agrega las credenciales de MercadoPago
        MercadoPagoConfig::setAccessToken(env('MERCADOPAGO_ACCESS_TOKEN'));
    }

    public function createPreference(Request $request)
    {
        // Validar los datos recibidos
        $request->validate([
            'idPedido' => 'required|integer',
            'detalles' => 'required|array',
            'total' => 'required|numeric',
            'correo' => 'required|email'
        ]);
        
        // Obtener los datos del request
        $idPedido = $request->input('idPedido');
        $detalles = $request->input('detalles');
        $total = $request->input('total');
        $correo = $request->input('correo');
        
        // Crear una instancia del cliente de preferencias de MercadoPago
        $client = new PreferenceClient();
    
        $currentUrlBase = 'https://loops-versus-nova-duncan.trycloudflare.com'; // DOMINIO DEL FRONT
    
        // URLs de retorno
        $backUrls = [
            "success" => "{$currentUrlBase}/PHP/CLIENTEPHP/pedidos.php?status=approved&external_reference={$idPedido}&payment_type=online",
            "failure" => "{$currentUrlBase}/PHP/CLIENTEPHP/pedidos.php?status=failure",
            "pending" => "{$currentUrlBase}/PHP/CLIENTEPHP/pedidos.php?status=pending"
        ];
    
        // Crear los ítems a partir de los detalles del pedido
        $items = [];
        foreach ($detalles as $detalle) {
            $items[] = [
                "id" => $detalle['idProducto'],
                "title" => $detalle['nombreProducto'],
                "quantity" => (int)$detalle['cantidad'],
                "unit_price" => (float)$detalle['precioUnitario'],
                "currency_id" => "PEN" // Ajusta según tu moneda
            ];
        }
    
        // Configurar la preferencia con los datos necesarios
        $preferenceData = [
            "items" => $items,
            "payer" => [
               // "email" => $correo
            ],
            "back_urls" => $backUrls,
            "auto_return" => "approved", // Automáticamente vuelve al front-end cuando el pago es aprobado
            "binary_mode" => true, // Usar modo binario para más seguridad
            "external_reference" => $idPedido
        ];
    
        try {
            // Crear la preferencia en MercadoPago
            $preference = $client->create($preferenceData);
    
            // Verificar si se creó la preferencia correctamente
            if (isset($preference->id)) {
                // Responder con el punto de inicio del pago
                return response()->json([
                    'success' => true,
                    'init_point' => $preference->init_point,
                    'preference_id' => $preference->id // Para el modal
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear la preferencia en MercadoPago'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la preferencia: ' . $e->getMessage()
            ]);
        }
    }


    public function recibirPago(Request $request)
    {
        try {
            // // Log inicial
            // Log::info('Headers recibidos:', $request->headers->all());
            // Log::info('Webhook recibido:', $request->all());
    
            // Obtener el ID del pago desde el request
            $id = $request->input('data')['id'] ?? null;
            $type = $request->input('type') ?? null;
    
           // Log::info("ID del pago recibido: {$id}, Tipo: {$type}");
    
            // Validar que el ID y el tipo estén presentes
            if (!$id || $type !== 'payment') {
                Log::warning('ID del pago o tipo no válido.');
                return response()->json(['error' => 'ID del pago o tipo no válido'], 400);
            }
    
            // URL de la API de Mercado Pago
            $url = "https://api.mercadopago.com/v1/payments/{$id}";
           // Log::info("URL para consultar el pago: {$url}");
    
            // Solicitar el pago a la API de Mercado Pago
            $client = new Client();
            $response = $client->request('GET', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . env('MERCADOPAGO_ACCESS_TOKEN'),
                ],
            ]);
            $pago = json_decode($response->getBody(), true);
    
           // Log::info('Respuesta obtenida de Mercado Pago:', $pago);
    
            // Verificar estado del pago
            $estado_pago = $pago['status'];
            $metodo_pago = $pago['payment_method_id'] ?? null;
            $externalReference = $pago['external_reference'];
    
           // Log::info("Estado del pago: {$estado_pago}, Método de pago: {$metodo_pago}, Referencia externa: {$externalReference}");
    
            // Buscar el pago asociado al pedido
            $pagoModel = Pago::where('idPedido', $externalReference)->first();
    
            if (!$pagoModel) {
              //  Log::warning("Pago no encontrado para el pedido con referencia {$externalReference}.");
                return response()->json(['success' => false, 'message' => 'Pago no encontrado para este pedido'],200);
            }
    
            if ($pagoModel->estado_pago === 'completado') {
               // Log::warning("El pago con ID {$id} ya ha sido completado previamente.");
                return response()->json(['success' => false, 'message' => 'Este pago ya ha sido completado previamente'],200);
            }
    
            // Actualizar el estado del pago a "completado"
            $pagoModel->estado_pago = 'completado';
            if ($metodo_pago) {
                $pagoModel->metodo_pago = $metodo_pago;
            }
            $pagoModel->save();
    
            // Buscar el pedido asociado
            $pedido = Pedido::with('detalles')->find($externalReference);
    
            if (!$pedido) {
               // Log::warning("Pedido con referencia {$externalReference} no encontrado.");
                return response()->json(['success' => false, 'message' => 'Pedido no encontrado'], 404);
            }
    
            // Actualizar el estado del pedido a "aprobando"
            if ($estado_pago === 'approved') {
                if (in_array($pedido->estado, ['aprobando', 'completado'])) {
                  //  Log::warning("El pedido con referencia {$externalReference} ya fue procesado previamente.");
                    return response()->json(['success' => false, 'message' => 'El pedido ya fue procesado previamente'], 200);
                }
    
                $pedido->estado = 'aprobando';
                $pedido->save();
    
             //   Log::info("Pedido {$externalReference} actualizado a 'aprobando'.");
    
                // Descontar el stock de productos
                foreach ($pedido->detalles as $detalle) {
                    $producto = Producto::find($detalle->idProducto);
                    if ($producto) {
                        $producto->stock -= $detalle->cantidad;
                        $producto->save();
                    }
                }
    
               // Generar boleta y enviar correo
                $usuario = Usuario::find($pedido->idUsuario);
                if ($usuario) {
                    $nombreCompleto = "{$usuario->nombres} {$usuario->apellidos}";

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

                    // Ruta para guardar la boleta
                    $pdfDirectory = "boletas/{$usuario->idUsuario}/{$externalReference}";
                    $pdfFileName = "boleta_pedido_{$externalReference}.pdf";
                    $pdfPath = public_path("{$pdfDirectory}/{$pdfFileName}");

                    // Crear el directorio si no existe
                    if (!file_exists(public_path($pdfDirectory))) {
                        mkdir(public_path($pdfDirectory), 0755, true);
                    }
                    
                    // Generar el PDF
                    $this->generateBoletaPDF($pdfPath, $nombreCompleto, $detallesPedido, $total);

                    // Enviar el correo con la boleta adjunta
                    Mail::to($usuario->correo)->send(new NotificacionPagoCompletado(
                        $nombreCompleto,
                        $detallesPedido,
                        $total,
                        $pdfPath
                    ));
                }
            }
    
            //Log::info("Estado de pago y pedido actualizados correctamente para el ID {$id}.");
            return response()->json(['success' => true, 'message' => 'Estado de pago y pedido actualizados correctamente'],200);
        } catch (\Exception $e) {
           // Log::error('Error al procesar el webhook: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno: ' . $e->getMessage()], 500);
        }
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
            $pdf->Cell(0, 10, "Producto: {$detalle['producto']}, Cantidad: {$detalle['cantidad']}, Subtotal: S/{$detalle['subtotal']}", 0, 1);
        }
    
        // Total
        $pdf->Ln(5);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, "Total: S/{$total}", 0, 1);
    
        // Guardar el PDF en la ruta especificada
        $pdf->Output('F', $pdfPath);
    }


    // Método para manejar el caso de fallo en el pago
    public function failure(Request $request, $idPedido)
    {
        // Aquí puedes manejar la lógica en caso de que el pago haya fallado
        // Por ejemplo, cambiar el estado del pedido a 'fallido'
        
        $pedido = Pedido::find($idPedido);
        if ($pedido) {
            $pedido->estado = 'fallido'; // Estado del pedido
            $pedido->save();
        }

        return response()->json([
            'success' => false,
            'message' => 'Pago fallido.'
        ]);
    }

    // Método para manejar pagos pendientes
    public function pending(Request $request, $idPedido)
    {
        // Aquí puedes manejar el caso de pagos pendientes
        $pedido = Pedido::find($idPedido);
        if ($pedido) {
            $pedido->estado = 'pendiente'; // Estado del pedido
            $pedido->save();
        }

        return response()->json([
            'success' => false,
            'message' => 'Pago pendiente.'
        ]);
    }
}







