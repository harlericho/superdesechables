<?php
// controllers/factura/facturaReenviarCorreoController.php
header('Content-Type: application/json');

require_once '../../models/detalleModel.php';
require_once '../../assets/fpdf/fpdf.php';
require_once '../../config/empresa.php';
require_once '../../helpers/mailer_helper.php';

$factura_id = $_POST['factura_id'] ?? null;
$correo = $_POST['correo'] ?? null;

if (!$factura_id || !$correo) {
  echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
  exit;
}

$detallesFactura = DetalleModel::obtenerDetalleFacturaPdf($factura_id);
if (empty($detallesFactura)) {
  echo json_encode(['success' => false, 'message' => 'No se encontraron detalles para la factura.']);
  exit;
}

$primerDetalle = $detallesFactura[0];
$clienteNombres = $primerDetalle['cliente_nombres'] . " " . $primerDetalle['cliente_apellidos'];
$clienteDni = $primerDetalle['cliente_dni'];
$clienteDireccion = $primerDetalle['cliente_direccion'];
$clienteTelefono = $primerDetalle['cliente_telefono'];
$facturaComprobante = $primerDetalle['factura_num_comprobante'];
$facturaFecha = $primerDetalle['factura_fecha'];
$usuarioNombres = $primerDetalle['usuario_nombres'];
$usuarioEmail = $primerDetalle['usuario_email'];
$facturaSubTotal = $primerDetalle['factura_subtotal'];
$facturaImpuesto = $primerDetalle['factura_impuesto'];
$facturaTotal = $primerDetalle['factura_total'];

$pdf = new FPDF('P', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 14);
$pdf->Image('../../assets/image/pdflogo.png', 10, 10, 40);
$pdf->SetXY(60, 10);
$pdf->Cell(90, 10, Empresa::getNombre(), 0, 1, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->SetX(60);
$pdf->Cell(90, 5, "Calle 1, Ciudad", 0, 1, 'L');
$pdf->SetX(60);
$pdf->Cell(90, 5, "Tel: 0111111110", 0, 1, 'L');
$pdf->SetX(60);
$pdf->Cell(90, 5, Empresa::getEmail(), 0, 1, 'L');
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetXY(150, 10);
$pdf->Cell(50, 10, "Factura: #$facturaComprobante", 0, 1, 'R');
$pdf->SetFont('Arial', '', 10);
$pdf->SetXY(150, 20);
$pdf->Cell(50, 5, "Fecha: $facturaFecha", 0, 1, 'R');
$pdf->SetXY(150, 25);
$pdf->Cell(50, 5, "Vencimiento: 30 dias", 0, 1, 'R');
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(100, 6, "Cliente:", 0, 1);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(100, 6, $clienteNombres, 0, 1);
$pdf->Cell(100, 6, "DNI: $clienteDni", 0, 1);
$pdf->Cell(100, 6, "Tel: $clienteTelefono", 0, 1);
$pdf->Cell(100, 6, "Direccion: $clienteDireccion", 0, 1);
$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(20, 7, "Cod.", 1, 0, 'C');
$pdf->Cell(60, 7, utf8_decode("Descripción"), 1, 0, 'C');
$pdf->Cell(20, 7, "Cantidad", 1, 0, 'C');
$pdf->Cell(25, 7, "Precio Unit.", 1, 0, 'C');
$pdf->Cell(25, 7, "Descuento %", 1, 0, 'C');
$pdf->Cell(40, 7, "Total", 1, 1, 'C');
$pdf->SetFont('Arial', '', 10);
foreach ($detallesFactura as $detalle) {
  $pdf->Cell(20, 6, $detalle['producto_codigo'], 1, 0, 'C');
  $pdf->Cell(60, 6, utf8_decode($detalle['producto_nombre']), 1, 0, 'L');
  $pdf->Cell(20, 6, $detalle['detalle_cantidad'], 1, 0, 'C');
  $pdf->Cell(25, 6, "$ " . number_format($detalle['detalle_precio_unit'], 2), 1, 0, 'R');
  $pdf->Cell(25, 6, number_format($detalle['detalle_descuento'], 2) . "%", 1, 0, 'R');
  $pdf->Cell(40, 6, "$ " . number_format($detalle['detalle_total'], 2), 1, 1, 'R');
}
$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetX(115);
$pdf->Cell(50, 6, "Subtotal", 1);
$pdf->Cell(35, 6, "$ " . number_format($facturaSubTotal, 2), 1, 1, 'R');
$pdf->SetX(115);
$pdf->Cell(50, 6, "Descuento", 1);
$pdf->Cell(35, 6, "$ 0.00", 1, 1, 'R');
$pdf->SetX(115);
$pdf->Cell(50, 6, "Impuesto", 1);
$pdf->Cell(35, 6, "$ " . number_format($facturaImpuesto, 2), 1, 1, 'R');
$pdf->SetX(115);
$pdf->Cell(50, 6, "Total", 1);
$pdf->Cell(35, 6, "$ " . number_format($facturaTotal, 2), 1, 1, 'R');
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 6, "TERMINOS Y CONDICIONES", 0, 1, 'C');
$pdf->SetFont('Arial', '', 10);
$pdf->MultiCell(0, 6, "El cliente se compromete a pagar la factura en su totalidad en la fecha establecida.\nCopyright@:" . Empresa::getNombre(), 0, 'C');

$nombreArchivo = preg_replace('/[^A-Za-z0-9\-]/', '', $facturaComprobante) . '.pdf';
$pdfString = $pdf->Output('S', $nombreArchivo);
$enviado = enviarFacturaPorCorreo($correo, $pdfString, $nombreArchivo);

if ($enviado) {
  echo json_encode(['success' => true]);
} else {
  echo json_encode(['success' => false, 'message' => 'No se pudo enviar el correo.']);
}
