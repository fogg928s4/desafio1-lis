<?php
session_start(); // Inicia la sesión para manejar las variables de sesión

// Verifica si es la primera vez que se accede y establece los valores predeterminados
if (!isset($_SESSION['saldo'])) {
    $_SESSION['saldo'] = 100000; // Saldo inicial del usuario
    $_SESSION['criptos'] = [
        'BTC' => 0,
        'ETH' => 0,
        'XRP' => 0
    ];
}

if (!isset($_SESSION['precios'])) {
    $_SESSION['precios'] = [
        'BTC' => rand(20000, 50000),
        'ETH' => rand(1000, 3000),
        'XRP' => rand(5, 20)/10
    ];
}

if (!isset($_SESSION['historico'])) {
    $_SESSION['historico'] = [
        'BTC' => [],
        'ETH' => [],
        'XRP' => []
    ];
}

// Función para actualizar los precios aleatorios de las criptomonedas
function actualizarPrecios() {
    foreach ($_SESSION['precios'] as $cripto => $precio) {
        if($cripto == 'BTC')
            $nuevoPrecio = rand(20000, 50000); // Generar precio aleatorio
        elseif ($cripto == 'ETH') {
            $nuevoPrecio = rand(1000, 3000);
        } elseif ($cripto == 'XRP') {
            $nuevoPrecio = rand(5, 20)/10;
        }
        $_SESSION['precios'][$cripto] = $nuevoPrecio;
        // Guardar el nuevo precio en el historial
        $_SESSION['historico'][$cripto][] = $precio;
        // Limitar el historial a los últimos 10 precios para no sobrecargar la sesión
        if (count($_SESSION['historico'][$cripto]) > 10) {
            array_shift($_SESSION['historico'][$cripto]); // Eliminar el precio más antiguo
        }
        echo "--------------------------------------------------- Historico -------------------------------------------------------";
        var_dump($_SESSION['historico']);
        echo "--------------------------------------------------- Precios -------------------------------------------------------";
        var_dump($_SESSION['precios']);
        // Actualizar el precio actual
       
       
    }
}

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
