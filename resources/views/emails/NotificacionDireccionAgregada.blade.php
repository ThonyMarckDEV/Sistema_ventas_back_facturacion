<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Dirección Agregada</title>
</head>
<body>
    <h1>Se ha agregado una nueva dirección</h1>
    <p>Has agregado la siguiente dirección:</p>
    <p>Región: {{ $direccion->region }}</p>
    <p>Provincia: {{ $direccion->provincia }}</p>
    <p>Dirección completa: {{ $direccion->direccion }}</p>
    <p>Gracias por actualizar tus datos.</p>
</body>
</html>
