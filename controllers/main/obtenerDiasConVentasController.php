<?php
include_once '../../models/facturaModel.php';
session_start();

// Obtener el mes y año solicitado (por defecto el actual)
$mes = isset($_GET['mes']) ? intval($_GET['mes']) : date('m');
$anio = isset($_GET['anio']) ? intval($_GET['anio']) : date('Y');

// Consultar días con ventas en el mes especificado
try {
  $sql = "SELECT DISTINCT DATE(factura_fecha) as fecha, COUNT(*) as ventas, SUM(factura_total) as total
          FROM tbl_factura
          WHERE MONTH(factura_fecha) = :mes 
            AND YEAR(factura_fecha) = :anio
            AND factura_estado = '1'
          GROUP BY DATE(factura_fecha)
          ORDER BY fecha ASC";

  $query = Db::dbConnection()->prepare($sql);
  $query->bindParam(":mes", $mes, PDO::PARAM_INT);
  $query->bindParam(":anio", $anio, PDO::PARAM_INT);
  $query->execute();

  $diasConVentas = array();
  while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
    $diasConVentas[] = array(
      'fecha' => $row['fecha'],
      'ventas' => (int)$row['ventas'],
      'total' => (float)$row['total']
    );
  }

  echo json_encode($diasConVentas);
} catch (PDOException $e) {
  echo json_encode([]);
}
