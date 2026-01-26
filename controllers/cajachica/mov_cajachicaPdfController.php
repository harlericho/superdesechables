<?php
require_once '../../assets/fpdf/fpdf.php';
require_once '../../models/cajachicaModel.php';
require_once '../../config/empresa.php';

session_start();
ob_start();

class PDF extends FPDF
{
  function Header()
  {
    $this->Image('../../assets/image/pdflogo.png', 10, 8, 50);
    $this->Ln(15);

    $this->SetFont('Arial', 'B', 16);
    $this->SetTextColor(0, 96, 100);
    $this->Cell(60);
    $this->Cell(70, 10, Empresa::getNombre(), 0, 1, 'C');

    $this->SetFont('Arial', 'B', 12);
    $this->Cell(60);
    $this->Cell(70, 10, 'Reporte de Movimientos Caja Chica', 0, 1, 'C');

    $this->SetFont('Arial', '', 10);
    $this->Cell(60);
    setlocale(LC_TIME, 'es_ES.UTF-8', 'Spanish_Spain', 'es_ES');
    date_default_timezone_set('America/Bogota');
    $this->Cell(70, 10, 'Fecha: ' . date('Y-m-d H:i:s'), 0, 1, 'C');

    $this->Ln(10);
    $this->SetFont('Arial', 'B', 10);
    $this->SetFillColor(0, 96, 100);
    $this->SetTextColor(255);
    $this->Cell(50, 10, 'Fecha', 1, 0, 'C', true);
    $this->Cell(80, 10, 'Descripcion', 1, 0, 'C', true);
    $this->Cell(30, 10, 'Tipo', 1, 0, 'C', true);
    $this->Cell(30, 10, 'Monto', 1, 1, 'C', true);
  }

  function Footer()
  {
    $this->SetY(-15);
    $this->SetFont('Arial', 'I', 10);
    $this->Cell(0, 10, 'Pagina ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
  }
}

require_once '../../models/usuarioModel.php';
$usuario = UsuarioModel::obtenerDatoUsuario($_SESSION['email']);
$rolDescripcion = $usuario[0]['rol_descripcion'] ?? '';

if ($rolDescripcion === 'ADMINISTRADOR') {
  $pdf = new PDF();
  $pdf->AliasNbPages();
  $pdf->AddPage();
  $pdf->SetFont('Arial', '', 10);
  $pdf->SetFillColor(220, 240, 240);
  $fill = false;

  $total = 0;
  foreach (CajachicaModel::listarMovimientoCajachica() as $mov) {
    // Fecha y descripción en negro
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(50, 10, utf8_decode($mov['mov_fecharegistro']), 1, 0, 'C', $fill);
    $pdf->Cell(80, 10, utf8_decode($mov['mov_descripcion']), 1, 0, 'L', $fill);

    // Tipo y monto con color y negrilla según ingreso/egreso
    if ($mov['mov_tipo'] === 'INGRESO') {
      $pdf->SetTextColor(0, 128, 0); // Verde
      $pdf->SetFont('Arial', 'B', 8);
      $pdf->Cell(30, 10, utf8_decode($mov['mov_tipo']), 1, 0, 'C', $fill);
      $pdf->Cell(30, 10, number_format($mov['mov_monto'], 2), 1, 1, 'R', $fill);
      $total += floatval($mov['mov_monto']);
    } else if ($mov['mov_tipo'] === 'EGRESO') {
      $pdf->SetTextColor(220, 20, 60); // Rojo
      $pdf->SetFont('Arial', '', 8);
      $pdf->Cell(30, 10, utf8_decode($mov['mov_tipo']), 1, 0, 'C', $fill);
      $pdf->Cell(30, 10, number_format($mov['mov_monto'], 2), 1, 1, 'R', $fill);
      $total -= floatval($mov['mov_monto']);
    } else {
      $pdf->SetTextColor(0, 0, 0); // Negro
      $pdf->SetFont('Arial', '', 8);
      $pdf->Cell(30, 10, utf8_decode($mov['mov_tipo']), 1, 0, 'C', $fill);
      $pdf->Cell(30, 10, number_format($mov['mov_monto'], 2), 1, 1, 'R', $fill);
    }
    $fill = !$fill;
  }

  // Mostrar total al final en verde si es positivo, rojo si es negativo
  if ($total >= 0) {
    $pdf->SetTextColor(0, 128, 0); // Verde
  } else {
    $pdf->SetTextColor(220, 20, 60); // Rojo
  }
  $pdf->SetFont('Arial', 'B', 12);
  $pdf->Cell(160, 12, 'TOTAL', 1, 0, 'R', true);
  $pdf->Cell(30, 12, number_format($total, 2), 1, 1, 'R', true);

  ob_end_clean();
  $pdf->Output('I', 'reporte_movimientos_cajachica_' . date('Y-m-d_H-i-s') . '.pdf');
} else {
  ob_end_clean();
  header('Location: ../../../../views/500.php');
  exit;
}
