<?php
// obtener_vistas.php
// 1. Configuraciones de seguridad para evitar que errores nativos rompan el JSON
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');
include 'conexion.php';

$tipo_vista = trim($_GET['vista'] ?? '');
$resultados = [];
$sql = "";

// 2. Sistema de Enrutamiento (Switch)
switch ($tipo_vista) {
    case 'top_clientes':
        $sql = "SELECT * FROM Vista_Top_Clientes";
        break;

    case 'ingresos_tipo':
        $sql = "SELECT * FROM Vista_Ingresos_Por_Tipo";
        break;

    case 'huespedes_activos':
        $sql = "SELECT * FROM Vista_Huespedes_Activos";
        break;

    case 'excel':
        // El reporte de Excel tiene un trato especial porque no devuelve una tabla, sino que ejecuta BCP
        $stmt = sqlsrv_query($conn, "{call Sp_Reporte_Excel}");
        if ($stmt) {
            echo json_encode([["Mensaje" => "Reporte generado exitosamente en C:\\PIA\\Reporte"]]);
        } else {
            echo json_encode([["Error" => "Error de SQL Server al intentar generar el archivo físico."]]);
        }
        exit; // Terminamos la ejecución para que no intente hacer el bloque de abajo

    default:
        // Si alguien intenta mandar un parámetro manipulado por la URL, lo bloqueamos
        echo json_encode([["Error" => "Proceso no autorizado o vista inexistente."]]);
        exit;
}

// =========================================================================
// 3. EJECUCIÓN CENTRALIZADA PARA LAS VISTAS
// =========================================================================
$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    // Si la vista falla (ej. borraste la vista en SQL por error), devolvemos un JSON de error
    echo json_encode([["Error" => "No se pudo extraer la información de la base de datos."]]);
    exit;
}

// Llenado dinámico del arreglo
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $resultados[] = $row;
}

// Devolvemos la tabla completa en JSON
echo json_encode($resultados);
?>