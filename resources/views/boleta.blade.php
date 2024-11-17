<!-- resources/views/boleta.blade.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Boleta de Compra</title>
    <style>
        /* Estilos para impresión */
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 16px; /* Aumentar el tamaño de fuente base */
        }
        h1 {
            font-size: 24px; /* Aumentar el tamaño del encabezado */
        }
        p {
            font-size: 18px; /* Aumentar el tamaño de los párrafos */
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px; /* Añadir espacio superior */
        }
        th, td {
            padding: 12px; /* Aumentar el padding */
            border: 1px solid #ddd;
            text-align: left;
            font-size: 16px; /* Aumentar el tamaño de fuente en la tabla */
        }
        th {
            background-color: #f2f2f2;
        }
        .no-print {
            display: none;
        }
    </style>
</head>
<body>
    <div id="boletaContent">
        <h1>Boleta de Compra</h1>
        <p><strong>Usuario:</strong> {{ $nombreCompleto }}</p>
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio Unitario</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($detalles as $detalle)
                    <tr>
                        <td>{{ $detalle->nombreProducto }}</td>
                        <td>{{ $detalle->cantidad }}</td>
                        <td>S/{{ number_format($detalle->precioUnitario, 2) }}</td>
                        <td>S/{{ number_format($detalle->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <p><strong>Total:</strong> S/{{ number_format($total, 2) }}</p>
    </div>
    <button id="downloadPdf">Descargar Boleta en PDF</button>
    <!-- Incluir las bibliotecas -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <!-- Código JavaScript para generar el PDF -->
    <script>
        document.getElementById('downloadPdf').addEventListener('click', function () {
            const { jsPDF } = window.jspdf;

            // Crear una instancia de jsPDF con orientación vertical
            const doc = new jsPDF('p', 'mm', 'a4');

            // Seleccionar el elemento que contiene la boleta
            const elementHTML = document.getElementById('boletaContent');

            // Utilizar html2canvas para capturar el contenido como una imagen con mayor escala
            html2canvas(elementHTML, { scale: 2 }).then(function (canvas) {
                const imgData = canvas.toDataURL('image/png');
                const imgWidth = 210; // Ancho en mm para formato A4 vertical
                const pageHeight = 297; // Alto en mm para formato A4 vertical
                const imgHeight = (canvas.height * imgWidth) / canvas.width;
                let heightLeft = imgHeight;
                let position = 0;

                doc.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                heightLeft -= pageHeight;

                while (heightLeft > 0) {
                    position = heightLeft - imgHeight;
                    doc.addPage();
                    doc.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                    heightLeft -= pageHeight;
                }

                // Descargar el PDF
                doc.save('boleta.pdf');
            });
        });
    </script>
    <script>
        window.onload = function() {
            // Enviar un mensaje a la ventana principal indicando que la boleta ha cargado
            window.opener.postMessage('boletaCargada', '*');
        };
    </script>
</body>
</html>
