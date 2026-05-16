<?php
// procesar_cliente.php
include 'conexion.php';

$accion = $_GET['accion'] ?? '';

if ($accion === 'buscar') {
    $correo = $_GET['correo'] ?? '';
    $sql = "SELECT R.ID_Reservacion, R.Numero_Habitacion, T.Tipo, R.Fecha_Llegada, R.Fecha_Salida, R.Monto_Total 
            FROM Reservaciones R
            INNER JOIN Huespedes H ON R.ID_Huesped = H.ID_Huesped
            INNER JOIN Habitaciones Ha ON R.Numero_Habitacion = Ha.Numero_Habitacion
            INNER JOIN Tipos_Habitacion T ON Ha.ID_Tipo = T.ID_Tipo
            WHERE H.Correo = ? AND R.Estado_Reserva = 'Activa'";

    $stmt = sqlsrv_query($conn, $sql, array($correo));
    $res = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $res[] = $row;
    }
    echo json_encode($res);
    exit;
}

if ($accion === 'eliminar') {
    $id = $_GET['id'] ?? '';
    // Baja lógica pasando el estado a Cancelada
    $sql = "UPDATE Reservaciones SET Estado_Reserva = 'Cancelada' WHERE ID_Reservacion = ?";
    $stmt = sqlsrv_query($conn, $sql, array($id));
    echo ($stmt) ? "ok" : "error";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $accion === 'editar') {
    $id = $_POST['id'] ?? '';
    $entrada = $_POST['entrada'] ?? '';
    $salida = $_POST['salida'] ?? '';
    $tipo_hab = $_POST['tipo'] ?? '';

    $dias = (strtotime($salida) - strtotime($entrada)) / 86400;
    if ($dias <= 0) {
        die("error_fechas");
    }

    $precios = ["Estandar" => 1500, "Premium" => 2500, "Ejecutiva" => 4000];
    $precio_unidad = $precios[$tipo_hab] ?? 1500;
    $nuevo_monto = $dias * $precio_unidad;

    // Validación anti-cruces y anti-mantenimiento antes de modificar
    $sql_check = "
        SELECT 1 FROM Reservaciones R
        INNER JOIN Habitaciones H ON R.Numero_Habitacion = H.Numero_Habitacion
        WHERE R.Numero_Habitacion = (SELECT Numero_Habitacion FROM Reservaciones WHERE ID_Reservacion = ?)
          AND R.ID_Reservacion <> ?
          AND R.Estado_Reserva = 'Activa'
          AND (? < R.Fecha_Salida AND ? > R.Fecha_Llegada)";

    $stmt_check = sqlsrv_query($conn, $sql_check, array($id, $id, $entrada, $salida));

    if (sqlsrv_has_rows($stmt_check)) {
        die("error_cruce");
    }

    // Si pasó los filtros, modificamos la reservación
    $sql_update = "UPDATE Reservaciones SET Fecha_Llegada = ?, Fecha_Salida = ?, Monto_Total = ? WHERE ID_Reservacion = ?";
    $stmt_update = sqlsrv_query($conn, $sql_update, array($entrada, $salida, $nuevo_monto, $id));
    echo ($stmt_update) ? "ok" : "error";
    exit;
}
?>