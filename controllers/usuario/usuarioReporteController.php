<?php
require_once '../../assets/fpdf/fpdf.php';
require_once '../../models/usuarioModel.php';
require_once '../../config/empresa.php';

session_start();
ob_start(); // Inicia el buffer de salida

class PDF extends FPDF
{
  // Cabecera de página
  function Header()
  {
    // Logo
    $this->Image('../../assets/image/pdflogo.png', 10, 8, 50);
    $this->Ln(15);

    // Fuente y color del título
    $this->SetFont('Arial', 'B', 16);
    $this->SetTextColor(0, 96, 100);
    $this->Cell(60);
    $this->Cell(70, 10, Empresa::getNombre(), 0, 1, 'C');

    // Subtítulo
    $this->SetFont('Arial', 'B', 12);
    $this->Cell(60);
    $this->Cell(70, 10, 'Reporte de Usuarios', 0, 1, 'C');

    // Fecha de generación
    $this->SetFont('Arial', '', 10);
    $this->Cell(60);
    setlocale(LC_TIME, 'es_ES.UTF-8', 'Spanish_Spain', 'es_ES');
    date_default_timezone_set('America/Bogota'); // Ajusta según tu país

    $this->Cell(70, 10, 'Fecha: ' . date('Y-m-d H:i:s'), 0, 1, 'C');

    // Encabezados de tabla
    $this->Ln(10);
    $this->SetFont('Arial', 'B', 10);
    $this->SetFillColor(0, 96, 100);
    $this->SetTextColor(255);
    $this->Cell(50, 10, 'Nombres', 1, 0, 'C', true);
    $this->Cell(55, 10, 'Email', 1, 0, 'C', true);
    $this->Cell(50, 10, 'Password', 1, 0, 'C', true);
    $this->Cell(40, 10, 'Rol', 1, 1, 'C', true);
  }

  // Pie de página
  function Footer()
  {
    $this->SetY(-15);
    $this->SetFont('Arial', 'I', 10);
    $this->Cell(0, 10, 'Pagina ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
  }
}

// Obtener rol del usuario autenticado
$usuario = UsuarioModel::obtenerDatoUsuario($_SESSION['email']);
$rolDescripcion = $usuario[0]['rol_descripcion'] ?? '';

if ($rolDescripcion === 'ADMINISTRADOR') {
  $pdf = new PDF();
  $pdf->AliasNbPages();
  $pdf->AddPage();
  $pdf->SetFont('Arial', '', 10);

  // Color de fondo para filas
  $pdf->SetFillColor(220, 240, 240); // Color basado en el color de la empresa
  $pdf->SetTextColor(0);
  $fill = false; // Alternar colores

  foreach (UsuarioModel::obtenerUsuarios() as $value) {
    $pdf->SetFont('Arial', '', 7);
    $pdf->Cell(50, 10, utf8_decode($value['nombres']), 1, 0, 'C', $fill);
    $pdf->Cell(55, 10, utf8_decode($value['email']), 1, 0, 'C', $fill);
    $pdf->Cell(50, 10, utf8_decode($value['password']), 1, 0, 'C', $fill); // Ocultando la contraseña
    $pdf->Cell(40, 10, utf8_decode($value['descripcion']), 1, 1, 'C', $fill);
    $fill = !$fill;
  }

  ob_end_clean(); // Limpia el buffer de salida
  $pdf->Output('I', 'reporte_usuarios_' . date('Y-m-d_H-i-s') . '.pdf');
} else {
  ob_end_clean();
  header('Location: ../../../../views/500.php');
  exit;
}
