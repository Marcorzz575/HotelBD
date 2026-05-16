<?php
// procesar_cliente.php

// 1. Apagamos errores visuales de PHP para no romper la respuesta al Frontend
error_reporting(0);
include 'conexion.php';

// Capturamos la acción (puede venir por GET o por POST)
$accion = trim($_GET['accion'] ?? $_POST['accion'] ?? '');

// 2. Sistema de Enrutamiento
switch ($accion) {

    // ========================================================
    // A) BUSCAR RESERVACIONES (Llamando al SP con Descuentos)
    // ========================================================
    case 'buscar':
        header('Content-Type: application/json; charset=utf-8');
        $correo = trim($_GET['correo'] ?? '');

        if (empty($correo)) {
            echo json_encode(["Error" => "Correo vacío"]);
            exit;
        }

        // Delegamos TODA la lógica compleja al procedimiento almacenado
        $sql = "{call Sp_Consultar_Reservas_Cliente(?)}";
        $stmt = sqlsrv_query($conn, $sql, array($correo));
        $res = [];

        if ($stmt) {
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $res[] = $row;
            }
        }
        echo json_encode($res);
        break;

    // ========================================================
    // B) CANCELAR RESERVACIÓN (Devuelve Texto)
    // ========================================================
    case 'eliminar':
        header('Content-Type: text/plain; charset=utf-8');

        // Seguridad: Forzamos a que el ID sea un número entero
        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            die("error");
        }

        $sql = "UPDATE Reservaciones SET Estado_Reserva = 'Cancelada' WHERE ID_Reservacion = ?";
        $stmt = sqlsrv_query($conn, $sql, array($id));

        echo ($stmt !== false) ? "ok" : "error";
        break;

    // ========================================================
    // C) EDITAR FECHAS DE RESERVACIÓN (Devuelve Texto)
    // ========================================================
    case 'editar':
        header('Content-Type: text/plain; charset=utf-8');

        // Validamos que la petición sea estrictamente por formulario (POST)
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            die("error_metodo");
        }

        $id = intval($_POST['id'] ?? 0);
        $entrada = $_POST['entrada'] ?? '';
        $salida = $_POST['salida'] ?? '';
        $tipo_hab = trim($_POST['tipo'] ?? '');

        if ($id <= 0 || empty($entrada) || empty($salida)) {
            die("error_datos_incompletos");
        }

        $dias = (strtotime($salida) - strtotime($entrada)) / 86400;
        if ($dias <= 0) {
            die("error_fechas");
        }

        // Seguridad: Verificamos que el tipo de habitación no haya sido alterado
        $precios = ["Estandar" => 1500, "Premium" => 2500, "Ejecutiva" => 4000];
        if (!array_key_exists($tipo_hab, $precios)) {
            die("error_tipo_habitacion");
        }

        $nuevo_monto = $dias * $precios[$tipo_hab];

        // Validación anti-cruces
        $sql_check = "
            SELECT 1 FROM Reservaciones R
            INNER JOIN Habitaciones H ON R.Numero_Habitacion = H.Numero_Habitacion
            WHERE R.Numero_Habitacion = (SELECT Numero_Habitacion FROM Reservaciones WHERE ID_Reservacion = ?)
              AND R.ID_Reservacion <> ?
              AND R.Estado_Reserva = 'Activa'
              AND (? < R.Fecha_Salida AND ? > R.Fecha_Llegada)";

        $stmt_check = sqlsrv_query($conn, $sql_check, array($id, $id, $entrada, $salida));

        if ($stmt_check && sqlsrv_has_rows($stmt_check)) {
            die("error_cruce");
        }

        // Actualización de datos
        $sql_update = "UPDATE Reservaciones SET Fecha_Llegada = ?, Fecha_Salida = ?, Monto_Total = ? WHERE ID_Reservacion = ?";
        $stmt_update = sqlsrv_query($conn, $sql_update, array($entrada, $salida, $nuevo_monto, $id));

        echo ($stmt_update !== false) ? "ok" : "error";
        break;

    // ========================================================
    // CASO POR DEFECTO (Seguridad extra)
    // ========================================================
    default:
        echo "error_accion_desconocida";
        break;
}
?>