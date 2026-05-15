<?php
// guardar_reserva.php
include 'conexion.php';

$nombre = $_POST['nombre'] ?? '';
$correo = $_POST['correo'] ?? '';
$entrada = $_POST['entrada'] ?? '';
$salida = $_POST['salida'] ?? '';
$huespedes = $_POST['huespedes'] ?? '';
$tipo_hab = $_POST['habitacion'] ?? '';

if (empty($nombre) || empty($correo) || empty($entrada) || empty($salida) || empty($tipo_hab)) {
    die("error_datos_incompletos");
}

// 1. Calcular el monto total
$dias = (strtotime($salida) - strtotime($entrada)) / 86400;
if ($dias <= 0) {
    die("error_fechas");
}

$precios = ["Estandar" => 1500, "Premium" => 2500, "Ejecutiva" => 4000];
$monto_total = $dias * $precios[$tipo_hab];

// 2. Gestionar el huésped (Buscar si existe, si no, insertarlo)
$sql_huesped = "
    IF NOT EXISTS (SELECT 1 FROM Huespedes WHERE Correo = ?)
    BEGIN
        INSERT INTO Huespedes (Nombre_Completo, Correo) VALUES (?, ?)
    END
    SELECT ID_Huesped FROM Huespedes WHERE Correo = ?";
$params_h = array($correo, $nombre, $correo, $correo);
$stmt_h = sqlsrv_query($conn, $sql_huesped, $params_h);
sqlsrv_fetch($stmt_h);
$id_huesped = sqlsrv_get_field($stmt_h, 0);

// 3. Buscar una habitación disponible del tipo seleccionado
$sql_hab = "
    SELECT TOP 1 H.Numero_Habitacion 
    FROM Habitaciones H 
    INNER JOIN Tipos_Habitacion T ON H.ID_Tipo = T.ID_Tipo 
    WHERE T.Nombre_Tipo = ? AND H.Estado = 'Disponible'";
$stmt_hab = sqlsrv_query($conn, $sql_hab, array($tipo_hab));
$row_hab = sqlsrv_fetch_array($stmt_hab, SQLSRV_FETCH_ASSOC);

if (!$row_hab) {
    die("error_sin_habitaciones");
}
$num_hab = $row_hab['Numero_Habitacion'];

// 4. Ejecutar el Procedimiento Almacenado Principal
$sql_res = "{call Sp_Reservas_Segura(?, ?, ?, ?, ?)}";
$params_res = array($id_huesped, $num_hab, $entrada, $salida, $monto_total);
$stmt_res = sqlsrv_query($conn, $sql_res, $params_res);

if ($stmt_res) {
    echo "ok";
} else {
    echo "error_sobreventa_o_falla";
}
?>