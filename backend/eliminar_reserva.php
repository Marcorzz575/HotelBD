<?php
// eliminar_reserva.php
include 'conexion.php';

if (!isset($_POST["id"])) {
    die("error");
}

$id = intval($_POST["id"]);

// El UPDATE dispara el Trigger TR_CancelacionHabitacion_Update automáticamente en SQL Server
$sql = "UPDATE Reservaciones SET Estado_Reserva = 'Cancelada' WHERE ID_Reservacion = ?";
$stmt = sqlsrv_query($conn, $sql, array($id));

if ($stmt) {
    echo "ok";
} else {
    echo "error";
}
?>