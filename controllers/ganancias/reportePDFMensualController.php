<?php
require_once '../../assets/fpdf/fpdf.php';
require_once '../../config/empresa.php';
// Configurar zona horaria de Ecuador
date_default_timezone_set('America/Guayaquil');
$pdf = new FPDF();
$pdf->AddPage();

// Logo
$logo = Empresa::getLogoEmpresa();
if ($logo && file_exists('../../assets/image/' . $logo)) {
  $pdf->Image('../../assets/image/' . $logo, 10, 10, 50);
}

// Datos

$pdf->SetFont('Arial', 'B', 16);
$pdf->SetXY(65, 18); // Más a la derecha y más abajo para alinear con el logo grande
$pdf->Cell(0, 10, utf8_decode(Empresa::getNombre()), 0, 1);
$pdf->SetFont('Arial', '', 12);
$pdf->SetXY(65, 28);
$pdf->Cell(0, 8, utf8_decode('Dirección: ' . Empresa::getDireccion()), 0, 1);
$pdf->SetX(65);
$pdf->Cell(0, 8, utf8_decode('Teléfono: ' . Empresa::getTelefono()), 0, 1);
$pdf->SetX(65);
$pdf->Cell(0, 8, utf8_decode('RUC: ' . Empresa::getRuc()), 0, 1);
$pdf->SetX(65);
$pdf->Cell(0, 8, utf8_decode('Email: ' . Empresa::getEmail()), 0, 1);

$pdf->Ln(10);
$pdf->SetDrawColor(100, 200, 255);
$pdf->SetLineWidth(1);
$pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, utf8_decode('Reporte Mensual de la empresa ' . Empresa::getNombre()), 0, 1);

// Obtener año
$anio = isset($_GET['anio']) ? intval($_GET['anio']) : date('Y');
// Obtener datos del reporte mensual
require_once '../../models/gananciasModel.php';
$reporte = GananciasModel::getReporteMensual($anio); // Debes tener este método en tu modelo


$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, utf8_decode('Reporte Mensual - Año: ' . $anio), 0, 1);

if (empty($reporte)) {
  $pdf->SetFont('Arial', 'I', 12);
  $pdf->Cell(0, 12, utf8_decode('No hay registros para este año.'), 0, 1, 'C');
} else {
  // Ordenar por mes
  usort($reporte, function ($a, $b) {
    $meses = ['Enero' => 1, 'Febrero' => 2, 'Marzo' => 3, 'Abril' => 4, 'Mayo' => 5, 'Junio' => 6, 'Julio' => 7, 'Agosto' => 8, 'Septiembre' => 9, 'Octubre' => 10, 'Noviembre' => 11, 'Diciembre' => 12];
    return ($meses[$a['mes']] ?? 99) - ($meses[$b['mes']] ?? 99);
  });
  // Encabezados de tabla
  $pdf->SetFont('Arial', 'B', 10);
  $pdf->SetFillColor(220, 240, 255);
  $pdf->Cell(25, 8, utf8_decode('Mes'), 1, 0, 'C', true);
  $pdf->Cell(20, 8, utf8_decode('Ventas'), 1, 0, 'C', true);
  $pdf->Cell(30, 8, utf8_decode('Cantidad'), 1, 0, 'C', true);
  $pdf->Cell(25, 8, utf8_decode('Ingresos'), 1, 0, 'C', true);
  $pdf->Cell(25, 8, utf8_decode('Descuentos'), 1, 0, 'C', true);
  $pdf->Cell(25, 8, utf8_decode('Costos'), 1, 0, 'C', true);
  $pdf->Cell(30, 8, utf8_decode('Ganancia Neta'), 1, 1, 'C', true);

  $pdf->SetFont('Arial', '', 10);
  foreach ($reporte as $item) {
    $pdf->Cell(25, 8, utf8_decode($item['mes']), 1, 0, 'C');
    $pdf->Cell(20, 8, $item['ventas'], 1, 0, 'C');
    $pdf->Cell(30, 8, $item['cantidad_vendida'], 1, 0, 'C');
    $pdf->Cell(25, 8, $item['ingresos'], 1, 0, 'C');
    $pdf->Cell(25, 8, $item['descuentos'], 1, 0, 'C');
    $pdf->Cell(25, 8, $item['costos'], 1, 0, 'C');
    $pdf->Cell(30, 8, $item['ganancia_neta'], 1, 1, 'C');
  }
}

// agregar el nombre del archivo como reporte del ano y la fecha de generacion
$nombreArchivo = 'reporte_mensual_' . date($anio) . '.pdf';
$pdf->Output('D', $nombreArchivo);
