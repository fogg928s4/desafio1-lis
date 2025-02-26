<?php
include('config.php');


$url = 'https://pro-api.coinmarketcap.com/v1/cryptocurrency/listings/latest';
$parameters = [
  'start' => '1',
  'limit' => '5000',
  'convert' => 'USD'
];

$headers = [
  'Accepts: application/json',
  'X-CMC_PRO_API_KEY: 2f1d9a29-457c-4394-8531-9f66a2161cc3'
];
$qs = http_build_query($parameters); // query string encode the parameters
$request = "{$url}?{$qs}"; // create the request URL


$curl = curl_init(); // Get cURL resource
// Set cURL options
curl_setopt_array($curl, array(
  CURLOPT_URL => $request,            // set the request URL
  CURLOPT_HTTPHEADER => $headers,     // set the headers 
  CURLOPT_RETURNTRANSFER => 1         // ask for raw response instead of bool
));

$response = curl_exec($curl); // Send the request, save the response
print_r(json_decode($response)); // print json decoded response
var_dump($response);
curl_close($curl); // Close request

echo "Funciona";

// Si se recarga la página, actualiza los precios aleatoriamente
if (isset($_GET['actualizar'])) {
    actualizarPrecios();
}

// Manejo de compra
if (isset($_POST['comprar'])) {
    $cripto = $_POST['cripto'];
    $cantidad = $_POST['cantidad'];
    
    if ($_SESSION['saldo'] >= $_SESSION['precios'][$cripto] * $cantidad) {
        $_SESSION['saldo'] -= $_SESSION['precios'][$cripto] * $cantidad;
        $_SESSION['criptos'][$cripto] += $cantidad;
    }
}

// Manejo de venta
if (isset($_POST['vender'])) {
    $cripto = $_POST['cripto'];
    $cantidad = $_POST['cantidad'];
    
    if ($_SESSION['criptos'][$cripto] >= $cantidad) {
        $_SESSION['saldo'] += $_SESSION['precios'][$cripto] * $cantidad;
        $_SESSION['criptos'][$cripto] -= $cantidad;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulador de Criptomonedas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Agregar Chart.js -->
</head>
<body>
    <div class="container mt-5">
        <h1>Simulador de Criptomonedas</h1>
        
        <!-- Mostrar saldo -->
        <div class="alert alert-info">
            <strong>Saldo disponible: </strong> $<?php echo number_format($_SESSION['saldo'], 2); ?>
        </div>

        <h2>Criptomonedas</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Criptomoneda</th>
                    <th>Precio</th>
                    <th>Cantidad en tu cuenta</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($_SESSION['precios'] as $cripto => $precio): ?>
                    <tr>
                        <td><?php echo $cripto; ?></td>
                        <td>$<?php echo number_format($precio, 2); ?></td>
                        <td><?php echo $_SESSION['criptos'][$cripto]; ?></td>
                        <td>
                            <!-- Formulario de compra -->
                            <form action="" method="POST" class="d-inline">
                                <input type="number" name="cantidad" placeholder="Cantidad" min="0" step="0.0000001" required>
                                <input type="hidden" name="cripto" value="<?php echo $cripto; ?>">
                                <button type="submit" name="comprar" class="btn btn-success">Comprar</button>
                            </form>
                            <!-- Formulario de venta -->
                            <form action="" method="POST" class="d-inline">
                                <input type="number" name="cantidad" placeholder="Cantidad" min="0" step="0.0000001" required>
                                <input type="hidden" name="cripto" value="<?php echo $cripto; ?>">
                                <button type="submit" name="vender" class="btn btn-danger">Vender</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Botón para actualizar los precios -->
        <a href="?actualizar=true" class="btn btn-primary">Actualizar Precios</a>

        <h2 class="mt-5">Gráfico de Evolución del Precio de Criptomonedas</h2>
        
        <!-- Selección de criptomoneda -->
        <form method="GET">
            <select name="cripto_elegida" class="form-select" onchange="this.form.submit()">
                <option value="">Selecciona una criptomoneda</option>
                <option value="BTC" <?php echo isset($_GET['cripto_elegida']) && $_GET['cripto_elegida'] == 'BTC' ? 'selected' : ''; ?>>BTC</option>
                <option value="ETH" <?php echo isset($_GET['cripto_elegida']) && $_GET['cripto_elegida'] == 'ETH' ? 'selected' : ''; ?>>ETH</option>
                <option value="XRP" <?php echo isset($_GET['cripto_elegida']) && $_GET['cripto_elegida'] == 'XRP' ? 'selected' : ''; ?>>XRP</option>
            </select>
        </form>

        <?php if (isset($_GET['cripto_elegida'])): ?>
            <canvas id="cryptoChart" width="400" height="200"></canvas>
            <script>
                // Obtener el historial de precios de la criptomoneda elegida
                var historial = <?php echo json_encode($_SESSION['historico'][isset($_GET['cripto_elegida']) ? $_GET['cripto_elegida'] : 'BTC']); ?>;
                var criptos = <?php echo json_encode(array_keys($_SESSION['precios'])); ?>;

                // Crear el gráfico con Chart.js
                var ctx = document.getElementById('cryptoChart').getContext('2d');
                var chart = new Chart(ctx, {
                    type: 'line', // Tipo de gráfico
                    data: {
                        labels: Array.from({length: historial.length}, (_, i) => `Día ${i+1}`), // Eje X: días
                        datasets: [{
                            label: 'Precio de <?php echo isset($_GET['cripto_elegida']) ? $_GET['cripto_elegida'] : 'BTC'; ?> (USD)',
                            data: historial, // Datos del historial de precios
                            borderColor: 'rgba(75, 192, 192, 1)',
                            fill: false,
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: false
                            }
                        }
                    }
                });
            </script>
        <?php endif; ?>
    </div>
</body>
</html>
