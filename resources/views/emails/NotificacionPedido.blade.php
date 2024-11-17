<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Pedido</title>
</head>
<body>
    <h1>Confirmación de Pedido</h1>

    <p>Hola,</p>
    <p>Has creado exitosamente el pedido <strong>#{{ $idPedido }}</strong> en CpuraWeb.</p>

    <h3>Detalles del Pedido:</h3>
    <ul>
        @foreach ($productos as $producto)
            <li>{{ $producto->nombreProducto }} - Cantidad: {{ $producto->cantidad }} - Precio: S/ {{ $producto->precioUnitario }} - Subtotal: S/ {{ $producto->subtotal }}</li>
        @endforeach
    </ul>

    <p><strong>Total del Pedido: S/ {{ $total }}</strong></p>

    <p>Por favor, realiza el pago lo antes posible para que podamos procesar tu pedido.</p>

    <p>Saludos,<br>
    El equipo de CpuraWeb</p>
</body>
</html>
