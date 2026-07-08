<?php

require_once __DIR__ . '/../assets/fpdf/fpdf.php';
require_once __DIR__ . '/../config/empresa.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/facturacionElectronicaModel.php';

// Obtiene los datos de facturación electrónica (autorización SRI, claves, valores) de una factura.
function facturaPdfObtenerFE($factura_id)
{
  $sql = "SELECT fe_clave_acceso, fe_numero_autorizacion, fe_fecha_autorizacion, fe_estado_sri,
                 fe_xml_autorizado, fe_xml_firmado, fe_ambiente, fe_tipo_emision, fe_numero_comprobante,
                 fe_subtotal_sin_impuestos, fe_subtotal_iva0, fe_subtotal_iva, fe_iva_valor,
                 fe_descuento_total, fe_propina, fe_total_comprobante
          FROM tbl_factura_electronica
          WHERE factura_id = :factura_id
          LIMIT 1";
  $query = Db::dbConnection()->prepare($sql);
  $query->bindParam(":factura_id", $factura_id, PDO::PARAM_INT);
  $query->execute();
  return $query->fetch(PDO::FETCH_ASSOC) ?: null;
}

// Datos del emisor: usa la configuración de facturación electrónica si está activa, si no cae a empresa.ini.
function facturaPdfObtenerEmpresaInfo()
{
  $config = FacturacionElectronicaModel::obtenerConfiguracionActiva();
  if ($config) {
    return [
      'nombre' => $config['config_fe_nombre_comercial'] ?: $config['config_fe_razon_social'],
      'ruc' => $config['config_fe_ruc'],
      'direccion_matriz' => $config['config_fe_direccion_matriz'],
      'direccion_sucursal' => $config['config_fe_direccion_sucursal'] ?? null,
      'obligado_contabilidad' => $config['config_fe_obligado_contabilidad'] ?? null,
      'ambiente' => $config['config_fe_ambiente'] ?? null,
    ];
  }
  return [
    'nombre' => Empresa::getNombre(),
    'ruc' => Empresa::getRuc(),
    'direccion_matriz' => Empresa::getDireccion(),
    'direccion_sucursal' => null,
    'obligado_contabilidad' => null,
    'ambiente' => null,
  ];
}

// Dibuja un código de barras Code 39 a partir de x,y. Sirve para representar visualmente la clave de acceso.
function facturaPdfCode39($pdf, $x, $y, $code, $narrow = 0.22, $height = 12)
{
  $wide = $narrow * 2.5;
  $gap = $narrow;
  $barChar = [
    '0' => '000110100',
    '1' => '100100001',
    '2' => '001100001',
    '3' => '101100000',
    '4' => '000110001',
    '5' => '100110000',
    '6' => '001110000',
    '7' => '000100101',
    '8' => '100100100',
    '9' => '001100100',
    '-' => '010000101',
  ];

  $code = '*' . strtoupper($code) . '*';
  $pdf->SetFillColor(0, 0, 0);

  for ($i = 0; $i < strlen($code); $i++) {
    if (!isset($barChar[$code[$i]]) && $code[$i] !== '*') {
      continue;
    }
    $char = $code[$i] === '*' ? '010010100' : $barChar[$code[$i]];
    for ($j = 0; $j < 9; $j++) {
      $lineWidth = ($char[$j] == '1') ? $wide : $narrow;
      if ($j % 2 == 0) {
        $pdf->Rect($x, $y, $lineWidth, $height, 'F');
      }
      $x += $lineWidth;
    }
    $x += $gap;
  }
}

// Cell de label en negrita + valor normal, en una sola línea.
function facturaPdfLV($pdf, $label, $valor, $wLabel, $wValor, $h = 5, $sizeLabel = 8, $sizeValor = 8, $ln = 0, $alignValor = 'L')
{
  $pdf->SetFont('Arial', 'B', $sizeLabel);
  $pdf->Cell($wLabel, $h, utf8_decode($label), 0, 0, 'L');
  $pdf->SetFont('Arial', '', $sizeValor);
  $pdf->Cell($wValor, $h, utf8_decode((string)($valor ?? '')), 0, $ln, $alignValor);
}

/**
 * Construye el PDF de la factura (estilo RIDE de facturación electrónica) y lo retorna sin enviarlo a salida.
 */
function construirFacturaPdf(array $detallesFactura, ?array $facturaElectronica): FPDF
{
  $primerDetalle = $detallesFactura[0];
  $clienteNombres = $primerDetalle['cliente_nombres'] . " " . $primerDetalle['cliente_apellidos'];
  $clienteDni = $primerDetalle['cliente_dni'];
  $clienteEmail = $primerDetalle['cliente_email'];
  $clienteDireccion = $primerDetalle['cliente_direccion'];
  $clienteTelefono = $primerDetalle['cliente_telefono'];
  $facturaComprobante = $primerDetalle['factura_num_comprobante'];
  $facturaFecha = $primerDetalle['factura_fecha'];
  $facturaSubTotal = $primerDetalle['factura_subtotal'];
  $facturaImpuesto = $primerDetalle['factura_impuesto'];
  $facturaTotal = $primerDetalle['factura_total'];
  $facturaDescuentoValor = isset($primerDetalle['factura_descuento_global']) ? $primerDetalle['factura_descuento_global'] : 0;
  $facturaDescuentoPorcentaje = isset($primerDetalle['factura_descuento_global_porcentaje']) ? $primerDetalle['factura_descuento_global_porcentaje'] : 0;
  $facturaIva = isset($primerDetalle['factura_iva']) ? $primerDetalle['factura_iva'] : 0;
  $formaPago = $primerDetalle['tipo_comp_descripcion'];

  $empresa = facturaPdfObtenerEmpresaInfo();
  $tieneFE = $facturaElectronica && !empty($facturaElectronica['fe_clave_acceso']);

  $pdf = new FPDF('P', 'mm', 'A4');
  $pdf->AddPage();
  $pdf->SetMargins(10, 8, 10);

  // --- ENCABEZADO: columna izquierda (logo + datos del emisor), columna derecha (factura + autorización SRI) ---
  $xIzq = 10;
  $wIzq = 90;
  $xDer = 105;
  $wDer = 95;
  $yTop = 8;
  $logoHeight = 32;

  // -- Columna derecha: se escribe primero (sin bordes) para medir su altura real --
  $pdf->SetXY($xDer + 3, $yTop + 2);
  facturaPdfLV($pdf, 'R.U.C.: ', $empresa['ruc'], 18, $wDer - 24, 5, 8, 8, 1, 'R');
  $pdf->SetX($xDer);
  $pdf->SetFont('Arial', 'B', 15);
  $pdf->Cell($wDer, 7, 'FACTURA', 0, 2, 'C');

  if ($tieneFE) {
    $estadoSRI = $facturaElectronica['fe_estado_sri'] ?? 'PENDIENTE';
    $pdf->SetX($xDer + 3);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(24, 5, 'ESTADO SRI:', 0, 0, 'L');
    if ($estadoSRI === 'AUTORIZADO') {
      $pdf->SetTextColor(0, 128, 0);
    } elseif (in_array($estadoSRI, ['ERROR', 'NO_AUTORIZADO', 'ERROR_ENVIO'])) {
      $pdf->SetTextColor(180, 0, 0);
    } else {
      $pdf->SetTextColor(200, 100, 0);
    }
    $pdf->Cell($wDer - 27, 5, utf8_decode($estadoSRI), 0, 2, 'L');
    $pdf->SetTextColor(0, 0, 0);
  }
  $pdf->SetX($xDer + 3);
  $pdf->SetFont('Arial', 'B', 9);
  $pdf->Cell($wDer - 6, 5, 'No. ' . $facturaComprobante, 0, 2, 'L');

  if ($tieneFE) {
    $pdf->SetX($xDer + 3);
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->Cell($wDer - 6, 4, utf8_decode('NÚMERO DE AUTORIZACIÓN:'), 0, 2, 'L');
    $pdf->SetX($xDer + 3);
    $pdf->SetFont('Arial', '', 6.5);
    $pdf->MultiCell($wDer - 6, 3.2, $facturaElectronica['fe_numero_autorizacion'], 0, 'L');

    $pdf->SetX($xDer + 3);
    facturaPdfLV($pdf, 'Fecha Aut.: ', $facturaElectronica['fe_fecha_autorizacion'], 18, $wDer - 27, 4, 7, 7, 1);

    $pdf->SetX($xDer + 3);
    $wMitad = ($wDer - 6) / 2;
    facturaPdfLV($pdf, 'Ambiente: ', $facturaElectronica['fe_ambiente'] ?? $empresa['ambiente'] ?? 'PRODUCCION', 18, $wMitad - 18, 4, 7, 7);
    facturaPdfLV($pdf, 'Emisión: ', $facturaElectronica['fe_tipo_emision'] ?? 'NORMAL', 15, $wMitad - 15, 4, 7, 7, 1);

    $pdf->SetX($xDer);
    $pdf->Ln(1);
    $pdf->SetX($xDer);
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->Cell($wDer, 5, utf8_decode('CLAVE DE ACCESO'), 0, 2, 'C');

    $claveAcceso = $facturaElectronica['fe_clave_acceso'];
    facturaPdfCode39($pdf, $xDer + 2, $pdf->GetY(), $claveAcceso, 0.115, 8);
    $pdf->SetXY($xDer, $pdf->GetY() + 9);
    $pdf->SetFont('Arial', '', 6);
    $pdf->Cell($wDer, 3, $claveAcceso, 0, 1, 'C');
  }
  $alturaDer = ($pdf->GetY() + 2) - $yTop;

  // -- Columna izquierda: caja de logo (altura fija) + caja de datos del emisor (mide su contenido) --
  $yEmpresa = $yTop + $logoHeight + 3;
  $pdf->SetXY($xIzq + 2, $yEmpresa + 2);
  $pdf->SetFont('Arial', 'B', 9);
  $pdf->Cell($wIzq - 4, 5, utf8_decode($empresa['nombre']), 0, 1, 'C');
  $pdf->SetX($xIzq + 2);
  facturaPdfLV($pdf, 'Matriz: ', $empresa['direccion_matriz'], 16, $wIzq - 20, 4, 7, 7, 1);
  $pdf->SetX($xIzq + 2);
  if (!empty($empresa['direccion_sucursal'])) {
    facturaPdfLV($pdf, 'Sucursal: ', $empresa['direccion_sucursal'], 16, $wIzq - 20, 4, 7, 7, 1);
    $pdf->SetX($xIzq + 2);
  }
  facturaPdfLV($pdf, 'Teléfono: ', Empresa::getTelefono(), 16, $wIzq - 20, 4, 7, 7, 1);
  $pdf->SetX($xIzq + 2);
  if (!empty($empresa['obligado_contabilidad'])) {
    facturaPdfLV($pdf, 'Obligado a Llevar Contabilidad: ', $empresa['obligado_contabilidad'], $wIzq - 20, 16, 4, 7, 7, 1);
  } else {
    facturaPdfLV($pdf, 'Email: ', Empresa::getEmailClientes(), 16, $wIzq - 20, 4, 7, 7, 1);
  }
  $alturaEmpresaContenido = ($pdf->GetY() + 2) - $yEmpresa;
  $alturaIzq = $logoHeight + 3 + $alturaEmpresaContenido;

  // -- Se igualan las alturas de ambas columnas y se dibujan los bordes --
  $alturaTotal = max($alturaDer, $alturaIzq);
  $alturaEmpresaCaja = $alturaTotal - $logoHeight - 3;

  $pdf->Rect($xIzq, $yTop, $wIzq, $logoHeight);
  $logoPath = '../../assets/image/' . Empresa::getLogoEmpresa();
  if (file_exists($logoPath)) {
    $maxW = $wIzq - 8;
    $maxH = $logoHeight - 6;
    [$imgW, $imgH] = getimagesize($logoPath);
    $ratio = min($maxW / $imgW, $maxH / $imgH);
    $drawW = $imgW * $ratio;
    $drawH = $imgH * $ratio;
    $pdf->Image($logoPath, $xIzq + ($wIzq - $drawW) / 2, $yTop + ($logoHeight - $drawH) / 2, $drawW, $drawH);
  }
  $pdf->Rect($xIzq, $yEmpresa, $wIzq, $alturaEmpresaCaja);
  $pdf->Rect($xDer, $yTop, $wDer, $alturaTotal);

  $pdf->SetY($yTop + $alturaTotal + 3);

  // --- DATOS DEL CLIENTE (recuadro con dos columnas, estilo RIDE) ---
  $yCliente = $pdf->GetY();
  $pdf->SetXY(12, $yCliente + 2);
  facturaPdfLV($pdf, 'Razón Social / Nombre y Apellidos: ', $clienteNombres, 62, 63, 6, 9, 9);
  facturaPdfLV($pdf, 'RUC / CI: ', $clienteDni, 20, 45, 6, 9, 9, 1);

  $pdf->SetX(12);
  facturaPdfLV($pdf, 'Condición de Pago: ', $formaPago, 62, 63, 6, 9, 9);
  facturaPdfLV($pdf, 'Teléfono: ', $clienteTelefono, 20, 45, 6, 9, 9, 1);

  $pdf->SetX(12);
  facturaPdfLV($pdf, 'Fecha de Emisión: ', $facturaFecha, 62, 63, 6, 9, 9);
  facturaPdfLV($pdf, 'Email: ', $clienteEmail, 20, 45, 6, 9, 9, 1);

  $pdf->SetX(12);
  facturaPdfLV($pdf, 'Dirección: ', $clienteDireccion, 25, 160, 6, 9, 9, 1);

  $yClienteEnd = $pdf->GetY() + 2;
  $pdf->SetDrawColor(0, 0, 0);
  $pdf->Rect(10, $yCliente, 190, $yClienteEnd - $yCliente);
  $pdf->Line(135, $yCliente, 135, $yClienteEnd);
  $pdf->SetY($yClienteEnd + 3);

  // --- TABLA DE PRODUCTOS ---
  $pdf->SetFont('Arial', 'B', 10);
  $pdf->Cell(28, 8, 'Cod. Principal', 1, 0, 'C');
  $pdf->Cell(15, 8, 'Cant', 1, 0, 'C');
  $pdf->Cell(75, 8, utf8_decode('Descripción'), 1, 0, 'C');
  $pdf->Cell(25, 8, 'Precio Unitario', 1, 0, 'C');
  $pdf->Cell(22, 8, 'Descuento', 1, 0, 'C');
  $pdf->Cell(25, 8, 'Precio Total', 1, 1, 'C');
  $pdf->SetFont('Arial', '', 10);
  foreach ($detallesFactura as $detalle) {
    $pdf->Cell(28, 7, $detalle['producto_codigo'], 1, 0, 'C');
    $pdf->Cell(15, 7, $detalle['detalle_cantidad'], 1, 0, 'C');
    $x = $pdf->GetX();
    $y = $pdf->GetY();
    $pdf->MultiCell(75, 7, utf8_decode($detalle['producto_nombre']), 1, 'L');
    $pdf->SetXY($x + 75, $y);
    $pdf->Cell(25, 7, '$ ' . number_format($detalle['detalle_precio_unit'], 2), 1, 0, 'R');
    $descuento = $detalle['detalle_descuento'] > 0 ? number_format($detalle['detalle_descuento'], 2) . '%' : '0.00%';
    $pdf->Cell(22, 7, $descuento, 1, 0, 'R');
    $pdf->Cell(25, 7, '$ ' . number_format($detalle['detalle_total'], 2), 1, 1, 'R');
  }

  $pdf->Ln(3);
  $yBloque = $pdf->GetY();

  // --- TOTALES (a la derecha) ---
  if ($tieneFE) {
    $filasTotales = [
      ['SUBTOTAL 15%', $facturaElectronica['fe_subtotal_iva'] ?? $facturaSubTotal],
      ['SUBTOTAL 0%', $facturaElectronica['fe_subtotal_iva0'] ?? 0],
      ['SUBTOTAL SIN IMPUESTOS', $facturaSubTotal],
      ['SUBTOTAL Exento IVA', 0],
      ['DESCUENTO', $facturaDescuentoValor],
      ['ICE', 0],
      ['IVA ' . $facturaImpuesto . '%', $facturaElectronica['fe_iva_valor'] ?? $facturaIva],
      ['PROPINA', $facturaElectronica['fe_propina'] ?? 0],
      ['VALOR TOTAL', $facturaElectronica['fe_total_comprobante'] ?? $facturaTotal],
    ];
  } else {
    $descuentoPorcentajeStr = rtrim(rtrim(number_format($facturaDescuentoPorcentaje, 2), '0'), '.');
    if ($descuentoPorcentajeStr === '') $descuentoPorcentajeStr = '0';
    $filasTotales = [
      ['SUBTOTAL SIN IMPUESTOS', $facturaSubTotal],
      ['SUBTOTAL Exento IVA', 0],
      ['DESCUENTO (' . $descuentoPorcentajeStr . '%)', $facturaDescuentoValor],
      ['ICE', 0],
      ['IVA ' . $facturaImpuesto . '%', $facturaIva],
      ['VALOR TOTAL', $facturaTotal],
    ];
  }

  $anchoCol1 = 60;
  $anchoCol2 = 30;
  $anchoTotales = $anchoCol1 + $anchoCol2;
  $xTotales = 210 - 10 - $anchoTotales;
  $altoTotales = count($filasTotales) * 7;

  $pdf->SetXY($xTotales, $yBloque);
  $pdf->SetFont('Arial', 'B', 10);
  foreach ($filasTotales as $fila) {
    $pdf->SetX($xTotales);
    $esTotal = $fila[0] === 'VALOR TOTAL';
    $pdf->SetFont('Arial', 'B', $esTotal ? 11 : 9);
    $pdf->Cell($anchoCol1, 7, utf8_decode($fila[0]), 1, 0, 'L');
    $pdf->Cell($anchoCol2, 7, '$ ' . number_format($fila[1], 2), 1, 1, 'R');
  }

  // --- INFORMACIÓN ADICIONAL (a la izquierda, misma altura que los totales) ---
  $anchoIzq = $xTotales - 14;
  $pdf->Rect(10, $yBloque, $anchoIzq, $altoTotales);
  $pdf->SetFillColor(230, 230, 230);
  $pdf->SetXY(10, $yBloque);
  $pdf->SetFont('Arial', 'B', 9);
  $pdf->Cell($anchoIzq, 6, utf8_decode('INFORMACIÓN ADICIONAL'), 1, 1, 'C', true);
  $pdf->SetXY(12, $yBloque + 8);
  facturaPdfLV($pdf, 'Email Cliente: ', $clienteEmail, 28, $anchoIzq - 30, 5, 8, 8, 1);

  // --- FORMA DE PAGO (debajo de información adicional) ---
  $yFormaPago = $yBloque + $altoTotales + 4;
  $pdf->SetXY(10, $yFormaPago);
  $pdf->SetFont('Arial', 'B', 9);
  $pdf->Cell(65, 7, utf8_decode('Forma de Pago'), 1, 0, 'C', true);
  $pdf->Cell(30, 7, 'Total', 1, 1, 'C', true);
  $pdf->SetX(10);
  $pdf->SetFont('Arial', '', 9);
  $pdf->Cell(65, 7, utf8_decode($formaPago), 1, 0, 'L');
  $pdf->Cell(30, 7, '$ ' . number_format($facturaTotal, 2), 1, 1, 'R');

  $pdf->SetY($yFormaPago + 14 + 10);

  // --- TÉRMINOS Y CONDICIONES ---
  $pdf->SetFont('Arial', 'B', 10);
  $pdf->Cell(0, 6, utf8_decode("TÉRMINOS Y CONDICIONES"), 0, 1, 'C');
  $pdf->SetFont('Arial', '', 10);
  $pdf->MultiCell(0, 6, utf8_decode("El cliente se compromete a pagar la factura en su totalidad en la fecha establecida.\nCopyright@: ") . utf8_decode($empresa['nombre']), 0, 'C');

  return $pdf;
}
