<?php

/**
 * Helper para comunicación con Web Services del SRI Ecuador.
 * Usa cURL puro (sin SoapClient) para evitar problemas SSL en Windows/Laragon.
 * errno=10054 / SSL_read reset son causados por el stack SSL nativo de PHP en Windows.
 */
class SriWebServiceHelper
{
  // Endpoints reales del SRI (sin ?wsdl)
  private static function getEndpointRecepcion($ambiente)
  {
    return $ambiente == '1'
      ? 'https://celcer.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline'
      : 'https://cel.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline';
  }

  private static function getEndpointAutorizacion($ambiente)
  {
    return $ambiente == '1'
      ? 'https://celcer.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline'
      : 'https://cel.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline';
  }

  /**
   * Ejecuta una llamada SOAP usando cURL puro con reintento en errores SSL.
   * IMPORTANTE: NO usar array_merge() con constantes CURLOPT (son enteros) porque
   * PHP re-enumera las claves desde 0, destruyendo CURLOPT_POST y convirtiendo
   * la petición en GET. Usar curl_setopt() separado para cada opción extra.
   */
  private static function curlSoap($endpoint, $soapEnvelope)
  {
    $headers = [
      'Content-Type: text/xml;charset=UTF-8',
      'SOAPAction: ""',
      'Content-Length: ' . strlen($soapEnvelope),
      'Connection: close',
    ];

    // Intento 1: TLS 1.2 explícito
    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST,           true);
    curl_setopt($ch, CURLOPT_POSTFIELDS,     $soapEnvelope);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_TIMEOUT,        15);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
    curl_setopt($ch, CURLOPT_SSLVERSION,     CURL_SSLVERSION_TLSv1_2);
    curl_setopt($ch, CURLOPT_HTTPHEADER,     $headers);
    $respuesta = curl_exec($ch);
    $error     = curl_error($ch);
    curl_close($ch);

    if ($respuesta !== false && !$error) {
      return $respuesta;
    }

    error_log("SRI cURL TLS1.2 fallido: $error — reintentando sin versión forzada...");
    sleep(2);

    // Intento 2: TLS sin versión forzada (fallback)
    $ch2 = curl_init($endpoint);
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch2, CURLOPT_POST,           true);
    curl_setopt($ch2, CURLOPT_POSTFIELDS,     $soapEnvelope);
    curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch2, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch2, CURLOPT_TIMEOUT,        15);
    curl_setopt($ch2, CURLOPT_CONNECTTIMEOUT, 8);
    curl_setopt($ch2, CURLOPT_HTTPHEADER,     $headers);
    $respuesta2 = curl_exec($ch2);
    $error2     = curl_error($ch2);
    curl_close($ch2);

    if ($respuesta2 !== false && !$error2) {
      return $respuesta2;
    }

    throw new Exception('cURL error al comunicarse con el SRI: ' . ($error2 ?: $error));
  }

  /**
   * Parsea la respuesta SOAP del SRI eliminando prefijos de namespace
   * para poder usar SimpleXML sin registrar namespaces manualmente.
   */
  private static function parsearRespuestaSoap($xmlRespuesta)
  {
    // Eliminar prefijos de namespace para simplificar el parseo
    $xml = preg_replace('/(<\/?)[\w]+:/', '$1', $xmlRespuesta);
    libxml_use_internal_errors(true);
    $dom = simplexml_load_string($xml);
    libxml_clear_errors();
    return $dom;
  }

  /**
   * Enviar comprobante al SRI para validación.
   */
  public static function enviarComprobante($xmlFirmado, $ambiente)
  {
    try {
      $endpoint  = self::getEndpointRecepcion($ambiente);
      $xmlBase64 = base64_encode($xmlFirmado);

      $soapEnvelope = '<?xml version="1.0" encoding="UTF-8"?>
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
                  xmlns:rec="http://ec.gob.sri.ws.recepcion">
  <soapenv:Header/>
  <soapenv:Body>
    <rec:validarComprobante>
      <xml>' . $xmlBase64 . '</xml>
    </rec:validarComprobante>
  </soapenv:Body>
</soapenv:Envelope>';

      $respuestaRaw = self::curlSoap($endpoint, $soapEnvelope);
      $dom          = self::parsearRespuestaSoap($respuestaRaw);

      if (!$dom) {
        return ['success' => false, 'estado' => 'ERROR', 'mensaje' => 'Respuesta inválida del SRI', 'detalles' => []];
      }

      // Navegar la estructura: Envelope > Body > validarComprobanteResponse > RespuestaRecepcionComprobante
      $respuesta = $dom->Body->validarComprobanteResponse->RespuestaRecepcionComprobante
        ?? $dom->Body->children()->children();

      $estado = '';
      // Intentar extraer el estado directamente
      foreach ($dom->Body->children() as $child) {
        foreach ($child->children() as $inner) {
          if (isset($inner->estado)) {
            $estado = (string)$inner->estado;
          }
        }
      }

      // Si no se encontró, buscar en el XML sin namespace
      if (!$estado) {
        preg_match('/<estado>([^<]+)<\/estado>/', $respuestaRaw, $m);
        $estado = $m[1] ?? '';
      }

      // Extraer mensajes de error si los hay
      $mensajes = [];
      preg_match_all(
        '/<mensaje>\s*<identificador>([^<]*)<\/identificador>\s*<mensaje>([^<]*)<\/mensaje>\s*(?:<tipo>([^<]*)<\/tipo>)?/s',
        $respuestaRaw,
        $matches,
        PREG_SET_ORDER
      );
      foreach ($matches as $match) {
        $mensajes[] = [
          'identificador' => $match[1],
          'mensaje'       => $match[2],
          'tipo'          => $match[3] ?? '',
        ];
      }

      $recibida = strtoupper(trim($estado)) === 'RECIBIDA';
      $textoMensaje = $recibida ? 'Comprobante recibido por el SRI' : 'Comprobante devuelto con errores';
      if (!empty($mensajes)) {
        $textoMensaje .= ' - ' . $mensajes[0]['mensaje'];
      }

      return [
        'success'  => $recibida,
        'estado'   => strtoupper(trim($estado)) ?: 'ERROR',
        'mensaje'  => $textoMensaje,
        'detalles' => $mensajes,
      ];
    } catch (Exception $e) {
      error_log('SRI enviarComprobante error: ' . $e->getMessage());
      return [
        'success'  => false,
        'estado'   => 'ERROR',
        'mensaje'  => 'Error de conexión con el SRI: ' . $e->getMessage(),
        'detalles' => [],
      ];
    }
  }

  /**
   * Consultar autorización de un comprobante por clave de acceso.
   */
  public static function consultarAutorizacion($claveAcceso, $ambiente)
  {
    try {
      $endpoint = self::getEndpointAutorizacion($ambiente);

      $soapEnvelope = '<?xml version="1.0" encoding="UTF-8"?>
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
                  xmlns:aut="http://ec.gob.sri.ws.autorizacion">
  <soapenv:Header/>
  <soapenv:Body>
    <aut:autorizacionComprobante>
      <claveAccesoComprobante>' . htmlspecialchars($claveAcceso, ENT_XML1) . '</claveAccesoComprobante>
    </aut:autorizacionComprobante>
  </soapenv:Body>
</soapenv:Envelope>';

      $respuestaRaw = self::curlSoap($endpoint, $soapEnvelope);
      file_put_contents('test_sri_raw.xml', $respuestaRaw);

      // Extraer estado de la autorización usando regex (más robusto con namespaces)
      preg_match('/<estado>([^<]+)<\/estado>/', $respuestaRaw, $mEstado);
      $estado = strtoupper(trim($mEstado[1] ?? ''));

      preg_match('/<numeroAutorizacion>([^<]+)<\/numeroAutorizacion>/', $respuestaRaw, $mNum);
      $numeroAutorizacion = $mNum[1] ?? null;

      preg_match('/<fechaAutorizacion>([^<]+)<\/fechaAutorizacion>/', $respuestaRaw, $mFecha);
      $fechaAutorizacionRaw = $mFecha[1] ?? null;
      // Convertir ISO 8601 (2026-06-07T23:21:36-05:00) a formato MySQL (2026-06-07 23:21:36)
      $fechaAutorizacion = null;
      if ($fechaAutorizacionRaw) {
        try {
          $dtAuth = new DateTime($fechaAutorizacionRaw);
          $fechaAutorizacion = $dtAuth->format('Y-m-d H:i:s');
        } catch (Exception $eDate) {
          $fechaAutorizacion = substr(str_replace('T', ' ', $fechaAutorizacionRaw), 0, 19);
        }
      }

      // Extraer XML autorizado (comprobante dentro de la respuesta de autorización)
      preg_match('/<comprobante><!\[CDATA\[(.*?)\]\]><\/comprobante>/s', $respuestaRaw, $mComp);
      if (!$mComp) {
        preg_match('/<comprobante>(.*?)<\/comprobante>/s', $respuestaRaw, $mComp);
      }
      $xmlAutorizado = $mComp[1] ?? null;

      // Mensajes de error/advertencia
      $mensajes = [];
      preg_match_all('/<mensaje>([^<]+)<\/mensaje>/', $respuestaRaw, $mMsgs);
      foreach ($mMsgs[1] as $msg) {
        $mensajes[] = ['mensaje' => $msg];
      }

      if ($estado === 'AUTORIZADO') {
        return [
          'success'           => true,
          'autorizado'        => true,
          'numeroAutorizacion' => $numeroAutorizacion,
          'fechaAutorizacion' => $fechaAutorizacion,
          'estado'            => 'AUTORIZADO',
          'mensajes'          => $mensajes,
          'xmlAutorizado'     => $xmlAutorizado,
        ];
      } elseif ($estado === 'NO AUTORIZADO' || $estado === 'NO_AUTORIZADO') {
        return [
          'success'           => true,
          'autorizado'        => false,
          'numeroAutorizacion' => null,
          'fechaAutorizacion' => null,
          'estado'            => 'NO_AUTORIZADO',
          'mensajes'          => $mensajes,
          'xmlAutorizado'     => null,
        ];
      } elseif ($estado !== '') {
        // Otro estado (EN PROCESO, etc.)
        return [
          'success'           => true,
          'autorizado'        => false,
          'numeroAutorizacion' => null,
          'fechaAutorizacion' => null,
          'estado'            => $estado,
          'mensajes'          => $mensajes,
          'xmlAutorizado'     => null,
        ];
      }

      return [
        'success'           => true,
        'autorizado'        => false,
        'numeroAutorizacion' => null,
        'fechaAutorizacion' => null,
        'estado'            => 'NO_AUTORIZADO',
        'mensajes'          => [],
        'xmlAutorizado'     => null,
      ];
    } catch (Exception $e) {
      error_log('SRI consultarAutorizacion error: ' . $e->getMessage());
      return [
        'success'           => false,
        'autorizado'        => false,
        'numeroAutorizacion' => null,
        'fechaAutorizacion' => null,
        'estado'            => 'ERROR',
        'mensajes'          => [['mensaje' => $e->getMessage()]],
        'xmlAutorizado'     => null,
      ];
    }
  }

  /**
   * Proceso completo: enviar al SRI y consultar autorización con reintentos.
   */
  public static function enviarYAutorizar($xmlFirmado, $claveAcceso, $ambiente, $maxReintentos = 2, $tiempoEspera = 2)
  {
    // 1. Enviar comprobante
    $resultadoEnvio = self::enviarComprobante($xmlFirmado, $ambiente);

    // Si el comprobante ya fue recibido previamente (el SRI devuelve error de clave registrada o secuencial registrado),
    // entonces podemos obviar el error de envío y saltar a consultar autorización.
    $yaRecibidoPrevio = false;
    if (!$resultadoEnvio['success'] && !empty($resultadoEnvio['detalles'])) {
      foreach ($resultadoEnvio['detalles'] as $detalle) {
        $msg = strtoupper($detalle['mensaje']);
        if ($detalle['identificador'] == '43' || strpos($msg, 'REGISTRAD') !== false) {
          $yaRecibidoPrevio = true;
          break;
        }
      }
    }

    if (!$resultadoEnvio['success'] && !$yaRecibidoPrevio) {
      return [
        'success'       => false,
        'autorizado'    => false,
        'mensaje'       => 'Error al enviar al SRI: ' . $resultadoEnvio['mensaje'],
        'estado'        => 'ERROR_ENVIO',
        'detallesEnvio' => $resultadoEnvio['detalles'],
      ];
    }

    // 2. Esperar que el SRI procese
    sleep(2);

    // 3. Consultar autorización con reintentos
    for ($intento = 1; $intento <= $maxReintentos; $intento++) {
      $resultadoAuth = self::consultarAutorizacion($claveAcceso, $ambiente);

      if ($resultadoAuth['success'] && $resultadoAuth['autorizado']) {
        return [
          'success'           => true,
          'autorizado'        => true,
          'numeroAutorizacion' => $resultadoAuth['numeroAutorizacion'],
          'fechaAutorizacion' => $resultadoAuth['fechaAutorizacion'],
          'estado'            => 'AUTORIZADO',
          'mensaje'           => 'Comprobante autorizado por el SRI',
          'xmlAutorizado'     => $resultadoAuth['xmlAutorizado'],
        ];
      }

      if ($resultadoAuth['estado'] === 'NO_AUTORIZADO') {
        return [
          'success'           => false,
          'autorizado'        => false,
          'numeroAutorizacion' => null,
          'fechaAutorizacion' => null,
          'estado'            => 'NO_AUTORIZADO',
          'mensaje'           => 'Comprobante no autorizado por el SRI',
          'mensajes'          => $resultadoAuth['mensajes'],
        ];
      }

      if ($intento < $maxReintentos) {
        sleep($tiempoEspera);
      }
    }

    return [
      'success'           => false,
      'autorizado'        => false,
      'numeroAutorizacion' => null,
      'fechaAutorizacion' => null,
      'estado'            => 'EN_PROCESO',
      'mensaje'           => 'El SRI está procesando el comprobante. Consulte más tarde.',
    ];
  }
}
