<?php
// obtener_vistas.php
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');
include 'conexion.php';

$tipo_vista = trim($_GET['vista'] ?? '');
$resultados = [];
$sql = "";

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

    // 👇 CASO CORREGIDO PARA EL TABLERO DE OCUPACIÓN 👇
    case 'todas_activas':
        // Ordenamos usando el nombre exacto de la columna en la vista SQL ([Check-In])
        $sql = "SELECT * FROM Vista_Reservas_Activas ORDER BY [Check-In] ASC";
        break;

    case 'excel':
        $stmt = sqlsrv_query($conn, "{call Sp_Reporte_Excel}");
        if ($stmt) {
            echo json_encode([["Mensaje" => "Reporte generado exitosamente en C:\\PIA\\Reporte"]]);
        } else {
            echo json_encode([["Error" => "Error de SQL Server al intentar generar el archivo físico."]]);
        }
        exit;

    default:
        echo json_encode([["Error" => "Proceso no autorizado o vista inexistente."]]);
        exit;
}

// Ejecución centralizada
$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    // Si la consulta falla, mostramos el error limpio sin romper la web
    echo json_encode([["Error" => "No se pudo extraer la información de la base de datos."]]);
    exit;
}

while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $resultados[] = $row;
}

echo json_encode($resultados);
?>