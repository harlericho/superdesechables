<?php

require_once __DIR__ . '/../assets/fpdf/fpdf.php';
require_once __DIR__ . '/../config/empresa.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/facturacionElectronicaModel.php';
require_once __DIR__ . '/facturacion_electronica_helper.php';

// FPDF no expone el número de líneas que ocupará un MultiCell. Se necesita para
// que las celdas vecinas (código, cantidad, precios) tengan la misma altura que
// la descripción cuando esta se envuelve en 2+ líneas; si no, la siguiente fila
// de la tabla se dibuja encima del texto todavía visible de la fila anterior.
class FacturaPdfDocument extends FPDF
{
  function NbLines($w, $txt)
  {
    $cw = $this->CurrentFont['cw'];
    if ($w == 0) {
      $w = $this->w - $this->rMargin - $this->x;
    }
    $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
    $s = str_replace("\r", '', $txt);
    $nb = strlen($s);
    if ($nb > 0 && $s[$nb - 1] == "\n") {
      $nb--;
    }
    $sep = -1;
    $i = 0;
    $j = 0;
    $l = 0;
    $nl = 1;
    while ($i < $nb) {
      $c = $s[$i];
      if ($c == "\n") {
        $i++;
        $sep = -1;
        $j = $i;
        $l = 0;
        $nl++;
        continue;
      }
      if ($c == ' ') {
        $sep = $i;
      }
      $l += $cw[$c] ?? 0;
      if ($l > $wmax) {
        if ($sep == -1) {
          if ($i == $j) {
            $i++;
          }
        } else {
          $i = $sep + 1;
        }
        $sep = -1;
        $j = $i;
        $l = 0;
        $nl++;
      } else {
        $i++;
      }
    }
    return $nl;
  }
}

// Reduce el tamaño de fuente hasta que el texto quepa en el ancho dado, para que
// valores largos (emails, direcciones) no se salgan de su recuadro en el PDF.
function facturaPdfFitFontSize($pdf, $texto, $maxWidth, $sizeInicial, $sizeMinimo = 5)
{
  $size = $sizeInicial;
  $pdf->SetFont('Arial', '', $size);
  while ($size > $sizeMinimo && $pdf->GetStringWidth($texto) > $maxWidth) {
    $size -= 0.5;
    $pdf->SetFont('Arial', '', $size);
  }
  return $size;
}

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
      'contribuyente_rimpe' => $config['config_fe_contribuyente_rimpe'] ?? 'NO',
    ];
  }
  return [
    'nombre' => Empresa::getNombre(),
    'ruc' => Empresa::getRuc(),
    'direccion_matriz' => Empresa::getDireccion(),
    'direccion_sucursal' => null,
    'obligado_contabilidad' => null,
    'ambiente' => null,
    'contribuyente_rimpe' => 'NO',
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
  $texto = utf8_decode((string)($valor ?? ''));
  facturaPdfFitFontSize($pdf, $texto, $wValor - 1, $sizeValor, 5);
  $pdf->Cell($wValor, $h, $texto, 0, $ln, $alignValor);
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

  $pdf = new FacturaPdfDocument('P', 'mm', 'A4');
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
  $tituloRecibo = (strpos($facturaComprobante, 'TK') !== false) ? 'TICKET / NOTA DE VENTA' : 'FACTURA';
  $pdf->Cell($wDer, 7, $tituloRecibo, 0, 2, 'C');

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
    // Dar un poco de margen para que se vea más centrado y estético
    $narrow = 0.26; 
    $barcodeWidth = 332 * $narrow; // ~86.32 mm
    $offsetX = ($wDer - $barcodeWidth) / 2;
    
    facturaPdfCode128($pdf, $xDer + $offsetX, $pdf->GetY() + 1, $claveAcceso, $narrow, 12);
    $pdf->SetXY($xDer, $pdf->GetY() + 14);
    $pdf->SetFont('Arial', '', 7);
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
  $textoRimpe = FacturacionElectronicaHelper::textoLeyendaRimpe($empresa['contribuyente_rimpe'] ?? 'NO');
  if ($textoRimpe !== null) {
    $pdf->SetX($xIzq + 2);
    $pdf->SetFont('Arial', 'B', 6.5);
    $pdf->Cell($wIzq - 4, 4, utf8_decode($textoRimpe), 0, 1, 'C');
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
  facturaPdfLV($pdf, 'Fecha de Emisión: ', $facturaFecha, 62, 123, 6, 9, 9, 1);

  $pdf->SetX(12);
  facturaPdfLV($pdf, 'Dirección: ', $clienteDireccion, 25, 160, 6, 9, 9, 1);

  $pdf->SetX(12);
  facturaPdfLV($pdf, 'Email: ', $clienteEmail, 25, 160, 6, 9, 9, 1);

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
  $pdf->SetFont('Arial', '', 10);

  foreach ($detallesFactura as $detalle) {
    $nombreTexto = utf8_decode($detalle['producto_nombre']);
    
    // La descripción puede envolverse en varias líneas: se calcula cuántas para que
    // el resto de celdas de la fila (código, cantidad, precios) tengan esa misma
    // altura y no queden más cortas que la descripción (lo que provocaba que la
    // siguiente fila se dibujara encima del texto todavía visible de esta).
    $numLineas = max(1, $pdf->NbLines(75, $nombreTexto));
    $alturaFila = $numLineas * 7;

    if ($pdf->GetY() + $alturaFila > 280) {
      $pdf->AddPage();
    }

    $xFila = $pdf->GetX();
    $yFila = $pdf->GetY();

    $pdf->Cell(28, $alturaFila, $detalle['producto_codigo'], 1, 0, 'C');
    $pdf->Cell(15, $alturaFila, $detalle['detalle_cantidad'], 1, 0, 'C');

    $xDesc = $pdf->GetX();
    $pdf->MultiCell(75, 7, $nombreTexto, 1, 'L');
    $pdf->SetXY($xDesc + 75, $yFila);

    $precioUnitPdf = floatval($detalle['detalle_precio_unit']);
    $precioNetoProducto = floatval($detalle['detalle_total']);
    $descuentoMostrado = $detalle['detalle_descuento'] > 0 ? number_format($detalle['detalle_descuento'], 2) . '%' : '0.00%';

    $pdf->Cell(25, $alturaFila, '$ ' . number_format($precioUnitPdf, 2), 1, 0, 'R');
    $pdf->Cell(22, $alturaFila, $descuentoMostrado, 1, 0, 'R');
    $pdf->Cell(25, $alturaFila, '$ ' . number_format($precioNetoProducto, 2), 1, 0, 'R');

    $pdf->SetXY($xFila, $yFila + $alturaFila);
  }

  $pdf->Ln(3);

  // --- TOTALES (a la derecha) ---
  if ($tieneFE) {
    $filasTotales = [
      ['SUBTOTAL 15%', $facturaElectronica['fe_subtotal_iva'] ?? $facturaSubTotal],
      ['SUBTOTAL 0%', $facturaElectronica['fe_subtotal_iva0'] ?? 0],
      ['SUBTOTAL SIN IMPUESTOS', $facturaElectronica['fe_subtotal_sin_impuestos'] ?? $facturaSubTotal],
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

  if ($pdf->GetY() + $altoTotales + 45 > 285) {
    $pdf->AddPage();
  }
  $yBloque = $pdf->GetY();

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

// Generador compacto de Code 128 optimizado para claves de acceso SRI (49 dígitos)
function facturaPdfCode128($pdf, $x, $y, $code, $narrow = 0.25, $height = 10)
{
  $T128 = [
    "212222", "222122", "222221", "121223", "121322", "131222", "122213", "122312", "132212", "221213", 
    "221312", "231212", "112232", "122132", "122231", "113222", "123122", "123221", "223211", "221132", 
    "221231", "213212", "223112", "312131", "311222", "321122", "321221", "312212", "322112", "322211", 
    "212123", "212321", "232121", "111323", "131123", "131321", "112313", "132113", "132311", "211313", 
    "231113", "231311", "112133", "112331", "132131", "113123", "113321", "133121", "313121", "211331", 
    "231131", "213113", "213311", "213131", "311123", "311321", "331121", "312113", "312311", "332111", 
    "314111", "221411", "431111", "111224", "111422", "121124", "121421", "141122", "141221", "112214", 
    "112412", "122114", "122411", "142112", "142211", "241211", "221114", "413111", "241112", "134111", 
    "111242", "121142", "121241", "114212", "124112", "124211", "411212", "421112", "421211", "212141", 
    "214121", "412121", "111143", "111341", "131141", "114113", "114311", "411113", "411311", "113141", 
    "114131", "311141", "411131", "211412", "211214", "211232", "2331112"
  ];
  
  $indices = [104]; // START B
  if (strlen($code) == 49) {
    $indices[] = intval($code[0]) + 16; // Primer dígito en Code B
    $indices[] = 99; // Cambiar a Code C
    for ($i = 1; $i < 49; $i += 2) {
      $indices[] = intval(substr($code, $i, 2));
    }
  } else {
    // Fallback: Todo en Code B (Menos eficiente pero seguro)
    for ($i = 0; $i < strlen($code); $i++) {
      $indices[] = ord($code[$i]) - 32;
    }
  }
  
  $checksum = $indices[0];
  for ($i = 1; $i < count($indices); $i++) {
    $checksum += $indices[$i] * $i;
  }
  $indices[] = $checksum % 103;
  $indices[] = 106; // STOP
  
  $pdf->SetFillColor(0, 0, 0);
  foreach ($indices as $idx) {
    $pattern = $T128[$idx];
    for ($j = 0; $j < strlen($pattern); $j++) {
      $wBar = intval($pattern[$j]) * $narrow;
      if ($j % 2 == 0) {
        $pdf->Rect($x, $y, $wBar, $height, 'F');
      }
      $x += $wBar;
    }
  }
}
