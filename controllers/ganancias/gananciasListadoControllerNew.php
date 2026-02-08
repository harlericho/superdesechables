<?php
header('Content-Type: application/json; charset=utf-8');
include_once '../../config/db.php';

$fechaDesde = isset($_POST['fecha_desde']) && !empty($_POST['fecha_desde']) ? $_POST['fecha_desde'] : null;
$fechaHasta = isset($_POST['fecha_hasta']) && !empty($_POST['fecha_hasta']) ? $_POST['fecha_hasta'] : null;
$codigoProducto = isset($_POST['codigo_producto']) && !empty($_POST['codigo_producto']) ? $_POST['codigo_producto'] : null;

try {
  $conexion = Db::dbConnection();

  // Consulta básica simplificada
  $sql = "SELECT 
                p.producto_codigo,
                p.producto_nombre,
                COALESCE(c.categoria_descripcion, 'Sin categoría') as categoria_descripcion,
                COUNT(d.detalle_id) as cantidad_vendida,
                p.producto_precio_compra,
                p.producto_precio_venta,
                SUM(d.detalle_total) as total_ventas,
                0 as total_descuentos,
                (COUNT(d.detalle_id) * p.producto_precio_compra) as costo_total,
                (SUM(d.detalle_total) - (COUNT(d.detalle_id) * p.producto_precio_compra)) as ganancia_bruta,
                (SUM(d.detalle_total) - (COUNT(d.detalle_id) * p.producto_precio_compra)) as ganancia_neta,
                CASE 
                    WHEN SUM(d.detalle_total) > 0 
                    THEN ROUND(((SUM(d.detalle_total) - (COUNT(d.detalle_id) * p.producto_precio_compra)) / SUM(d.detalle_total)) * 100, 2)
                    ELSE 0 
                END as margen_porcentaje
            FROM tbl_detalle d
            INNER JOIN tbl_producto p ON d.producto_id = p.producto_id
            LEFT JOIN tbl_categoria c ON p.categoria_id = c.categoria_id
            INNER JOIN tbl_factura f ON d.factura_id = f.factura_id
            WHERE f.factura_estado = 1 AND d.detalle_estado = 1";

  $params = [];

  // Filtros opcionales
  if ($codigoProducto) {
    $sql .= " AND p.producto_codigo = :codigo_producto";
    $params[':codigo_producto'] = $codigoProducto;
  }

  if ($fechaDesde && $fechaHasta) {
    $sql .= " AND f.factura_fecha BETWEEN :fecha_desde AND :fecha_hasta";
    $params[':fecha_desde'] = $fechaDesde;
    $params[':fecha_hasta'] = $fechaHasta;
  }

  $sql .= " GROUP BY p.producto_id
              ORDER BY ganancia_neta DESC";

  $query = $conexion->prepare($sql);

  foreach ($params as $param => $value) {
    $query->bindValue($param, $value);
  }

  $query->execute();
  $ganancias = $query->fetchAll(PDO::FETCH_ASSOC);

  // Si no hay datos, devolver array vacío en lugar de error
  if (!$ganancias) {
    $ganancias = [];
  }

  echo json_encode($ganancias);
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}
