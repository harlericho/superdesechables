<?php
ob_start(); // Inicia el buffer de salida para evitar interferencias con el PDF.

require_once '../../assets/fpdf/fpdf.php';
require_once '../../models/detalleModel.php';
require_once '../../config/empresa.php';
require_once '../../helpers/mailer_helper.php';
session_start();

if (!isset($_SESSION['email'])) {
  header('Location: ../../../../index.html');
  exit();
}

$factura_id = $_GET['factura_id'];
$email = $_SESSION['email'];

// Obtiene los datos de la factura
$detallesFactura = DetalleModel::obtenerDetalleFacturaPdf($factura_id);
if (empty($detallesFactura)) {
  die("Error: No se encontraron detalles para la factura.");
}

$primerDetalle = $detallesFactura[0];
$clienteNombres = $primerDetalle['cliente_nombres'] . " " . $primerDetalle['cliente_apellidos'];
$clienteDni = $primerDetalle['cliente_dni'];
$clienteEmail = $primerDetalle['cliente_email'];
$clienteDireccion = $primerDetalle['cliente_direccion'];
$clienteTelefono = $primerDetalle['cliente_telefono'];
$facturaComprobante = $primerDetalle['factura_num_comprobante'];
$facturaFecha = $primerDetalle['factura_fecha'];
$usuarioNombres = $primerDetalle['usuario_nombres'];
$usuarioEmail = $primerDetalle['usuario_email'];
$facturaSubTotal = $primerDetalle['factura_subtotal'];
$facturaImpuesto = $primerDetalle['factura_impuesto'];
$facturaTotal = $primerDetalle['factura_total'];

// Inicia el PDF
$pdf = new FPDF('P', 'mm', 'A4');
$pdf->AddPage();

// Logo y Nombre de Empresa - POSICIÓN CORREGIDA
$pdf->Image('../../assets/image/' . Empresa::getLogoEmpresa(), 10, 10, 45); // Logo tamaño correcto
$pdf->SetFont('Arial', 'B', 13);
$pdf->SetXY(75, 10);
$pdf->SetXY(75, 10);
$pdf->Cell(125, 7, utf8_decode(Empresa::getNombre()), 0, 1, 'R');
$pdf->SetFont('Arial', '', 10);
$pdf->SetXY(75, 17);
$pdf->Cell(125, 5, 'R.U.C.:' . Empresa::getRuc(), 0, 1, 'R');

$pdf->SetXY(75, 22);
$pdf->Cell(125, 5, 'FACTURA', 0, 1, 'R');

$pdf->SetXY(75, 27);
$pdf->Cell(125, 5, 'No.' . str_pad($facturaComprobante, 9, '0', STR_PAD_LEFT), 0, 1, 'R');
$pdf->SetXY(75, 34);
$pdf->Cell(125, 5, utf8_decode(Empresa::getDireccion()), 0, 1, 'R');
$pdf->SetXY(75, 39);
$pdf->Cell(125, 5, 'Tel: ' . Empresa::getTelefono(), 0, 1, 'R');
$pdf->SetXY(75, 44);
$pdf->Cell(125, 5, utf8_decode(Empresa::getEmailClientes()), 0, 1, 'R');

// --- BLOQUE DE DATOS DEL CLIENTE ESTILO FACTURA ELECTRÓNICA ---
$pdf->Ln(8);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(65, 7, utf8_decode('Razón Social / Nombre y Apellidos:'), 1, 0, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(65, 7, utf8_decode($clienteNombres), 1, 0, 'L');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(20, 7, utf8_decode('RUC / CI:'), 1, 0, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(40, 7, $clienteDni, 1, 1, 'L');

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(65, 7, utf8_decode('Condición de Pago:'), 1, 0, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(65, 7, utf8_decode($primerDetalle['tipo_comp_descripcion']), 1, 0, 'L');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(20, 7, utf8_decode('Teléfono:'), 1, 0, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(40, 7, $clienteTelefono, 1, 1, 'L');

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(65, 7, utf8_decode('Fecha de Emisión:'), 1, 0, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(65, 7, $facturaFecha, 1, 0, 'L');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(20, 7, utf8_decode('Email:'), 1, 0, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(40, 7, $clienteEmail, 1, 1, 'L');

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(65, 7, utf8_decode('Dirección:'), 1, 0, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(190 - 65, 7, utf8_decode($clienteDireccion), 1, 1, 'L');

// --- TABLA DE PRODUCTOS MEJORADA ---

$pdf->Ln(5);
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
  $pdf->Cell(22, 7, '$ ' . number_format($detalle['detalle_descuento'], 2), 1, 0, 'R');
  $pdf->Cell(25, 7, '$ ' . number_format($detalle['detalle_total'], 2), 1, 1, 'R');
}



// --- INFORMACIÓN ADICIONAL Y FORMA DE PAGO ---
$pdf->Ln(3);

// --- ALINEAR TOTALES A LA ALTURA DE LA TABLA DE PRODUCTOS ---
$yTotales = $pdf->GetY();
if ($yTotales < 110) {
  $yTotales = 110;
} // Altura mínima para evitar superposición
$pdf->SetY($yTotales);

// Totales

// --- NUEVO BLOQUE DE TOTALES ESTILO FACTURA ELECTRÓNICA ---
$anchoCol1 = 55;
$anchoCol2 = 35;
$anchoTablaTotales = $anchoCol1 + $anchoCol2;
$margenDerecho = 10; // margen derecho de la página
$xTotales = 210 - $margenDerecho - $anchoTablaTotales; // 210 es el ancho A4 en mm
$yTotales = $pdf->GetY();
$pdf->SetXY($xTotales, $yTotales);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell($anchoCol1, 7, 'SUBTOTAL', 1, 0, 'L');
$pdf->Cell($anchoCol2, 7, '$ ' . number_format($facturaSubTotal, 2), 1, 1, 'R');
$pdf->SetX($xTotales);
$pdf->Cell($anchoCol1, 7, 'DESCUENTO', 1, 0, 'L');
$pdf->Cell($anchoCol2, 7, '$ 0.00', 1, 1, 'R');
$pdf->SetX($xTotales);
$pdf->Cell($anchoCol1, 7, 'IVA 15%', 1, 0, 'L');
$pdf->Cell($anchoCol2, 7, '$ ' . number_format($facturaSubTotal * $facturaImpuesto / 100, 2), 1, 1, 'R');
$pdf->SetX($xTotales);
$pdf->Cell($anchoCol1, 7, 'VALOR TOTAL', 1, 0, 'L');
$pdf->Cell($anchoCol2, 7, '$ ' . number_format($facturaTotal, 2), 1, 1, 'R');

// Términos y condiciones
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 6, "TERMINOS Y CONDICIONES", 0, 1, 'C');
$pdf->SetFont('Arial', '', 10);
$pdf->MultiCell(0, 6, "El cliente se compromete a pagar la factura en su totalidad en la fecha establecida.\nCopyright@:" . Empresa::getNombre(), 0, 'C');


// Guardar el PDF en un string y enviar con nombre de la serie/número
$nombreArchivo = preg_replace('/[^A-Za-z0-9\-]/', '', $facturaComprobante) . '.pdf';
$pdfString = $pdf->Output('S', $nombreArchivo);
ob_end_clean(); // Limpia el buffer de salida

// Solo enviar el PDF al correo si se recibe ?enviar=1
if (isset($_GET['enviar']) && $_GET['enviar'] == '1' && !empty($clienteEmail)) {
  enviarFacturaPorCorreo($clienteEmail, $pdfString, $nombreArchivo);
}


$pdf->Output('I', 'factura.pdf');
