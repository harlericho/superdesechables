<?php

/**
 * Modelo para Facturación Electrónica Ecuador
 * Gestiona la configuración y operaciones de FE con el SRI
 */

require_once __DIR__ . '/../config/db.php';

class FacturacionElectronicaModel
{
  /**
   * Obtener la configuración activa de facturación electrónica
   * Incluye datos de tbl_config_serie para establecimiento, punto emisión y secuencial
   */
  public static function obtenerConfiguracionActiva()
  {
    try {
      $sql = "SELECT 
                cfe.*,
                cs.config_primera_serie as config_fe_cod_establecimiento,
                cs.config_segunda_serie as config_fe_cod_punto_emision,
                cs.config_secuencial as config_fe_secuencial_factura
              FROM tbl_config_facturacion_electronica cfe
              LEFT JOIN tbl_config_serie cs ON cs.config_id = 1
              WHERE cfe.config_fe_activo = 1 AND cfe.config_fe_estado = 1 
              LIMIT 1";
      $query = Db::dbConnection()->prepare($sql);
      $query->execute();
      return $query->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      error_log("Error al obtener configuración FE: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Obtener la configuración de FE (sin importar si está activa)
   * Usado para configuración inicial, subir certificado, etc.
   */
  public static function obtenerConfiguracion()
  {
    try {
      $sql = "SELECT 
                cfe.*,
                cs.config_primera_serie as config_fe_cod_establecimiento,
                cs.config_segunda_serie as config_fe_cod_punto_emision,
                cs.config_secuencial as config_fe_secuencial_factura
              FROM tbl_config_facturacion_electronica cfe
              LEFT JOIN tbl_config_serie cs ON cs.config_id = 1
              WHERE cfe.config_fe_estado = 1 
              LIMIT 1";
      $query = Db::dbConnection()->prepare($sql);
      $query->execute();
      return $query->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      error_log("Error al obtener configuración FE: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Guardar o actualizar la configuración de FE
   */
  public static function guardarConfiguracion($data)
  {
    try {
      // Verificar si ya existe una configuración
      $sqlCheck = "SELECT config_fe_id FROM tbl_config_facturacion_electronica WHERE config_fe_id = :id";
      $queryCheck = Db::dbConnection()->prepare($sqlCheck);
      $queryCheck->bindParam(":id", $data['config_fe_id'], PDO::PARAM_INT);
      $queryCheck->execute();

      if ($queryCheck->rowCount() > 0) {
        // Actualizar
        $sql = "UPDATE tbl_config_facturacion_electronica SET
                        config_fe_ruc = :ruc,
                        config_fe_razon_social = :razon_social,
                        config_fe_nombre_comercial = :nombre_comercial,
                        config_fe_direccion_matriz = :direccion_matriz,
                        config_fe_direccion_sucursal = :direccion_sucursal,
                        config_fe_obligado_contabilidad = :obligado_contabilidad,
                        config_fe_regimen_microempresa = :regimen_microempresa,
                        config_fe_agente_retencion = :agente_retencion,
                        config_fe_contribuyente_rimpe = :contribuyente_rimpe,
                        config_fe_certificado_password = :certificado_password,
                        config_fe_certificado_fecha_caducidad = :certificado_fecha_caducidad,
                        config_fe_ambiente = :ambiente,
                        config_fe_tipo_emision = :tipo_emision,
                        config_fe_email_envio = :email_envio,
                        config_fe_email_copia = :email_copia,
                        config_fe_enviar_email_automatico = :enviar_email_automatico,
                        config_fe_activo = :activo,
                        config_fe_permitir_ventas_simples = :permitir_ventas_simples
                        WHERE config_fe_id = :id";
      } else {
        // Insertar
        $sql = "INSERT INTO tbl_config_facturacion_electronica 
                        (config_fe_ruc, config_fe_razon_social, config_fe_nombre_comercial, 
                         config_fe_direccion_matriz, config_fe_direccion_sucursal, 
                         config_fe_obligado_contabilidad, config_fe_regimen_microempresa,
                         config_fe_agente_retencion, config_fe_contribuyente_rimpe,
                         config_fe_certificado_password, config_fe_certificado_fecha_caducidad,
                         config_fe_ambiente, config_fe_tipo_emision,
                         config_fe_email_envio, config_fe_email_copia, config_fe_enviar_email_automatico,
                         config_fe_activo, config_fe_permitir_ventas_simples)
                        VALUES (:ruc, :razon_social, :nombre_comercial,  
                                :direccion_matriz, :direccion_sucursal,
                                :obligado_contabilidad, :regimen_microempresa,
                                :agente_retencion, :contribuyente_rimpe,
                                :certificado_password, :certificado_fecha_caducidad,
                                :ambiente, :tipo_emision,
                                :email_envio, :email_copia, :enviar_email_automatico,
                                :activo, :permitir_ventas_simples)";
      }

      $query = Db::dbConnection()->prepare($sql);

      if (isset($data['config_fe_id']) && $queryCheck->rowCount() > 0) {
        $query->bindParam(":id", $data['config_fe_id'], PDO::PARAM_INT);
      }

      $query->bindParam(":ruc", $data['config_fe_ruc'], PDO::PARAM_STR);
      $query->bindParam(":razon_social", $data['config_fe_razon_social'], PDO::PARAM_STR);
      $query->bindParam(":nombre_comercial", $data['config_fe_nombre_comercial'], PDO::PARAM_STR);
      $query->bindParam(":direccion_matriz", $data['config_fe_direccion_matriz'], PDO::PARAM_STR);
      $query->bindParam(":direccion_sucursal", $data['config_fe_direccion_sucursal'], PDO::PARAM_STR);
      $query->bindParam(":obligado_contabilidad", $data['config_fe_obligado_contabilidad'], PDO::PARAM_STR);
      $query->bindParam(":regimen_microempresa", $data['config_fe_regimen_microempresa'], PDO::PARAM_STR);
      $query->bindParam(":agente_retencion", $data['config_fe_agente_retencion'], PDO::PARAM_STR);
      $query->bindParam(":contribuyente_rimpe", $data['config_fe_contribuyente_rimpe'], PDO::PARAM_STR);
      $query->bindParam(":certificado_password", $data['config_fe_certificado_password'], PDO::PARAM_STR);
      $query->bindParam(":certificado_fecha_caducidad", $data['config_fe_certificado_fecha_caducidad'], PDO::PARAM_STR);
      $query->bindParam(":ambiente", $data['config_fe_ambiente'], PDO::PARAM_STR);
      $query->bindParam(":tipo_emision", $data['config_fe_tipo_emision'], PDO::PARAM_STR);
      $query->bindParam(":email_envio", $data['config_fe_email_envio'], PDO::PARAM_STR);
      $query->bindParam(":email_copia", $data['config_fe_email_copia'], PDO::PARAM_STR);
      $query->bindParam(":enviar_email_automatico", $data['config_fe_enviar_email_automatico'], PDO::PARAM_INT);
      $query->bindParam(":activo", $data['config_fe_activo'], PDO::PARAM_INT);
      $query->bindParam(":permitir_ventas_simples", $data['config_fe_permitir_ventas_simples'], PDO::PARAM_INT);

      return $query->execute();
    } catch (PDOException $e) {
      error_log("Error al guardar configuración FE: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Subir certificado digital (.p12)
   */
  public static function subirCertificado($configId, $certificadoBlob, $nombreArchivo, $passwordEncriptado = null, $fechaCaducidad = null)
  {
    try {
      // Si se proporciona contraseña y fecha, actualizarlas también
      if ($passwordEncriptado !== null && $fechaCaducidad !== null) {
        $sql = "UPDATE tbl_config_facturacion_electronica SET 
                      config_fe_certificado_digital = :certificado,
                      config_fe_certificado_nombre = :nombre,
                      config_fe_certificado_password = :password,
                      config_fe_certificado_fecha_caducidad = :fecha_caducidad
                      WHERE config_fe_id = :id";

        $query = Db::dbConnection()->prepare($sql);
        $query->bindParam(":certificado", $certificadoBlob, PDO::PARAM_LOB);
        $query->bindParam(":nombre", $nombreArchivo, PDO::PARAM_STR);
        $query->bindParam(":password", $passwordEncriptado, PDO::PARAM_STR);
        $query->bindParam(":fecha_caducidad", $fechaCaducidad, PDO::PARAM_STR);
        $query->bindParam(":id", $configId, PDO::PARAM_INT);
      } else {
        $sql = "UPDATE tbl_config_facturacion_electronica SET 
                      config_fe_certificado_digital = :certificado,
                      config_fe_certificado_nombre = :nombre
                      WHERE config_fe_id = :id";

        $query = Db::dbConnection()->prepare($sql);
        $query->bindParam(":certificado", $certificadoBlob, PDO::PARAM_LOB);
        $query->bindParam(":nombre", $nombreArchivo, PDO::PARAM_STR);
        $query->bindParam(":id", $configId, PDO::PARAM_INT);
      }

      return $query->execute();
    } catch (PDOException $e) {
      error_log("Error al subir certificado: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Obtener el próximo secuencial para facturas desde tbl_config_serie
   */
  public static function obtenerProximoSecuencial($tipoComprobante = 'FACTURA')
  {
    try {
      // Para facturas, usar el secuencial de tbl_config_serie
      if ($tipoComprobante == 'FACTURA') {
        $sql = "SELECT cs.config_secuencial as secuencial 
                FROM tbl_config_serie cs 
                WHERE cs.config_id = 1 
                LIMIT 1";
        $query = Db::dbConnection()->prepare($sql);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);

        return $result ? str_pad($result['secuencial'], 9, '0', STR_PAD_LEFT) : '000000001';
      }

      // Para otros tipos de comprobantes (futura implementación)
      return '000000001';
    } catch (PDOException $e) {
      error_log("Error al obtener secuencial: " . $e->getMessage());
      return '000000001';
    }
  }

  /**
   * Incrementar el secuencial
   */
  public static function incrementarSecuencial($tipoComprobante = 'FACTURA')
  {
    try {
      $campo = 'config_fe_secuencial_factura';

      switch ($tipoComprobante) {
        case 'NOTA_CREDITO':
          $campo = 'config_fe_secuencial_nota_credito';
          break;
        case 'NOTA_DEBITO':
          $campo = 'config_fe_secuencial_nota_debito';
          break;
        case 'GUIA_REMISION':
          $campo = 'config_fe_secuencial_guia_remision';
          break;
        case 'RETENCION':
          $campo = 'config_fe_secuencial_retencion';
          break;
      }

      $sql = "UPDATE tbl_config_facturacion_electronica SET $campo = $campo + 1 WHERE config_fe_activo = 1";
      $query = Db::dbConnection()->prepare($sql);
      return $query->execute();
    } catch (PDOException $e) {
      error_log("Error al incrementar secuencial: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Guardar una factura electrónica
   */
  public static function guardarFacturaElectronica($data)
  {
    try {
      $sql = "INSERT INTO tbl_factura_electronica 
                    (factura_id, fe_tipo_comprobante, fe_clave_acceso, fe_numero_comprobante, fe_secuencial,
                     fe_cliente_identificacion, fe_cliente_tipo_identificacion, fe_cliente_razon_social,
                     fe_cliente_direccion, fe_cliente_email, fe_cliente_telefono,
                     fe_subtotal_sin_impuestos, fe_subtotal_iva0, fe_subtotal_iva, fe_iva_valor,
                     fe_descuento_total, fe_propina, fe_total_comprobante,
                     fe_xml_generado, fe_ambiente, fe_tipo_emision, fe_created_by)
                    VALUES 
                    (:factura_id, :tipo_comprobante, :clave_acceso, :numero_comprobante, :secuencial,
                     :cliente_identificacion, :cliente_tipo_identificacion, :cliente_razon_social,
                     :cliente_direccion, :cliente_email, :cliente_telefono,
                     :subtotal_sin_impuestos, :subtotal_iva0, :subtotal_iva, :iva_valor,
                     :descuento_total, :propina, :total_comprobante,
                     :xml_generado, :ambiente, :tipo_emision, :created_by)";

      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(":factura_id", $data['factura_id'], PDO::PARAM_INT);
      $query->bindParam(":tipo_comprobante", $data['fe_tipo_comprobante'], PDO::PARAM_STR);
      $query->bindParam(":clave_acceso", $data['fe_clave_acceso'], PDO::PARAM_STR);
      $query->bindParam(":numero_comprobante", $data['fe_numero_comprobante'], PDO::PARAM_STR);
      $query->bindParam(":secuencial", $data['fe_secuencial'], PDO::PARAM_STR);
      $query->bindParam(":cliente_identificacion", $data['fe_cliente_identificacion'], PDO::PARAM_STR);
      $query->bindParam(":cliente_tipo_identificacion", $data['fe_cliente_tipo_identificacion'], PDO::PARAM_STR);
      $query->bindParam(":cliente_razon_social", $data['fe_cliente_razon_social'], PDO::PARAM_STR);
      $query->bindParam(":cliente_direccion", $data['fe_cliente_direccion'], PDO::PARAM_STR);
      $query->bindParam(":cliente_email", $data['fe_cliente_email'], PDO::PARAM_STR);
      $query->bindParam(":cliente_telefono", $data['fe_cliente_telefono'], PDO::PARAM_STR);
      $query->bindParam(":subtotal_sin_impuestos", $data['fe_subtotal_sin_impuestos'], PDO::PARAM_STR);
      $query->bindParam(":subtotal_iva0", $data['fe_subtotal_iva0'], PDO::PARAM_STR);
      $query->bindParam(":subtotal_iva", $data['fe_subtotal_iva'], PDO::PARAM_STR);
      $query->bindParam(":iva_valor", $data['fe_iva_valor'], PDO::PARAM_STR);
      $query->bindParam(":descuento_total", $data['fe_descuento_total'], PDO::PARAM_STR);
      $query->bindParam(":propina", $data['fe_propina'], PDO::PARAM_STR);
      $query->bindParam(":total_comprobante", $data['fe_total_comprobante'], PDO::PARAM_STR);
      $query->bindParam(":xml_generado", $data['fe_xml_generado'], PDO::PARAM_STR);
      $query->bindParam(":ambiente", $data['fe_ambiente'], PDO::PARAM_STR);
      $query->bindParam(":tipo_emision", $data['fe_tipo_emision'], PDO::PARAM_STR);
      $query->bindParam(":created_by", $data['fe_created_by'], PDO::PARAM_INT);

      $resultado = $query->execute();

      if ($resultado) {
        // Usar LAST_INSERT_ID() de MySQL directamente (más confiable que PDO)
        $idQuery = Db::dbConnection()->query("SELECT LAST_INSERT_ID() as id");
        $idResult = $idQuery->fetch(PDO::FETCH_ASSOC);
        $insertedId = intval($idResult['id']);

        error_log("✓ Factura electrónica guardada exitosamente con ID: " . $insertedId);
        return $insertedId;
      }

      error_log("Error: execute() retornó false");
      return false;
    } catch (PDOException $e) {
      error_log("Error al guardar factura electrónica: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Actualizar estado de factura electrónica después de envío al SRI
   */
  public static function actualizarEstadoSRI($facturaElectronicaId, $data)
  {
    try {
      $setClauses = [
        'fe_estado_sri = :estado_sri',
        'fe_intentos_envio = fe_intentos_envio + 1',
        'fe_fecha_envio_sri = NOW()'
      ];
      $params = [':estado_sri' => $data['fe_estado_sri'], ':id' => $facturaElectronicaId];

      if (array_key_exists('fe_xml_firmado', $data)) {
        $setClauses[] = 'fe_xml_firmado = :xml_firmado';
        $params[':xml_firmado'] = $data['fe_xml_firmado'];
      }
      if (array_key_exists('fe_xml_autorizado', $data)) {
        $setClauses[] = 'fe_xml_autorizado = :xml_autorizado';
        $params[':xml_autorizado'] = $data['fe_xml_autorizado'];
      }
      if (array_key_exists('fe_numero_autorizacion', $data)) {
        $setClauses[] = 'fe_numero_autorizacion = :numero_autorizacion';
        $params[':numero_autorizacion'] = $data['fe_numero_autorizacion'];
      }
      if (array_key_exists('fe_fecha_autorizacion', $data)) {
        $setClauses[] = 'fe_fecha_autorizacion = :fecha_autorizacion';
        $params[':fecha_autorizacion'] = $data['fe_fecha_autorizacion'];
      }
      if (array_key_exists('fe_mensaje_sri', $data)) {
        $setClauses[] = 'fe_mensaje_sri = :mensaje_sri';
        $params[':mensaje_sri'] = $data['fe_mensaje_sri'];
      }

      $sql = "UPDATE tbl_factura_electronica SET " . implode(', ', $setClauses) . " WHERE factura_electronica_id = :id";
      $query = Db::dbConnection()->prepare($sql);
      return $query->execute($params);
    } catch (PDOException $e) {
      error_log("Error al actualizar estado SRI [FE_ID={$facturaElectronicaId}]: " . $e->getMessage()
          . " | SQL: " . ($sql ?? 'N/A')
          . " | fe_fecha_autorizacion=" . ($data['fe_fecha_autorizacion'] ?? 'no_key'));
      return false;
    }
  }

  /**
   * Registrar log de facturación electrónica
   */
  public static function registrarLog($facturaElectronicaId, $tipo, $evento, $descripcion, $datosJson = null, $usuarioId = null)
  {
    try {
      error_log("Intentando registrar log - FE ID: $facturaElectronicaId, Tipo: $tipo, Evento: $evento");

      $sql = "INSERT INTO tbl_log_facturacion_electronica 
                    (factura_electronica_id, log_tipo, log_evento, log_descripcion, log_datos_json, log_ip, log_usuario_id)
                    VALUES (:factura_electronica_id, :tipo, :evento, :descripcion, :datos_json, :ip, :usuario_id)";

      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(":factura_electronica_id", $facturaElectronicaId, PDO::PARAM_INT);
      $query->bindParam(":tipo", $tipo, PDO::PARAM_STR);
      $query->bindParam(":evento", $evento, PDO::PARAM_STR);
      $query->bindParam(":descripcion", $descripcion, PDO::PARAM_STR);
      $query->bindParam(":datos_json", $datosJson, PDO::PARAM_STR);

      $ip = $_SERVER['REMOTE_ADDR'] ?? null;
      $query->bindParam(":ip", $ip, PDO::PARAM_STR);
      $query->bindParam(":usuario_id", $usuarioId, PDO::PARAM_INT);

      $resultado = $query->execute();
      error_log("Resultado del INSERT log: " . ($resultado ? "SUCCESS" : "FAILED"));
      return $resultado;
    } catch (PDOException $e) {
      error_log("Error al registrar log FE: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Obtener logs de una factura electrónica
   */
  public static function obtenerLogsPorFacturaElectronica($facturaElectronicaId)
  {
    try {
      $sql = "SELECT log_id, factura_electronica_id, log_tipo, log_evento, 
                     log_descripcion, log_datos_json as log_detalle, log_created_at,
                     COALESCE(log_descripcion, '') as log_mensaje, 
                     COALESCE(log_evento, '') as log_codigo
              FROM tbl_log_facturacion_electronica 
              WHERE factura_electronica_id = :factura_electronica_id 
              ORDER BY log_created_at DESC";

      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(":factura_electronica_id", $facturaElectronicaId, PDO::PARAM_INT);
      $query->execute();
      return $query->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      error_log("Error al obtener logs FE: " . $e->getMessage());
      return [];
    }
  }

  /**
   * Obtener facturas electrónicas con filtros
   */
  public static function obtenerFacturasElectronicas($filtros = [])
  {
    try {
      $sql = "SELECT fe.*, f.factura_fecha, f.factura_num_comprobante,
                           u.usuario_nombres, u.usuario_email
                    FROM tbl_factura_electronica fe
                    INNER JOIN tbl_factura f ON fe.factura_id = f.factura_id
                    LEFT JOIN tbl_usuario u ON fe.fe_created_by = u.usuario_id
                    WHERE 1=1";

      if (isset($filtros['estado_sri'])) {
        $sql .= " AND fe.fe_estado_sri = :estado_sri";
      }

      if (isset($filtros['ambiente'])) {
        $sql .= " AND fe.fe_ambiente = :ambiente";
      }

      if (isset($filtros['fecha_desde'])) {
        $sql .= " AND f.factura_fecha >= :fecha_desde";
      }

      if (isset($filtros['fecha_hasta'])) {
        $sql .= " AND f.factura_fecha <= :fecha_hasta";
      }

      $sql .= " ORDER BY fe.fe_created_date DESC";

      $query = Db::dbConnection()->prepare($sql);

      if (isset($filtros['estado_sri'])) {
        $query->bindParam(":estado_sri", $filtros['estado_sri'], PDO::PARAM_STR);
      }
      if (isset($filtros['ambiente'])) {
        $query->bindParam(":ambiente", $filtros['ambiente'], PDO::PARAM_STR);
      }
      if (isset($filtros['fecha_desde'])) {
        $query->bindParam(":fecha_desde", $filtros['fecha_desde'], PDO::PARAM_STR);
      }
      if (isset($filtros['fecha_hasta'])) {
        $query->bindParam(":fecha_hasta", $filtros['fecha_hasta'], PDO::PARAM_STR);
      }

      $query->execute();
      return $query->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      error_log("Error al obtener facturas electrónicas: " . $e->getMessage());
      return [];
    }
  }
}
