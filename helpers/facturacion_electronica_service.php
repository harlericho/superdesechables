<?php

/**
 * Servicio completo de Facturación Electrónica
 * Procesa facturas desde el momento de la venta
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/facturacionElectronicaModel.php';
require_once __DIR__ . '/../models/facturaModel.php';
require_once __DIR__ . '/../models/detalleModel.php';
require_once __DIR__ . '/facturacion_electronica_helper.php';
require_once __DIR__ . '/sri_webservice_helper.php';

class FacturacionElectronicaService
{
  /**
   * Procesar factura electrónica completa
   * 
   * @param int $facturaId ID de la factura creada
   * @param int $usuarioId ID del usuario que realiza la venta
   * @return array Resultado del proceso
   */
  public static function procesarFacturaElectronica($facturaId, $usuarioId)
  {
    set_time_limit(120);
    ignore_user_abort(true); // Continuar aunque Apache cierre la conexión con el cliente
    try {
      // 1. Obtener configuración
      $config = FacturacionElectronicaModel::obtenerConfiguracionActiva();
      if (!$config || $config['config_fe_activo'] != 1) {
        throw new Exception('Facturación electrónica no está activa');
      }

      // 2. Obtener datos de la factura
      $factura = self::obtenerDatosFactura($facturaId);
      if (!$factura) {
        throw new Exception('Factura no encontrada');
      }

      // 3. Obtener detalles con información de productos
      $detalles = self::obtenerDetallesConProductos($facturaId);

      if (empty($detalles)) {
        throw new Exception('No se encontraron detalles para la factura');
      }
      $fecha = date('dmY', strtotime($factura['factura_fecha']));
      $tipoComprobante = '01'; // Factura
      $ambiente = $config['config_fe_ambiente'] == 'PRUEBAS' ? '1' : '2';
      $serie = $config['config_fe_cod_establecimiento'] . $config['config_fe_cod_punto_emision'];
      $secuencial = FacturacionElectronicaModel::obtenerProximoSecuencial('FACTURA');
      $codigoNumerico = FacturacionElectronicaHelper::generarCodigoNumerico();
      $tipoEmision = $config['config_fe_tipo_emision'] == 'NORMAL' ? '1' : '2';

      $claveAcceso = FacturacionElectronicaHelper::generarClaveAcceso(
        $fecha,
        $tipoComprobante,
        $config['config_fe_ruc'],
        $ambiente,
        $serie,
        $secuencial,
        $codigoNumerico,
        $tipoEmision
      );

      // 5. Formatear número de comprobante
      $numeroComprobante = FacturacionElectronicaHelper::formatearNumeroComprobante(
        $config['config_fe_cod_establecimiento'],
        $config['config_fe_cod_punto_emision'],
        $secuencial
      );

      // 6. Preparar datos para XML
      $datosFactura = self::prepararDatosFactura($factura, $detalles, $config, $claveAcceso, $secuencial, $numeroComprobante);

      // 7. Generar XML
      $xml = FacturacionElectronicaHelper::generarXMLFactura($datosFactura, $config);

      // 8. Firmar XML (solo si hay certificado)
      $xmlFirmado = null;
      if (!empty($config['config_fe_certificado_digital'])) {
        require_once __DIR__ . '/../config/encryption.php';
        $passwordCertificado = Encryption::_desencryptacion($config['config_fe_certificado_password']);
        $xmlFirmado = FacturacionElectronicaHelper::firmarXML(
          $xml,
          $config['config_fe_certificado_digital'],
          $passwordCertificado
        );

        if (!$xmlFirmado) {
          throw new Exception('Error al firmar el XML');
        }
      }

      // 9. Guardar en base de datos
      $dataFE = [
        'factura_id' => $facturaId,
        'fe_tipo_comprobante' => '01',
        'fe_clave_acceso' => $claveAcceso,
        'fe_numero_comprobante' => $numeroComprobante,
        'fe_secuencial' => $secuencial,
        'fe_cliente_identificacion' => $factura['cliente_dni'] ?: '9999999999999',
        'fe_cliente_tipo_identificacion' => FacturacionElectronicaHelper::obtenerTipoIdentificacion($factura['cliente_dni']),
        'fe_cliente_razon_social' => trim($factura['cliente_nombres'] . ' ' . $factura['cliente_apellidos']),
        'fe_cliente_direccion' => $factura['cliente_direccion'] ?: 'S/N',
        'fe_cliente_email' => $factura['cliente_email'],
        'fe_cliente_telefono' => $factura['cliente_telefono'],
        'fe_subtotal_sin_impuestos' => $factura['factura_subtotal'],
        'fe_subtotal_iva0' => 0,
        'fe_subtotal_iva' => $factura['factura_subtotal'],
        'fe_iva_valor' => ($factura['factura_subtotal'] * $factura['factura_impuesto']) / 100,
        'fe_descuento_total' => 0,
        'fe_propina' => 0,
        'fe_total_comprobante' => $factura['factura_total'],
        'fe_xml_generado' => $xml,
        'fe_ambiente' => $config['config_fe_ambiente'],
        'fe_tipo_emision' => $config['config_fe_tipo_emision'],
        'fe_created_by' => $usuarioId
      ];

      $facturaElectronicaId = FacturacionElectronicaModel::guardarFacturaElectronica($dataFE);

      error_log("DEBUG: Factura Electrónica ID recibido: " . var_export($facturaElectronicaId, true));

      // Si no se obtuvo el ID, intentar recuperarlo de la base de datos
      if (!$facturaElectronicaId || $facturaElectronicaId == 0) {
        error_log("ADVERTENCIA: ID no obtenido automáticamente, consultando BD...");
        $query = Db::dbConnection()->prepare("SELECT factura_electronica_id FROM tbl_factura_electronica WHERE factura_id = ? ORDER BY factura_electronica_id DESC LIMIT 1");
        $query->execute([$facturaId]);
        $result = $query->fetch(PDO::FETCH_ASSOC);
        if ($result) {
          $facturaElectronicaId = intval($result['factura_electronica_id']);
          error_log("✓ ID recuperado de BD: " . $facturaElectronicaId);
        } else {
          error_log("ERROR: No se encontró el registro en BD");
          throw new Exception('Error: No se pudo obtener el ID de la factura electrónica');
        }
      }

      // 10. Actualizar con XML firmado si existe
      if ($xmlFirmado) {
        $updateData = [
          'fe_estado_sri' => 'PENDIENTE',
          'fe_xml_firmado' => $xmlFirmado,
          'fe_xml_autorizado' => null,
          'fe_numero_autorizacion' => null,
          'fe_fecha_autorizacion' => null,
          'fe_mensaje_sri' => 'XML generado y firmado correctamente, enviando al SRI...'
        ];

        FacturacionElectronicaModel::actualizarEstadoSRI($facturaElectronicaId, $updateData);
      }

      // 11. Enviar al SRI (si está configurado)
      $resultadoSRI = self::enviarAlSRI($xmlFirmado, $config, $facturaElectronicaId);

      // NOTA: El secuencial ya se incrementó con FacturaModel::aumentarSecuencialSerie() 
      // en facturaGuardarController.php, NO incrementar aquí

      // 12. Registrar log exitoso
      FacturacionElectronicaModel::registrarLog(
        $facturaElectronicaId,
        'SUCCESS',
        'FACTURA_GENERADA',
        'Factura electrónica generada correctamente',
        json_encode(['clave_acceso' => $claveAcceso, 'numero' => $numeroComprobante]),
        $usuarioId
      );

      $estadoSRI = $resultadoSRI['estado'] ?? 'PENDIENTE';
      return [
        'success' => true,
        'clave_acceso' => $claveAcceso,
        'numero_comprobante' => $numeroComprobante,
        'estado_sri' => $estadoSRI,
        'autorizado' => ($estadoSRI === 'AUTORIZADO'),
        'numero_autorizacion' => $resultadoSRI['numeroAutorizacion'] ?? null,
        'fecha_autorizacion' => $resultadoSRI['fechaAutorizacion'] ?? null,
        'factura_electronica_id' => $facturaElectronicaId,
        'mensaje' => 'Factura electrónica procesada correctamente'
      ];
    } catch (Exception $e) {
      error_log("Error en Facturación Electrónica: " . $e->getMessage());

      // Registrar log de error si tenemos facturaElectronicaId
      if (isset($facturaElectronicaId)) {
        FacturacionElectronicaModel::registrarLog(
          $facturaElectronicaId,
          'ERROR',
          'ERROR_PROCESO',
          $e->getMessage(),
          null,
          $usuarioId ?? null
        );
      }

      return [
        'success' => false,
        'mensaje' => $e->getMessage()
      ];
    }
  }

  /**
   * Obtener datos completos de la factura
   */
  private static function obtenerDatosFactura($facturaId)
  {
    try {
      $sql = "SELECT f.*, 
                           c.cliente_dni, c.cliente_nombres, c.cliente_apellidos, 
                           c.cliente_direccion, c.cliente_email, c.cliente_telefono,
                           c.cliente_tipo_identificacion,
                           u.usuario_nombres
                    FROM tbl_factura f
                    LEFT JOIN tbl_cliente c ON f.cliente_id = c.cliente_id
                    LEFT JOIN tbl_usuario u ON f.usuario_id = u.usuario_id
                    WHERE f.factura_id = :factura_id";

      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(":factura_id", $facturaId, PDO::PARAM_INT);
      $query->execute();

      return $query->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      error_log("Error al obtener factura: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Obtener detalles con información de productos
   */
  private static function obtenerDetallesConProductos($facturaId)
  {
    try {
      $sql = "SELECT d.*, p.producto_codigo, p.producto_nombre
                    FROM tbl_detalle d
                    INNER JOIN tbl_producto p ON d.producto_id = p.producto_id
                    WHERE d.factura_id = :factura_id";

      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(":factura_id", $facturaId, PDO::PARAM_INT);
      $query->execute();

      return $query->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      error_log("Error al obtener detalles: " . $e->getMessage());
      return [];
    }
  }

  /**
   * Preparar datos de la factura para el XML
   */
  private static function prepararDatosFactura($factura, $detalles, $config, $claveAcceso, $secuencial, $numeroComprobante)
  {
    // Calcular subtotales por tarifa de IVA
    $subtotalIVA0 = 0;
    $subtotalIVA = 0;
    $ivaValor = 0;

    // Por ahora asumimos que todo tiene IVA (según configuración)
    $porcentajeIVA = floatval($factura['factura_impuesto']);

    if ($porcentajeIVA > 0) {
      $subtotalIVA = floatval($factura['factura_subtotal']);
      $ivaValor = ($subtotalIVA * $porcentajeIVA) / 100;
    } else {
      $subtotalIVA0 = floatval($factura['factura_subtotal']);
    }

    $datosFactura = [
      'fe_clave_acceso' => $claveAcceso,
      'fe_tipo_comprobante' => '01',
      'fe_secuencial' => $secuencial,
      'factura_fecha' => $factura['factura_fecha'],
      'fe_cliente_identificacion' => $factura['cliente_dni'] ?: '9999999999999',
      'fe_cliente_tipo_identificacion' => FacturacionElectronicaHelper::obtenerTipoIdentificacion($factura['cliente_dni']),
      'fe_cliente_razon_social' => trim($factura['cliente_nombres'] . ' ' . $factura['cliente_apellidos']),
      'fe_cliente_direccion' => $factura['cliente_direccion'] ?: 'S/N',
      'fe_subtotal_sin_impuestos' => $factura['factura_subtotal'],
      'fe_subtotal_iva0' => $subtotalIVA0,
      'fe_subtotal_iva' => $subtotalIVA,
      'fe_iva_valor' => $ivaValor,
      'fe_descuento_total' => 0,
      'fe_propina' => 0,
      'fe_total_comprobante' => $factura['factura_total'],
      'detalles' => [],
      'informacion_adicional' => []
    ];

    // Agregar información adicional
    if (!empty($factura['cliente_email'])) {
      $datosFactura['informacion_adicional']['Email'] = $factura['cliente_email'];
    }
    if (!empty($factura['cliente_telefono'])) {
      $datosFactura['informacion_adicional']['Telefono'] = $factura['cliente_telefono'];
    }

    // Procesar detalles
    foreach ($detalles as $detalle) {
      $precioSinImpuesto = floatval($detalle['detalle_total']);
      $tarifaIVA = $porcentajeIVA;
      $codigoPorcentaje = '0'; // 0%

      // Determinar código de porcentaje según tarifa
      if ($tarifaIVA == 0) {
        $codigoPorcentaje = '0'; // 0%
      } elseif ($tarifaIVA >= 14 && $tarifaIVA <= 16) {
        $codigoPorcentaje = '4'; // 15%
      } elseif ($tarifaIVA >= 11 && $tarifaIVA <= 13) {
        $codigoPorcentaje = '2'; // 12%
      }

      $valorIVA = ($precioSinImpuesto * $tarifaIVA) / 100;

      $datosFactura['detalles'][] = [
        'producto_codigo' => $detalle['producto_codigo'] ?? 'PROD',
        'producto_nombre' => $detalle['producto_nombre'],
        'detalle_cantidad' => $detalle['detalle_cantidad'],
        'detalle_precio_unit' => $detalle['detalle_precio_unit'],
        'detalle_descuento' => $detalle['detalle_descuento'] ?? 0,
        'precio_sin_impuesto' => $precioSinImpuesto,
        'codigo_porcentaje_iva' => $codigoPorcentaje,
        'tarifa_iva' => $tarifaIVA,
        'valor_iva' => $valorIVA
      ];
    }

    return $datosFactura;
  }

  /**
   * Enviar XML firmado al SRI
   * 
   * NOTA: Esta función es un placeholder
   * Aquí debes implementar la conexión SOAP real con el SRI
   */
  /**
   * Enviar XML firmado al SRI y obtener autorización
   */
  private static function enviarAlSRI($xmlFirmado, $config, $facturaElectronicaId)
  {
    if (!$xmlFirmado) {
      FacturacionElectronicaModel::registrarLog(
        $facturaElectronicaId,
        'WARNING',
        'SIN_FIRMA',
        'No hay XML firmado para enviar al SRI',
        null,
        null
      );
      return ['estado' => 'PENDIENTE', 'mensaje' => 'XML sin firmar'];
    }

    try {
      // Obtener clave de acceso del XML firmado
      // El XML firmado tiene namespaces de firma — usar regex para extraer la clave
      $claveAcceso = '';
      if (preg_match('/<claveAcceso>(\d{49})<\/claveAcceso>/', $xmlFirmado, $m)) {
        $claveAcceso = $m[1];
      }
      if (!$claveAcceso) {
        // Fallback: cargar XML suprimiendo errores de namespace
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xmlFirmado);
        libxml_clear_errors();
        if ($xml) {
          $claveAcceso = (string)$xml->infoTributaria->claveAcceso;
        }
      }
      if (!$claveAcceso) {
        throw new Exception('No se pudo extraer la clave de acceso del XML firmado');
      }

      // Determinar ambiente (1=Pruebas, 2=Producción)
      $ambiente = ($config['config_fe_ambiente'] == 'PRUEBAS') ? '1' : '2';

      FacturacionElectronicaModel::registrarLog(
        $facturaElectronicaId,
        'INFO',
        'ENVIANDO_SRI',
        'Enviando comprobante al SRI en ambiente ' . $config['config_fe_ambiente'],
        json_encode(['clave_acceso' => $claveAcceso]),
        null
      );

      // Enviar al SRI y obtener autorización
      $resultado = SriWebServiceHelper::enviarYAutorizar($xmlFirmado, $claveAcceso, $ambiente);

      if ($resultado['success'] && $resultado['autorizado']) {
        // AUTORIZADO - Actualizar en base de datos
        $updateData = [
          'fe_estado_sri' => 'AUTORIZADO',
          'fe_numero_autorizacion' => $resultado['numeroAutorizacion'],
          'fe_fecha_autorizacion' => $resultado['fechaAutorizacion'],
          'fe_xml_autorizado' => $resultado['xmlAutorizado'],
          'fe_mensaje_sri' => $resultado['mensaje']
        ];

        FacturacionElectronicaModel::actualizarEstadoSRI($facturaElectronicaId, $updateData);

        FacturacionElectronicaModel::registrarLog(
          $facturaElectronicaId,
          'SUCCESS',
          'AUTORIZADO',
          'Comprobante autorizado por el SRI',
          json_encode([
            'numero_autorizacion' => $resultado['numeroAutorizacion'],
            'fecha_autorizacion' => $resultado['fechaAutorizacion']
          ]),
          null
        );

        return [
          'estado' => 'AUTORIZADO',
          'mensaje' => 'Comprobante autorizado por el SRI',
          'numeroAutorizacion' => $resultado['numeroAutorizacion'],
          'fechaAutorizacion' => $resultado['fechaAutorizacion']
        ];
      } elseif ($resultado['estado'] === 'NO_AUTORIZADO') {
        // RECHAZADO
        $mensajeError = $resultado['mensaje'];
        if (isset($resultado['mensajes']) && !empty($resultado['mensajes'])) {
          $mensajeError .= ' - ' . $resultado['mensajes'][0]['mensaje'];
        }

        $updateData = [
          'fe_estado_sri' => 'NO_AUTORIZADO',
          'fe_mensaje_sri' => $mensajeError
        ];

        FacturacionElectronicaModel::actualizarEstadoSRI($facturaElectronicaId, $updateData);

        FacturacionElectronicaModel::registrarLog(
          $facturaElectronicaId,
          'ERROR',
          'NO_AUTORIZADO',
          'Comprobante rechazado por el SRI',
          json_encode($resultado['mensajes'] ?? []),
          null
        );

        return [
          'estado' => 'NO_AUTORIZADO',
          'mensaje' => $mensajeError
        ];
      } elseif ($resultado['estado'] === 'EN_PROCESO') {
        // EN PROCESO - El SRI aún está procesando
        $updateData = [
          'fe_estado_sri' => 'EN_PROCESO',
          'fe_mensaje_sri' => $resultado['mensaje']
        ];

        FacturacionElectronicaModel::actualizarEstadoSRI($facturaElectronicaId, $updateData);

        FacturacionElectronicaModel::registrarLog(
          $facturaElectronicaId,
          'WARNING',
          'EN_PROCESO',
          'El SRI está procesando el comprobante',
          null,
          null
        );

        return [
          'estado' => 'EN_PROCESO',
          'mensaje' => $resultado['mensaje']
        ];
      } else {
        // ERROR DE ENVÍO
        $mensajeError = $resultado['mensaje'];
        if (isset($resultado['detallesEnvio']) && !empty($resultado['detallesEnvio'])) {
          $mensajeError .= ' - ' . $resultado['detallesEnvio'][0]['mensaje'];
        }

        $updateData = [
          'fe_estado_sri' => 'ERROR',
          'fe_mensaje_sri' => $mensajeError
        ];

        FacturacionElectronicaModel::actualizarEstadoSRI($facturaElectronicaId, $updateData);

        FacturacionElectronicaModel::registrarLog(
          $facturaElectronicaId,
          'ERROR',
          'ERROR_ENVIO',
          'Error al enviar al SRI: ' . $mensajeError,
          json_encode($resultado),
          null
        );

        return [
          'estado' => 'ERROR',
          'mensaje' => $mensajeError
        ];
      }
    } catch (Exception $e) {
      error_log("Error al enviar al SRI: " . $e->getMessage());

      $updateData = [
        'fe_estado_sri' => 'ERROR',
        'fe_mensaje_sri' => 'Excepción al comunicarse con el SRI: ' . $e->getMessage()
      ];

      FacturacionElectronicaModel::actualizarEstadoSRI($facturaElectronicaId, $updateData);

      FacturacionElectronicaModel::registrarLog(
        $facturaElectronicaId,
        'ERROR',
        'EXCEPTION',
        'Excepción al enviar al SRI',
        json_encode(['error' => $e->getMessage()]),
        null
      );

      return [
        'estado' => 'ERROR',
        'mensaje' => 'Error de comunicación con el SRI: ' . $e->getMessage()
      ];
    }
  }
}
