<?php

/**
 * Helper para Facturación Electrónica Ecuador
 * Genera XMLs, calcula clave de acceso, firma documentos
 */

// Cargar autoloader de Composer
require_once __DIR__ . '/../vendor/autoload.php';

class FacturacionElectronicaHelper
{
  /**
   * Generar clave de acceso de 49 dígitos según normativa SRI Ecuador
   * 
   * @param string $fecha Fecha en formato ddmmyyyy
   * @param string $tipoComprobante 01=Factura, 04=Nota Crédito, etc.
   * @param string $ruc RUC del emisor
   * @param string $ambiente 1=Pruebas, 2=Producción
   * @param string $serie Establecimiento + Punto emisión (001001)
   * @param string $secuencial Número secuencial de 9 dígitos
   * @param string $codigoNumerico Código numérico de 8 dígitos (aleatorio)
   * @param string $tipoEmision 1=Normal, 2=Contingencia
   * @return string Clave de acceso de 49 dígitos
   */
  public static function generarClaveAcceso($fecha, $tipoComprobante, $ruc, $ambiente, $serie, $secuencial, $codigoNumerico, $tipoEmision = '1')
  {
    // Construir la clave sin el dígito verificador (48 dígitos)
    $claveSinDigito = $fecha . $tipoComprobante . $ruc . $ambiente . $serie . $secuencial . $codigoNumerico . $tipoEmision;

    // Calcular dígito verificador (módulo 11)
    $digitoVerificador = self::calcularDigitoVerificador($claveSinDigito);

    // Retornar clave completa de 49 dígitos
    return $claveSinDigito . $digitoVerificador;
  }

  /**
   * Calcular dígito verificador usando módulo 11
   */
  private static function calcularDigitoVerificador($cadena)
  {
    $factor = 2;
    $suma = 0;

    // Recorrer de derecha a izquierda
    for ($i = strlen($cadena) - 1; $i >= 0; $i--) {
      $suma += intval($cadena[$i]) * $factor;
      $factor = ($factor == 7) ? 2 : $factor + 1;
    }

    $resultado = 11 - ($suma % 11);

    if ($resultado == 11) {
      return 0;
    } elseif ($resultado == 10) {
      return 1;
    } else {
      return $resultado;
    }
  }

  /**
   * Generar código numérico aleatorio de 8 dígitos
   */
  public static function generarCodigoNumerico()
  {
    return str_pad(rand(1, 99999999), 8, '0', STR_PAD_LEFT);
  }

  /**
   * Generar XML de factura electrónica según esquema XSD del SRI
   */
  public static function generarXMLFactura($datosFactura, $configuracion)
  {
    $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><factura id="comprobante" version="1.0.0"></factura>');

    // Información tributaria
    $infoTributaria = $xml->addChild('infoTributaria');
    $infoTributaria->addChild('ambiente', $configuracion['config_fe_ambiente'] == 'PRUEBAS' ? '1' : '2');
    $infoTributaria->addChild('tipoEmision', $configuracion['config_fe_tipo_emision'] == 'NORMAL' ? '1' : '2');
    $infoTributaria->addChild('razonSocial', htmlspecialchars($configuracion['config_fe_razon_social'], ENT_XML1, 'UTF-8'));
    $infoTributaria->addChild('nombreComercial', htmlspecialchars($configuracion['config_fe_nombre_comercial'], ENT_XML1, 'UTF-8'));
    $infoTributaria->addChild('ruc', $configuracion['config_fe_ruc']);
    $infoTributaria->addChild('claveAcceso', $datosFactura['fe_clave_acceso']);
    $infoTributaria->addChild('codDoc', $datosFactura['fe_tipo_comprobante']);
    $infoTributaria->addChild('estab', $configuracion['config_fe_cod_establecimiento']);
    $infoTributaria->addChild('ptoEmi', $configuracion['config_fe_cod_punto_emision']);
    $infoTributaria->addChild('secuencial', $datosFactura['fe_secuencial']);
    $infoTributaria->addChild('dirMatriz', htmlspecialchars($configuracion['config_fe_direccion_matriz'], ENT_XML1, 'UTF-8'));

    // Contribuyentes bajo el Régimen RIMPE deben declararlo también en el XML (no solo impreso).
    // El texto legal exigido por el SRI difiere según la categoría (Negocio Popular vs. Emprendedor).
    $textoRimpe = self::textoLeyendaRimpe($configuracion['config_fe_contribuyente_rimpe'] ?? 'NO');
    if ($textoRimpe !== null) {
      $infoTributaria->addChild('contribuyenteRimpe', htmlspecialchars($textoRimpe, ENT_XML1, 'UTF-8'));
    }


    // Información de la factura
    $infoFactura = $xml->addChild('infoFactura');
    $infoFactura->addChild('fechaEmision', date('d/m/Y', strtotime($datosFactura['factura_fecha'])));
    $infoFactura->addChild('dirEstablecimiento', htmlspecialchars($configuracion['config_fe_direccion_sucursal'] ?: $configuracion['config_fe_direccion_matriz'], ENT_XML1, 'UTF-8'));

    // Obligado a llevar contabilidad — el XSD exige exactamente "SI" o "NO"
    $obligadoContabilidad = strtoupper(trim($configuracion['config_fe_obligado_contabilidad']));
    if (!in_array($obligadoContabilidad, ['SI', 'NO'])) {
      $obligadoContabilidad = 'NO';
    }
    $infoFactura->addChild('obligadoContabilidad', $obligadoContabilidad);

    // Información del comprador — orden exacto exigido por el XSD del SRI:
    // tipoIdentificacion → razonSocial → identificacion → direccion
    $infoFactura->addChild('tipoIdentificacionComprador', $datosFactura['fe_cliente_tipo_identificacion']);

    $infoFactura->addChild('razonSocialComprador', htmlspecialchars($datosFactura['fe_cliente_razon_social'], ENT_XML1, 'UTF-8'));

    $identificacionComprador = $datosFactura['fe_cliente_identificacion'];
    if ($datosFactura['fe_cliente_tipo_identificacion'] == '07' && empty($identificacionComprador)) {
      $identificacionComprador = '9999999999999';
    }
    $infoFactura->addChild('identificacionComprador', $identificacionComprador);
    $infoFactura->addChild('direccionComprador', htmlspecialchars($datosFactura['fe_cliente_direccion'] ?: 'S/N', ENT_XML1, 'UTF-8'));

    // Totales
    $infoFactura->addChild('totalSinImpuestos', number_format($datosFactura['fe_subtotal_sin_impuestos'], 2, '.', ''));
    $infoFactura->addChild('totalDescuento', number_format($datosFactura['fe_descuento_total'], 2, '.', ''));

    // Total con impuestos
    $totalConImpuestos = $infoFactura->addChild('totalConImpuestos');

    // IVA 0%
    if ($datosFactura['fe_subtotal_iva0'] > 0) {
      $totalImpuesto = $totalConImpuestos->addChild('totalImpuesto');
      $totalImpuesto->addChild('codigo', '2'); // Código IVA
      $totalImpuesto->addChild('codigoPorcentaje', '0'); // 0%
      $totalImpuesto->addChild('baseImponible', number_format($datosFactura['fe_subtotal_iva0'], 2, '.', ''));
      $totalImpuesto->addChild('valor', '0.00');
    }

    // IVA (12% o el porcentaje configurado)
    if ($datosFactura['fe_subtotal_iva'] > 0) {
      $totalImpuesto = $totalConImpuestos->addChild('totalImpuesto');
      $totalImpuesto->addChild('codigo', '2'); // Código IVA

      // Obtener porcentaje de IVA (15% en Ecuador desde 2025)
      $porcentajeIVA = round(($datosFactura['fe_iva_valor'] / $datosFactura['fe_subtotal_iva']) * 100);
      $codigoPorcentaje = '2'; // 2 = 12%, pero puede variar / 4 = 15%

      if ($porcentajeIVA >= 15) {
        $codigoPorcentaje = '4'; // 15%
      }

      $totalImpuesto->addChild('codigoPorcentaje', $codigoPorcentaje);
      $totalImpuesto->addChild('baseImponible', number_format($datosFactura['fe_subtotal_iva'], 2, '.', ''));
      $totalImpuesto->addChild('valor', number_format($datosFactura['fe_iva_valor'], 2, '.', ''));
    }

    $infoFactura->addChild('propina', number_format($datosFactura['fe_propina'], 2, '.', ''));
    $infoFactura->addChild('importeTotal', number_format($datosFactura['fe_total_comprobante'], 2, '.', ''));
    $infoFactura->addChild('moneda', 'DOLAR');

    // Agregar forma de pago
    $pagos = $infoFactura->addChild('pagos');
    $pago = $pagos->addChild('pago');
    $pago->addChild('formaPago', '01'); // 01 = Sin utilización del sistema financiero
    $pago->addChild('total', number_format($datosFactura['fe_total_comprobante'], 2, '.', ''));
    $pago->addChild('plazo', '0');
    $pago->addChild('unidadTiempo', 'dias');

    // Detalles de la factura
    $detalles = $xml->addChild('detalles');

    if (isset($datosFactura['detalles']) && is_array($datosFactura['detalles'])) {
      foreach ($datosFactura['detalles'] as $item) {
        $detalle = $detalles->addChild('detalle');
        $detalle->addChild('codigoPrincipal', htmlspecialchars($item['producto_codigo'], ENT_XML1));
        $detalle->addChild('descripcion', htmlspecialchars($item['producto_nombre'], ENT_XML1));
        $detalle->addChild('cantidad', number_format($item['detalle_cantidad'], 2, '.', ''));
        $detalle->addChild('precioUnitario', number_format($item['detalle_precio_unit'], 6, '.', ''));
        $detalle->addChild('descuento', number_format($item['detalle_descuento'] ?? 0, 2, '.', ''));
        $detalle->addChild('precioTotalSinImpuesto', number_format($item['precio_sin_impuesto'], 2, '.', ''));

        // Impuestos del detalle
        $impuestos = $detalle->addChild('impuestos');
        $impuesto = $impuestos->addChild('impuesto');
        $impuesto->addChild('codigo', '2'); // IVA
        $impuesto->addChild('codigoPorcentaje', $item['codigo_porcentaje_iva']);
        $impuesto->addChild('tarifa', intval($item['tarifa_iva']));
        $impuesto->addChild('baseImponible', number_format($item['precio_sin_impuesto'], 2, '.', ''));
        $impuesto->addChild('valor', number_format($item['valor_iva'], 2, '.', ''));
      }
    }

    // Información adicional
    if (isset($datosFactura['informacion_adicional']) && is_array($datosFactura['informacion_adicional'])) {
      $infoAdicional = $xml->addChild('infoAdicional');
      foreach ($datosFactura['informacion_adicional'] as $nombre => $valor) {
        $campoAdicional = $infoAdicional->addChild('campoAdicional', htmlspecialchars($valor, ENT_XML1, 'UTF-8'));
        $campoAdicional->addAttribute('nombre', htmlspecialchars($nombre, ENT_XML1, 'UTF-8'));
      }
    }

    // Formatear el XML con indentación
    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    $dom->loadXML($xml->asXML());

    return $dom->saveXML();
  }

  /**
   * Firmar XML con certificado digital .p12 usando formato XAdES-BES
   * requerido por el SRI Ecuador.
   *
   * @param string $xmlContent    Contenido del XML a firmar
   * @param string $certificadoP12 Contenido binario del archivo .p12
   * @param string $password      Contraseña del certificado
   * @return string|false XML firmado o false en caso de error
   */
  public static function firmarXML($xmlContent, $certificadoP12, $password)
  {
    try {
      $dsNs    = 'http://www.w3.org/2000/09/xmldsig#';
      $xadesNs = 'http://uri.etsi.org/01903/v1.3.2#';
      $sigId         = 'Signature0';
      $signedPropsId = 'Signature-SignedPropertiesID';

      // 1. Leer el .p12
      $certs = [];
      if (!openssl_pkcs12_read($certificadoP12, $certs, $password)) {
        throw new Exception('Error al leer certificado .p12: ' . openssl_error_string());
      }

      // 2. Extraer certificado en base64 puro y DER
      preg_match('/-----BEGIN CERTIFICATE-----\s*(.*?)\s*-----END CERTIFICATE-----/s', $certs['cert'], $m);
      $certB64    = preg_replace('/\s/', '', $m[1]);
      $certDer    = base64_decode($certB64);
      $certDigest = base64_encode(sha1($certDer, true));

      $certX509   = openssl_x509_read($certs['cert']);
      $certInfo   = openssl_x509_parse($certX509);
      if (!$certInfo || empty($certInfo['issuer'])) {
        throw new Exception('No se pudo parsear el certificado');
      }
      $issuerName = self::buildIssuerName((array)$certInfo['issuer']);
      $serialNum  = self::getSerialDecimal($certInfo);

      // 3. Cargar el XML preservando espacios y SIN formatOutput.
      //    Esta es la misma serialización que se enviará al SRI.
      $doc = new DOMDocument('1.0', 'UTF-8');
      $doc->preserveWhiteSpace = true;
      $doc->formatOutput = false;
      $doc->loadXML($xmlContent);
      $doc->documentElement->setIdAttribute('id', true);

      // 4. Digest de <factura id="comprobante"> antes de insertar la firma.
      //    El SRI aplica enveloped-signature (quita <ds:Signature>) y
      //    obtiene el mismo contenido que calculamos aquí.
      $facturaDigest = base64_encode(sha1($doc->documentElement->C14N(false, false), true));

      // 5. Construir xades:SignedProperties en documento temporal y calcular su digest
      $signingTime = (new DateTime('now', new DateTimeZone('America/Guayaquil')))->format('Y-m-d\TH:i:sP');
      $xDoc = new DOMDocument('1.0', 'UTF-8');
      $sp   = $xDoc->createElementNS($xadesNs, 'xades:SignedProperties');
      $sp->setAttribute('Id', $signedPropsId);
      $xDoc->appendChild($sp);

      $ssp  = $xDoc->createElementNS($xadesNs, 'xades:SignedSignatureProperties');
      $sp->appendChild($ssp);

      $stEl = $xDoc->createElementNS($xadesNs, 'xades:SigningTime');
      $stEl->appendChild($xDoc->createTextNode($signingTime));
      $ssp->appendChild($stEl);

      $scEl   = $xDoc->createElementNS($xadesNs, 'xades:SigningCertificate');
      $certEl = $xDoc->createElementNS($xadesNs, 'xades:Cert');
      $cdEl   = $xDoc->createElementNS($xadesNs, 'xades:CertDigest');
      $ssp->appendChild($scEl);
      $scEl->appendChild($certEl);
      $certEl->appendChild($cdEl);

      $dmEl = $xDoc->createElementNS($dsNs, 'ds:DigestMethod');
      $dmEl->setAttribute('Algorithm', $dsNs . 'sha1');
      $cdEl->appendChild($dmEl);
      $dvEl = $xDoc->createElementNS($dsNs, 'ds:DigestValue');
      $dvEl->appendChild($xDoc->createTextNode($certDigest));
      $cdEl->appendChild($dvEl);

      $isEl = $xDoc->createElementNS($xadesNs, 'xades:IssuerSerial');
      $certEl->appendChild($isEl);
      $inEl = $xDoc->createElementNS($dsNs, 'ds:X509IssuerName');
      $inEl->appendChild($xDoc->createTextNode($issuerName));
      $isEl->appendChild($inEl);
      $snEl = $xDoc->createElementNS($dsNs, 'ds:X509SerialNumber');
      $snEl->appendChild($xDoc->createTextNode($serialNum));
      $isEl->appendChild($snEl);

      $spDigest = base64_encode(sha1($sp->C14N(true, false), true));

      // 6. Construir el string Exc-C14N de ds:SignedInfo MANUALMENTE.
      //    PHP/libxml2 en Windows tiene un bug: cuando ds:SignedInfo está
      //    bajo ds:Signature xmlns:ds, omite xmlns:ds del C14N del hijo porque
      //    lo considera "ya en ámbito", pero el spec Exc-C14N exige incluirlo
      //    (el padre no está en el node-set). El validador Java del SRI siempre
      //    incluye xmlns:ds → los SHA1 no coincidían → "firma alterada".
      //    Construyendo el string manualmente obtenemos exactamente lo que Java produce.
      $siC14n =
        '<ds:SignedInfo xmlns:ds="http://www.w3.org/2000/09/xmldsig#">'
        . '<ds:CanonicalizationMethod Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#">'
        . '</ds:CanonicalizationMethod>'
        . '<ds:SignatureMethod Algorithm="http://www.w3.org/2000/09/xmldsig#rsa-sha1">'
        . '</ds:SignatureMethod>'
        . '<ds:Reference URI="#comprobante">'
        . '<ds:Transforms>'
        . '<ds:Transform Algorithm="http://www.w3.org/2000/09/xmldsig#enveloped-signature">'
        . '</ds:Transform>'
        . '</ds:Transforms>'
        . '<ds:DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1">'
        . '</ds:DigestMethod>'
        . '<ds:DigestValue>' . $facturaDigest . '</ds:DigestValue>'
        . '</ds:Reference>'
        . '<ds:Reference Type="http://uri.etsi.org/01903#SignedProperties" URI="#' . $signedPropsId . '">'
        . '<ds:Transforms>'
        . '<ds:Transform Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#">'
        . '</ds:Transform>'
        . '</ds:Transforms>'
        . '<ds:DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1">'
        . '</ds:DigestMethod>'
        . '<ds:DigestValue>' . $spDigest . '</ds:DigestValue>'
        . '</ds:Reference>'
        . '</ds:SignedInfo>';

      // 7. Firmar el C14N construido manualmente con RSA-SHA1
      $privKey = openssl_pkey_get_private($certs['pkey']);
      if (!openssl_sign($siC14n, $rawSig, $privKey, OPENSSL_ALGO_SHA1)) {
        throw new Exception('Error al firmar SignedInfo: ' . openssl_error_string());
      }
      $sigValue = base64_encode($rawSig);

      // 8. Construir el elemento ds:Signature en $doc con la misma estructura
      //    que produce el C14N manual (sin espacios ni nodos de texto extra).
      $sigEl = $doc->createElementNS($dsNs, 'ds:Signature');
      $sigEl->setAttribute('Id', $sigId);

      $siEl = $doc->createElementNS($dsNs, 'ds:SignedInfo');

      $cmEl = $doc->createElementNS($dsNs, 'ds:CanonicalizationMethod');
      $cmEl->setAttribute('Algorithm', 'http://www.w3.org/2001/10/xml-exc-c14n#');
      $siEl->appendChild($cmEl);

      $smEl = $doc->createElementNS($dsNs, 'ds:SignatureMethod');
      $smEl->setAttribute('Algorithm', $dsNs . 'rsa-sha1');
      $siEl->appendChild($smEl);

      // Referencia 1: comprobante
      $r1 = $doc->createElementNS($dsNs, 'ds:Reference');
      $r1->setAttribute('URI', '#comprobante');
      $siEl->appendChild($r1);
      $trEl = $doc->createElementNS($dsNs, 'ds:Transforms');
      $tEl  = $doc->createElementNS($dsNs, 'ds:Transform');
      $tEl->setAttribute('Algorithm', $dsNs . 'enveloped-signature');
      $trEl->appendChild($tEl);
      $r1->appendChild($trEl);
      $dm1 = $doc->createElementNS($dsNs, 'ds:DigestMethod');
      $dm1->setAttribute('Algorithm', $dsNs . 'sha1');
      $r1->appendChild($dm1);
      $dv1 = $doc->createElementNS($dsNs, 'ds:DigestValue');
      $dv1->appendChild($doc->createTextNode($facturaDigest));
      $r1->appendChild($dv1);

      // Referencia 2: xades:SignedProperties
      $r2 = $doc->createElementNS($dsNs, 'ds:Reference');
      $r2->setAttribute('Type', 'http://uri.etsi.org/01903#SignedProperties');
      $r2->setAttribute('URI', '#' . $signedPropsId);
      $siEl->appendChild($r2);
      $tr2El = $doc->createElementNS($dsNs, 'ds:Transforms');
      $t2El  = $doc->createElementNS($dsNs, 'ds:Transform');
      $t2El->setAttribute('Algorithm', 'http://www.w3.org/2001/10/xml-exc-c14n#');
      $tr2El->appendChild($t2El);
      $r2->appendChild($tr2El);
      $dm2 = $doc->createElementNS($dsNs, 'ds:DigestMethod');
      $dm2->setAttribute('Algorithm', $dsNs . 'sha1');
      $r2->appendChild($dm2);
      $dv2 = $doc->createElementNS($dsNs, 'ds:DigestValue');
      $dv2->appendChild($doc->createTextNode($spDigest));
      $r2->appendChild($dv2);

      $sigEl->appendChild($siEl);
      $doc->documentElement->appendChild($sigEl);

      // 8. Añadir ds:SignatureValue con la firma real
      $svEl = $doc->createElementNS($dsNs, 'ds:SignatureValue');
      $svEl->setAttribute('Id', 'SignatureValue');
      $svEl->appendChild($doc->createTextNode($sigValue));
      $sigEl->appendChild($svEl);

      // 9. Añadir ds:KeyInfo
      $kiEl  = $doc->createElementNS($dsNs, 'ds:KeyInfo');
      $kiEl->setAttribute('Id', 'KeyInfo');
      $x509d = $doc->createElementNS($dsNs, 'ds:X509Data');
      $x509c = $doc->createElementNS($dsNs, 'ds:X509Certificate');
      $x509c->appendChild($doc->createTextNode($certB64));
      $x509d->appendChild($x509c);
      $kiEl->appendChild($x509d);
      if (!empty($certs['extracerts'])) {
        foreach ($certs['extracerts'] as $extraCert) {
          preg_match('/-----BEGIN CERTIFICATE-----\s*(.*?)\s*-----END CERTIFICATE-----/s', $extraCert, $em);
          $eB64 = isset($em[1]) ? preg_replace('/\s/', '', $em[1]) : '';
          if ($eB64 === '') continue;
          $eXd = $doc->createElementNS($dsNs, 'ds:X509Data');
          $eXc = $doc->createElementNS($dsNs, 'ds:X509Certificate');
          $eXc->appendChild($doc->createTextNode($eB64));
          $eXd->appendChild($eXc);
          $kiEl->appendChild($eXd);
        }
      }
      $sigEl->appendChild($kiEl);

      // 10. Añadir ds:Object con xades:QualifyingProperties (XAdES-BES).
      //     Se añade DESPUÉS de calcular/firmar para no contaminar el C14N anterior.
      $objEl = $doc->createElementNS($dsNs, 'ds:Object');
      $objEl->setAttribute('Id', 'Signature-Object');
      $qpEl  = $doc->createElementNS($xadesNs, 'xades:QualifyingProperties');
      $qpEl->setAttribute('Target', '#' . $sigId);
      $qpEl->appendChild($doc->importNode($sp, true));
      $objEl->appendChild($qpEl);
      $sigEl->appendChild($objEl);

      // 11. Serializar UNA SOLA VEZ. Este es el XML exacto que se envía al SRI.
      return $doc->saveXML();
    } catch (Exception $e) {
      error_log('Error al firmar XML (XAdES-BES): ' . $e->getMessage());
      return false;
    }
  }

  /**
   * Construir el Distinguished Name del emisor en formato RFC 4514/2253
   * (orden inverso al de la secuencia ASN.1, más-específico primero).
   * El validador Java del SRI usa X500Principal.getName() que devuelve RFC 4514.
   */
  private static function buildIssuerName(array $issuer): string
  {
    $parts = [];
    foreach ($issuer as $key => $value) {
      $parts[] = $key . '=' . $value;
    }
    return implode(',', array_reverse($parts));
  }

  /**
   * Obtener el número de serie del certificado en formato decimal.
   */
  private static function getSerialDecimal(array $certInfo): string
  {
    $hex = '';
    if (!empty($certInfo['serialNumberHex'])) {
      $hex = ltrim($certInfo['serialNumberHex'], '0');
    } elseif (!empty($certInfo['serialNumber'])) {
      $sn = $certInfo['serialNumber'];
      if (substr($sn, 0, 2) === '0x') {
        $hex = ltrim(substr($sn, 2), '0');
      } else {
        return $sn;
      }
    }
    if ($hex === '') return '0';
    if (function_exists('gmp_init')) {
      return gmp_strval(gmp_init($hex, 16));
    }
    $dec = '0';
    for ($i = 0; $i < strlen($hex); $i++) {
      $dec = bcadd(bcmul($dec, '16'), (string) hexdec($hex[$i]));
    }
    return $dec;
  }

  /**
   * Validar estructura de XML según XSD del SRI
   */
  public static function validarXMLconXSD($xmlContent, $tipoComprobante = '01')
  {
    // Rutas a los esquemas XSD (debes descargarlos del SRI)
    $xsdPaths = [
      '01' => __DIR__ . '/../assets/xsd/factura_v1.0.0.xsd',
      '04' => __DIR__ . '/../assets/xsd/notaCredito_v1.0.0.xsd',
      '05' => __DIR__ . '/../assets/xsd/notaDebito_v1.0.0.xsd',
    ];

    if (!isset($xsdPaths[$tipoComprobante])) {
      return ['valido' => false, 'error' => 'Tipo de comprobante no soportado'];
    }

    if (!file_exists($xsdPaths[$tipoComprobante])) {
      return ['valido' => false, 'error' => 'Archivo XSD no encontrado'];
    }

    // Validar XML
    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadXML($xmlContent);

    if (!$dom->schemaValidate($xsdPaths[$tipoComprobante])) {
      $errors = libxml_get_errors();
      libxml_clear_errors();

      $erroresTexto = [];
      foreach ($errors as $error) {
        $erroresTexto[] = "Línea {$error->line}: {$error->message}";
      }

      return ['valido' => false, 'error' => implode("\n", $erroresTexto)];
    }

    return ['valido' => true];
  }


  /**
   * Leyenda legal exigida por el SRI según la categoría RIMPE del contribuyente.
   * Se usa tanto en el XML (contribuyenteRimpe) como en el PDF y el ticket.
   *
   * @param string $categoriaRimpe 'NO' | 'NEGOCIO_POPULAR' | 'EMPRENDEDOR'
   * @return string|null Texto a imprimir, o null si no aplica RIMPE
   */
  public static function textoLeyendaRimpe($categoriaRimpe)
  {
    switch ($categoriaRimpe) {
      case 'NEGOCIO_POPULAR':
        return 'CONTRIBUYENTE NEGOCIO POPULAR - RÉGIMEN RIMPE';
      case 'EMPRENDEDOR':
        return 'CONTRIBUYENTE RÉGIMEN RIMPE';
      default:
        return null;
    }
  }


  /**
   * Formatear número de comprobante: 001-001-000000001
   */
  public static function formatearNumeroComprobante($establecimiento, $puntoEmision, $secuencial)
  {
    $est = str_pad($establecimiento, 3, '0', STR_PAD_LEFT);
    $pto = str_pad($puntoEmision, 3, '0', STR_PAD_LEFT);
    $sec = str_pad($secuencial, 9, '0', STR_PAD_LEFT);

    return "{$est}-{$pto}-{$sec}";
  }

  /**
   * Obtener tipo de identificación según formato
   */
  public static function obtenerTipoIdentificacion($identificacion)
  {
    if (empty($identificacion) || $identificacion == '9999999999999') {
      return '07'; // Consumidor Final
    }

    $identificacion = preg_replace('/[^0-9]/', '', $identificacion);
    $longitud = strlen($identificacion);

    if ($longitud == 13 && substr($identificacion, 10, 3) == '001') {
      return '04'; // RUC
    } elseif ($longitud == 10) {
      return '05'; // Cédula
    } elseif ($longitud >= 6 && $longitud <= 20) {
      return '06'; // Pasaporte
    } else {
      return '07'; // Consumidor Final por defecto
    }
  }
}

// Nota: Para firmar XMLs necesitarás la librería xmlseclibs
// Instalar con: composer require robrichards/xmlseclibs
