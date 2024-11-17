<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo mensaje de contacto</title>
</head>
<body>
    <h2>Nuevo mensaje de contacto</h2>
    <p><strong>Nombre:</strong> {{ $name }}</p>
    <p><strong>Correo Electrónico:</strong> {{ $email }}</p>
    <p><strong>Mensaje:</strong></p>
    <p>{{ $messageContent }}</p>
</body>
</html>
