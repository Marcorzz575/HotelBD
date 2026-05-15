<?php
// obtener_vistas.php
header('Content-Type: application/json');
include 'conexion.php';

$tipo_vista = $_GET['vista'] ?? '';
$resultados = [];

if ($tipo_vista == 'top_clientes') {
    $sql = "SELECT * FROM Vista_Top_Clientes";
    $stmt = sqlsrv_query($conn, $sql);
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $resultados[] = $row;
    }
    echo json_encode($resultados);

} else if ($tipo_vista == 'ingresos_tipo') {
    $sql = "SELECT * FROM Vista_Ingresos_Por_Tipo";
    $stmt = sqlsrv_query($conn, $sql);
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $resultados[] = $row;
    }
    echo json_encode($resultados);

} else if ($tipo_vista == 'excel') {
    // Llama al procedimiento que usa BCP para generar el CSV físico en C:\Reportes
    $stmt = sqlsrv_query($conn, "{call Sp_Reporte_Excel}");
    if ($stmt) {
        echo json_encode([["Mensaje" => "Reporte generado exitosamente en C:\Reportes"]]);
    } else {
        echo json_encode([["Mensaje" => "Error al generar el reporte"]]);
    }
} else {
    echo json_encode([["Error" => "Vista no encontrada"]]);
}
?>