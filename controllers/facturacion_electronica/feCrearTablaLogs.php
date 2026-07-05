<?php
// Script para crear la tabla de logs de facturación electrónica

include_once '../config/db.php';

$sql = "CREATE TABLE IF NOT EXISTS `tbl_log_facturacion_electronica` (
  `log_id` INT(11) NOT NULL AUTO_INCREMENT,
  `factura_electronica_id` INT(11) NOT NULL,
  `log_tipo` VARCHAR(20) NOT NULL COMMENT 'SUCCESS, ERROR, WARNING, INFO',
  `log_evento` VARCHAR(50) NOT NULL COMMENT 'FACTURA_GENERADA, ENVIANDO_SRI, AUTORIZADO, etc',
  `log_descripcion` TEXT NULL,
  `log_datos_json` TEXT NULL COMMENT 'Datos adicionales en formato JSON',
  `log_ip` VARCHAR(45) NULL,
  `log_usuario_id` INT(11) NULL,
  `log_created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  INDEX `idx_factura_electronica` (`factura_electronica_id`),
  INDEX `idx_tipo` (`log_tipo`),
  INDEX `idx_evento` (`log_evento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

try {
  Db::dbConnection()->exec($sql);
  echo "✅ Tabla 'tbl_log_facturacion_electronica' creada correctamente.<br>";

  // Verificar
  $check = Db::dbConnection()->query("SHOW TABLES LIKE 'tbl_log_facturacion_electronica'");
  if ($check->rowCount() > 0) {
    echo "✅ Tabla verificada exitosamente.<br>";

    // Mostrar estructura
    $columns = Db::dbConnection()->query("DESCRIBE tbl_log_facturacion_electronica");
    echo "<br><strong>Estructura de la tabla:</strong><pre>";
    while ($col = $columns->fetch(PDO::FETCH_ASSOC)) {
      echo $col['Field'] . " - " . $col['Type'] . "\n";
    }
    echo "</pre>";
  }
} catch (PDOException $e) {
  echo "❌ Error al crear la tabla: " . $e->getMessage();
}

echo "<br><br><a href='javascript:history.back()'>← Volver</a>";
