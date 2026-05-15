<?php
// conexion.php
$serverName = "localhost";


$connectionInfo = array(
    "Database" => "HotelDB",
    "CharacterSet" => "UTF-8"

);

$conn = sqlsrv_connect($serverName, $connectionInfo);

if (!$conn) {
    echo "Error de conexión a la base de datos.<br>";
    die(print_r(sqlsrv_errors(), true));
}
?>