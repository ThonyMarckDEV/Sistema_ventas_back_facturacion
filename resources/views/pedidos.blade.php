<!-- resources/views/pedidos.blade.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Pedidos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col lg:flex-row">

    <!-- Notificación -->
    <div id="notification" class="hidden fixed top-4 left-1/2 transform -translate-x-1/2 px-4 py-2 text-white font-semibold text-center rounded shadow-md z-50"></div>
    
    <!-- Sidebar -->
    @include('sidebarCLIENTE') <!-- Asegúrate de que esta vista existe -->
    
    <!-- Contenido Principal -->
    <div class="flex-1 p-4 sm:p-6 md:p-8 lg:ml-64 w-full lg:w-full mx-auto">
        
        <!-- Encabezado -->
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-6 space-y-4 lg:space-y-0">
            <div>
                <h1 class="text-2xl font-bold">Mis Pedidos</h1>
                <nav class="text-gray-500 text-sm">
                    <span>Pedidos</span> &gt; <span>Mis Pedidos</span>
                </nav>
            </div>
        </div>

        <!-- Tabla de Pedidos -->
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white rounded-lg overflow-hidden">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="py-3 px-6 text-left">#</th>
                        <th class="py-3 px-6 text-left">ID Pedido</th>
                        <th class="py-3 px-6 text-left">ID Usuario</th>
                        <th class="py-3 px-6 text-left">Total</th>
                        <th class="py-3 px-6 text-left">Estado</th>
                        <th class="py-3 px-6 text-left">Acción</th>
                    </tr>
                </thead>
                <tbody id="pedidosTableBody" class="text-gray-700">
                    <!-- Las filas de pedidos serán insertadas aquí por JavaScript -->
                </tbody>
            </table>
        </div>

    </div>

    <!-- Modal para Proceder con el Pago -->
    <div id="paymentModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden z-50">
        <div class="bg-white rounded-lg w-11/12 md:w-1/2 lg:w-2/3 p-6 relative overflow-y-auto max-h-screen">
            <button id="closeModal" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
            <h2 class="text-xl font-bold mb-4">Detalles del Pedido</h2>
            <div class="mb-4">
                <p><strong>ID Pedido:</strong> <span id="modalPedidoId"></span></p>
                <p><strong>Total:</strong> S/<span id="modalTotal"></span></p>
                <p><strong>Estado:</strong> <span id="modalEstado"></span></p>
            </div>
            
            <!-- Tabla de Detalles del Pedido -->
            <div class="mb-4 overflow-x-auto">
                <table class="min-w-full bg-white rounded-lg overflow-hidden">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="py-2 px-4 text-left">ID Detalle</th>
                            <th class="py-2 px-4 text-left">ID Producto</th>
                            <th class="py-2 px-4 text-left">Producto</th>
                            <th class="py-2 px-4 text-left">Cantidad</th>
                            <th class="py-2 px-4 text-left">Precio Unitario</th>
                            <th class="py-2 px-4 text-left">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody id="modalDetalles">
                        <!-- Los detalles del pedido serán insertados aquí por JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- Botones de Acción -->
            <div class="flex justify-end space-x-4">
                <button id="cancelPayment" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">Cancelar</button>
                <button id="confirmPayment" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">Proceder al Pago</button>
            </div>
        </div>
    </div>

    <!-- Script para cargar pedidos -->
    <script type="module" src="{{ asset('js/pedido.js') }}"></script>
</body>
</html>
