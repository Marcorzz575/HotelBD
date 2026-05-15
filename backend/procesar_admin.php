<?php
// procesar_admin.php
include 'conexion.php';

$accion = $_POST['accion'] ?? '';

switch ($accion) {
    case 'checkin':
        $id = $_POST['id_reserva'];
        $stmt = sqlsrv_query($conn, "{call Sp_CheckIn(?)}", array($id));
        echo $stmt ? "ok" : "error";
        break;

    case 'checkout':
        $id = $_POST['id_reserva'];
        $stmt = sqlsrv_query($conn, "{call Sp_Checkout_Validado(?)}", array($id));
        echo $stmt ? "ok" : "error";
        break;

    case 'limpieza':
        $hab = $_POST['num_hab'];
        $stmt = sqlsrv_query($conn, "{call Sp_FinalizarLimpieza(?)}", array($hab));
        echo $stmt ? "ok" : "error";
        break;

    case 'mantenimiento':
        $hab = $_POST['num_hab'];
        $dias = $_POST['dias'];
        $stmt = sqlsrv_query($conn, "{call Sp_Mantenimiento(?, ?)}", array($hab, $dias));
        echo $stmt ? "ok" : "error";
        break;

    default:
        echo "error_accion_desconocida";
        break;
}
?>