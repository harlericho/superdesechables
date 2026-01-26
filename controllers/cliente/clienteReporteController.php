<?php
require_once '../../assets/fpdf/fpdf.php';
require_once '../../models/clienteModel.php';
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
    $this->SetTextColor(128, 128, 128); // Gris
    $this->Cell(60);
    $this->Cell(70, 10, Empresa::getNombre(), 0, 1, 'C');

    // Subtítulo
    $this->SetFont('Arial', 'B', 12);
    $this->Cell(60);
    $this->Cell(70, 10, 'Reporte de Clientes', 0, 1, 'C');

    // Fecha de generación
    $this->SetFont('Arial', '', 10);
    $this->Cell(60);
    setlocale(LC_TIME, 'es_ES.UTF-8', 'Spanish_Spain', 'es_ES');
    date_default_timezone_set('America/Bogota'); // Ajusta según tu país
    $this->Cell(70, 10, 'Fecha: ' . date('Y-m-d H:i:s'), 0, 1, 'C');

    // Encabezados de tabla
    $this->Ln(10);
    $this->SetFont('Arial', 'B', 10);
    $this->SetFillColor(169, 169, 169); // Gris medio
    $this->SetTextColor(0);
    $this->Cell(20, 10, 'DNI', 1, 0, 'C', true);
    $this->Cell(35, 10, 'Nombres', 1, 0, 'C', true);
    $this->Cell(45, 10, 'Direccion', 1, 0, 'C', true);
    $this->Cell(40, 10, 'Email', 1, 0, 'C', true);
    $this->Cell(30, 10, 'Telefono', 1, 0, 'C', true);
    $this->Cell(25, 10, 'Rol', 1, 1, 'C', true);
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
  $pdf->SetFillColor(192, 192, 192); // Gris claro
  $pdf->SetTextColor(0);
  $fill = false; // Alternar colores

  foreach (ClienteModel::obtenerClientes() as $value) {
    $pdf->SetFont('Arial', '', 7);
    $pdf->Cell(20, 10, utf8_decode($value['cliente_dni']), 1, 0, 'C', $fill);
    $pdf->Cell(35, 10, utf8_decode($value['cliente_nombres'] . " " . $value['cliente_apellidos']), 1, 0, 'C', $fill); // Texto pequeño para dirección
    $pdf->SetFont('Arial', '', 5);
    $pdf->Cell(45, 10, utf8_decode($value['cliente_direccion']), 1, 0, 'C', $fill);
    $pdf->SetFont('Arial', '', 7);
    $pdf->Cell(40, 10, utf8_decode($value['cliente_email']), 1, 0, 'C', $fill);
    $pdf->Cell(30, 10, utf8_decode($value['cliente_telefono']), 1, 0, 'C', $fill);
    $pdf->Cell(25, 10, utf8_decode($value['rol_descripcion']), 1, 1, 'C', $fill);
    $fill = !$fill;
  }

  ob_end_clean(); // Limpia el buffer de salida
  $pdf->Output('I', 'reporte_clientes_' . date('Y-m-d_H-i-s') . '.pdf');
} else {
  ob_end_clean();
  header('Location: ../../../../views/500.php');
  exit;
}
