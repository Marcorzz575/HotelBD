<?php
// conexion.php
$serverName = "TU_SERVIDOR";

// Datos de conexión (Si usas Autenticación de Windows no necesitas usuario/contraseña)
$connectionInfo = array(
    "Database" => "HotelDB",
    "CharacterSet" => "UTF-8"
    // Si usas usuario "sa", descomenta la línea de abajo y pon tu contraseña:
    // ,"UID" => "sa", "PWD" => "tu_contraseña"
);

$conn = sqlsrv_connect($serverName, $connectionInfo);

if (!$conn) {
    echo "Error de conexión a la base de datos.<br>";
    die(print_r(sqlsrv_errors(), true));
}
?>