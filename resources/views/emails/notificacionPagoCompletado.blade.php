<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Notificación de Pago Completado</title>
</head>
<body>
    <h1>Su pago ha sido completado</h1>
    <p>Estimado {{ $nombreCompleto }},</p>
    <p>Su pago ha sido procesado y revisado exitosamente por el Administrador. Aquí están los detalles de su pedido:</p>
    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($detallesPedido as $detalle)
            <tr>
                <td>{{ $detalle['producto'] }}</td>
                <td>{{ $detalle['cantidad'] }}</td>
                <td>{{ number_format($detalle['subtotal'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <p><strong>Total:</strong> {{ number_format($total, 2) }}</p>
    <p>Puede verificar el estado de su pedido en la sección "Mis Pedidos".</p>
    <p>Gracias por su compra.</p>
</body>
</html>
