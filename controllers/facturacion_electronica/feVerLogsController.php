<?php
session_start();
if (!isset($_SESSION["email"])) {
  header("location:../../index.html");
}

include_once '../../models/facturacionElectronicaModel.php';

if (isset($_GET['factura_id'])) {
  $facturaId = $_GET['factura_id'];

  // Obtener datos de factura electrónica
  $sql = "SELECT fe.factura_electronica_id, fe.factura_id, fe.fe_clave_acceso, fe.fe_numero_comprobante,
                 fe.fe_estado_sri, fe.fe_numero_autorizacion, fe.fe_fecha_autorizacion, 
                 fe.fe_mensaje_sri, f.factura_num_comprobante 
          FROM tbl_factura_electronica fe
          INNER JOIN tbl_factura f ON fe.factura_id = f.factura_id
          WHERE fe.factura_id = :factura_id";

  $query = Db::dbConnection()->prepare($sql);
  $query->bindParam(':factura_id', $facturaId, PDO::PARAM_INT);
  $query->execute();
  $facturaElectronica = $query->fetch(PDO::FETCH_ASSOC);

  if (!$facturaElectronica) {
    echo "Esta factura no tiene registro de facturación electrónica.";
    exit;
  }

  // Obtener logs
  $logs = FacturacionElectronicaModel::obtenerLogsPorFacturaElectronica($facturaElectronica['factura_electronica_id']);

?>
  <!DOCTYPE html>
  <html>

  <head>
    <title>Logs Facturación Electrónica</title>
    <style>
      body {
        font-family: Arial, sans-serif;
        padding: 20px;
      }

      .info {
        background: #e3f2fd;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
      }

      .log {
        background: #f5f5f5;
        padding: 10px;
        margin: 10px 0;
        border-left: 4px solid #ccc;
      }

      .log.success {
        border-color: #4caf50;
      }

      .log.error {
        border-color: #f44336;
      }

      .log.warning {
        border-color: #ff9800;
      }

      .log.info {
        border-color: #2196f3;
      }

      pre {
        background: #263238;
        color: #aed581;
        padding: 10px;
        overflow-x: auto;
      }
    </style>
  </head>

  <body>
    <h1>Logs de Facturación Electrónica</h1>

    <div class="info">
      <h3>Información de la Factura</h3>
      <p><strong>Número:</strong> <?php echo $facturaElectronica['factura_num_comprobante']; ?></p>
      <p><strong>Clave de Acceso:</strong> <?php echo $facturaElectronica['fe_clave_acceso']; ?></p>
      <p><strong>Estado SRI:</strong> <?php echo $facturaElectronica['fe_estado_sri']; ?></p>
      <p><strong>Número Autorización:</strong> <?php echo $facturaElectronica['fe_numero_autorizacion'] ?? 'N/A'; ?></p>
      <p><strong>Fecha Autorización:</strong> <?php echo $facturaElectronica['fe_fecha_autorizacion'] ?? 'N/A'; ?></p>
      <p><strong>Mensaje SRI:</strong> <?php echo $facturaElectronica['fe_mensaje_sri'] ?? 'N/A'; ?></p>
    </div>

    <h2>Logs del Proceso</h2>
    <?php foreach ($logs as $log): ?>
      <div class="log <?php echo strtolower($log['log_tipo']); ?>">
        <strong><?php echo $log['log_tipo']; ?> - <?php echo $log['log_codigo']; ?></strong>
        <br>
        <em><?php echo $log['log_created_at']; ?></em>
        <p><?php echo $log['log_mensaje']; ?></p>
        <?php if ($log['log_detalle']): ?>
          <details>
            <summary>Ver detalles</summary>
            <pre><?php echo htmlspecialchars(json_encode(json_decode($log['log_detalle']), JSON_PRETTY_PRINT)); ?></pre>
          </details>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>

    <br>
    <button onclick="window.close()">Cerrar</button>
  </body>

  </html>
<?php
} else {
  echo "factura_id no especificado";
}
