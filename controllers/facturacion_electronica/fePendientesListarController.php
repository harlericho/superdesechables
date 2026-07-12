<?php
// controllers/facturacion_electronica/fePendientesListarController.php
header('Content-Type: application/json');

require_once '../../config/db.php';

try {
  $sql = "SELECT 
            fe.factura_id, 
            f.factura_num_comprobante, 
            f.factura_fecha, 
            f.factura_total,
            CONCAT(c.cliente_nombres, ' ', c.cliente_apellidos) as cliente,
            fe.fe_estado_sri,
            fe.fe_mensaje_sri
          FROM tbl_factura_electronica fe
          INNER JOIN tbl_factura f ON fe.factura_id = f.factura_id
          INNER JOIN tbl_cliente c ON f.cliente_id = c.cliente_id
          WHERE fe.fe_estado_sri != 'AUTORIZADO' AND fe.fe_estado_sri IS NOT NULL
          ORDER BY f.factura_fecha DESC, f.factura_id DESC";
          
  $stmt = Db::dbConnection()->prepare($sql);
  $stmt->execute();
  $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
  
  echo json_encode([
    'data' => $resultados ? $resultados : []
  ]);
} catch (Exception $e) {
  echo json_encode([
    'data' => [],
    'error' => $e->getMessage()
  ]);
}
