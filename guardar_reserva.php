<?php
// guardar_reserva.php
// 1. Configuraciones de seguridad y cabeceras
error_reporting(0); // Evita que advertencias de PHP rompan la respuesta al JavaScript
header('Content-Type: text/plain; charset=utf-8');
include 'conexion.php';

// 2. Capturamos y sanitizamos las variables que manda el HTML
$nombre = trim($_POST['nombre'] ?? '');
$correo = trim($_POST['correo'] ?? '');
$entrada = $_POST['entrada'] ?? '';
$salida = $_POST['salida'] ?? '';
$huespedes = $_POST['huespedes'] ?? '';
$tipo_hab = $_POST['habitacion'] ?? '';

// 3. Validación estricta de campos vacíos
if (empty($nombre) || empty($correo) || empty($entrada) || empty($salida) || empty($tipo_hab)) {
    die("error_datos_incompletos");
}

// 4. Validación de Fechas y Cálculo de Días
$dias = (strtotime($salida) - strtotime($entrada)) / 86400;
if ($dias <= 0) {
    die("error_fechas");
}

// 5. Cálculo de tarifa dinámica (con protección de seguridad)
$precios = [
    "Estandar" => 1500,
    "Premium" => 2500,
    "Ejecutiva" => 4000
];

// Si mandan un tipo de habitación manipulado desde el navegador que no existe en el arreglo, rechazamos
if (!array_key_exists($tipo_hab, $precios)) {
    die("error_tipo_habitacion");
}

$monto_total = $dias * $precios[$tipo_hab];

// =========================================================================
// 6. DELEGAR TRANSACCIÓN AL MOTOR SQL SERVER
// =========================================================================
$sql_res = "{call Sp_Guardar_Reserva_Completa(?, ?, ?, ?, ?, ?)}";
$params_res = array($nombre, $correo, $tipo_hab, $entrada, $salida, $monto_total);

$stmt_res = sqlsrv_query($conn, $sql_res, $params_res);

// Verificamos si hubo una caída crítica del servidor o error de red
if ($stmt_res === false) {
    die("error_sobreventa_o_falla");
}

// 7. Capturar la respuesta estructurada de nuestro Procedimiento Almacenado
$row = sqlsrv_fetch_array($stmt_res, SQLSRV_FETCH_ASSOC);

if ($row && $row['Estatus'] === 'OK') {
    echo "ok";
} else {
    // En caso de que SQL rechace por mantenimiento o choque de fechas, pasamos el error exacto al Front-End
    echo $row['Mensaje'] ?? "error_sobreventa_o_falla";
}
?>