<?php

/**
 * Controlador: Descargar XML de factura electrónica
 */

require_once '../../config/db.php';
include_once '../../models/facturacionElectronicaModel.php';

// Validar parámetros
if (!isset($_GET['factura_id'])) {
  die('ID de factura no proporcionado');
}

$facturaId = intval($_GET['factura_id']);

try {
  // Obtener factura electrónica
  $sql = "SELECT fe.*, f.factura_num_comprobante 
          FROM tbl_factura_electronica fe
          INNER JOIN tbl_factura f ON fe.factura_id = f.factura_id
          WHERE fe.factura_id = :factura_id 
          LIMIT 1";

  $query = Db::dbConnection()->prepare($sql);
  $query->bindParam(":factura_id", $facturaId, PDO::PARAM_INT);
  $query->execute();
  $facturaElectronica = $query->fetch(PDO::FETCH_ASSOC);

  if (!$facturaElectronica) {
    die('Factura electrónica no encontrada');
  }

  // Determinar qué XML descargar (firmado si existe, si no el generado)
  $xml = $facturaElectronica['fe_xml_firmado'] ?: $facturaElectronica['fe_xml_generado'];

  if (empty($xml)) {
    die('XML no disponible para esta factura');
  }

  // Nombre del archivo: CLAVEACCESO.xml
  $nombreArchivo = $facturaElectronica['fe_clave_acceso'] . '.xml';

  // Headers para descarga
  header('Content-Type: application/xml; charset=utf-8');
  header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
  header('Content-Length: ' . strlen($xml));
  header('Cache-Control: must-revalidate');
  header('Pragma: public');

  // Enviar XML
  echo $xml;
  exit;
} catch (Exception $e) {
  die('Error al descargar XML: ' . $e->getMessage());
}
