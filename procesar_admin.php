<?php
// procesar_admin.php
// 1. Cabeceras y seguridad para evitar que errores rompan el JavaScript
error_reporting(0);
header('Content-Type: text/plain; charset=utf-8');
include 'conexion.php';

$accion = trim($_POST['accion'] ?? '');

// 2. Enrutador de operaciones de recepción
switch ($accion) {
    case 'limpieza':
        // intval() asegura que absolutamente nadie pueda meter letras o código, solo números enteros
        $hab = intval($_POST['num_hab'] ?? 0);

        if ($hab <= 0) {
            die("error"); // Detiene si la habitación es 0 o vacía
        }

        $stmt = sqlsrv_query($conn, "{call Sp_FinalizarLimpieza(?)}", array($hab));
        echo ($stmt !== false) ? "ok" : "error";
        break;

    case 'mantenimiento':
        $hab = intval($_POST['num_hab'] ?? 0);
        $dias = intval($_POST['dias'] ?? 0);

        if ($hab <= 0 || $dias <= 0) {
            die("error"); // Detiene si meten números negativos o vacíos
        }

        $stmt = sqlsrv_query($conn, "{call Sp_Mantenimiento(?, ?)}", array($hab, $dias));
        echo ($stmt !== false) ? "ok" : "error";
        break;

    default:
        // Si mandan una acción fantasma (como los viejos checkin/checkout)
        echo "error_accion_desconocida";
        break;
}
?>