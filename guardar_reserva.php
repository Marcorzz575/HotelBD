<?php
// guardar_reserva.php
include 'conexion.php';

// Capturamos exactamente las variables que manda tu HTML
$nombre = $_POST['nombre'] ?? '';
$correo = $_POST['correo'] ?? '';
$entrada = $_POST['entrada'] ?? '';
$salida = $_POST['salida'] ?? '';
$huespedes = $_POST['huespedes'] ?? '';
$tipo_hab = $_POST['habitacion'] ?? ''; // Tipo de habitación ("Estandar", "Premium", etc.)

// Validación de seguridad básica
if (empty($nombre) || empty($correo) || empty($entrada) || empty($salida) || empty($tipo_hab)) {
    die("error_datos_incompletos");
}

// 1. Calcular el monto total (Mantenemos intacta tu lógica de cálculo de días)
$dias = (strtotime($salida) - strtotime($entrada)) / 86400;
if ($dias <= 0) {
    die("error_fechas");
}

// Cálculo del monto total a cobrar
$precios = ["Estandar" => 1500, "Premium" => 2500, "Ejecutiva" => 4000];
$monto_total = $dias * $precios[$tipo_hab];

// =========================================================================
// 2. DELEGAR TODO EL TRABAJO A SQL SERVER
// =========================================================================
// Llamamos a nuestro nuevo procedimiento maestro pasándole los 6 datos clave
$sql_res = "{call Sp_Guardar_Reserva_Completa(?, ?, ?, ?, ?, ?)}";
$params_res = array($nombre, $correo, $tipo_hab, $entrada, $salida, $monto_total);

$stmt_res = sqlsrv_query($conn, $sql_res, $params_res);

if ($stmt_res === false) {
    // Si SQL Server se cae o hay un error de conexión
    die("error_sobreventa_o_falla");
}

// 3. Capturar la respuesta directa del Procedimiento Almacenado
$row = sqlsrv_fetch_array($stmt_res, SQLSRV_FETCH_ASSOC);

if ($row && $row['Estatus'] === 'OK') {
    // Si la base de datos logró guardar todo, mandamos 'ok' para que el HTML muestre éxito
    echo "ok";
} else {
    // Si no había cuartos o hubo choque de fechas, mandamos el error exacto ('error_sin_habitaciones')
    echo $row['Mensaje'] ?? "error_sobreventa_o_falla";
}
?>