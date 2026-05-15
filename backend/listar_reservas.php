<?php
// listar_reservas.php
header("Content-Type: application/json");
include 'conexion.php';

$correo = $_GET["correo"] ?? '';

// Hacemos INNER JOIN para traer los nombres bonitos de la habitación y el estado
$sql = "
    SELECT 
        R.ID_Reservacion AS id, 
        H.Nombre_Completo AS nombre, 
        H.Correo AS correo, 
        CONVERT(VARCHAR, R.Fecha_Llegada, 23) AS fecha_entrada, 
        CONVERT(VARCHAR, R.Fecha_Salida, 23) AS fecha_salida, 
        T.Nombre_Tipo AS habitacion,
        R.Monto_Total AS monto
    FROM Reservaciones R
    INNER JOIN Huespedes H ON R.ID_Huesped = H.ID_Huesped
    INNER JOIN Habitaciones Hab ON R.Numero_Habitacion = Hab.Numero_Habitacion
    INNER JOIN Tipos_Habitacion T ON Hab.ID_Tipo = T.ID_Tipo
    WHERE H.Correo = ? AND R.Estado_Reserva != 'Cancelada'";

$stmt = sqlsrv_query($conn, $sql, array($correo));
$reservas = [];

if ($stmt) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        // Agregamos un dato ficticio de 'huespedes' al JSON para que tu JS no falle
        $row['huespedes'] = 'N/A (Ver Detalle)';
        $reservas[] = $row;
    }
}

echo json_encode($reservas);
?>