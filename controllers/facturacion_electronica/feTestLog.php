<?php
// Script de prueba para registrar un log manualmente

include_once '../config/db.php';
include_once '../models/facturacionElectronicaModel.php';

echo "<h2>Test de Registro de Logs</h2>";

// 1. Verificar tabla existe
try {
  $check = Db::dbConnection()->query("SHOW TABLES LIKE 'tbl_log_facturacion_electronica'");
  if ($check->rowCount() > 0) {
    echo "✅ Tabla 'tbl_log_facturacion_electronica' existe<br>";
  } else {
    echo "❌ Tabla NO existe<br>";
    exit;
  }
} catch (Exception $e) {
  echo "❌ Error: " . $e->getMessage() . "<br>";
  exit;
}

// 2. Obtener una factura electrónica existente
try {
  $query = Db::dbConnection()->query("SELECT factura_electronica_id FROM tbl_factura_electronica ORDER BY factura_electronica_id DESC LIMIT 1");
  $fe = $query->fetch(PDO::FETCH_ASSOC);

  if ($fe) {
    $feId = $fe['factura_electronica_id'];
    echo "✅ Factura Electrónica ID encontrado: $feId<br>";

    // 3. Intentar registrar un log de prueba
    echo "<br><strong>Intentando registrar log de prueba...</strong><br>";
    $resultado = FacturacionElectronicaModel::registrarLog(
      $feId,
      'INFO',
      'TEST_MANUAL',
      'Este es un log de prueba creado manualmente',
      json_encode(['test' => true]),
      null
    );

    if ($resultado) {
      echo "✅ Log registrado exitosamente<br>";

      // 4. Verificar que se guardó
      $query2 = Db::dbConnection()->prepare("SELECT * FROM tbl_log_facturacion_electronica WHERE factura_electronica_id = ? ORDER BY log_id DESC LIMIT 1");
      $query2->execute([$feId]);
      $log = $query2->fetch(PDO::FETCH_ASSOC);

      if ($log) {
        echo "✅ Log verificado en base de datos:<br>";
        echo "<pre>";
        print_r($log);
        echo "</pre>";
      } else {
        echo "❌ No se encontró el log en la base de datos<br>";
      }
    } else {
      echo "❌ Error al registrar log<br>";
    }
  } else {
    echo "❌ No hay facturas electrónicas en la base de datos<br>";
  }
} catch (Exception $e) {
  echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<br><br><a href='javascript:history.back()'>← Volver</a>";
