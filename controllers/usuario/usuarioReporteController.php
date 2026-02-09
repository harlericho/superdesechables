<?php
require_once '../../assets/fpdf/fpdf.php';
require_once '../../models/usuarioModel.php';
require_once '../../config/empresa.php';

session_start();
ob_start(); // Inicia el buffer de salida

class PDF extends FPDF
{
  private $totalUsuarios = 0;

  function setTotalUsuarios($total)
  {
    $this->totalUsuarios = $total;
  }

  // Cabecera de página
  function Header()
  {
    // Línea decorativa superior
    $this->SetDrawColor(156, 39, 176); // Púrpura profesional
    $this->SetLineWidth(1);
    $this->Line(10, 8, 287, 8);

    // Logo
    $logoPath = '../../assets/image/' . Empresa::getLogoEmpresa();
    if (file_exists($logoPath)) {
      $this->Image($logoPath, 15, 12, 45);
    }

    // Información de la empresa (lado derecho)
    $this->SetFont('Arial', 'B', 18);
    $this->SetTextColor(156, 39, 176); // Púrpura
    $this->SetXY(70, 15);
    $this->Cell(150, 8, utf8_decode(Empresa::getNombre()), 0, 1, 'L');

    $this->SetFont('Arial', '', 9);
    $this->SetTextColor(80, 80, 80);
    $this->SetX(70);
    $this->Cell(150, 5, 'RUC: ' . Empresa::getRuc(), 0, 1, 'L');
    $this->SetX(70);
    $this->Cell(150, 5, utf8_decode('Teléfono: ') . Empresa::getTelefono(), 0, 1, 'L');

    // Fecha y hora de generación (lado derecho superior)
    $this->SetFont('Arial', '', 8);
    $this->SetTextColor(100, 100, 100);
    setlocale(LC_TIME, 'es_ES.UTF-8', 'Spanish_Spain', 'es_ES');
    date_default_timezone_set('America/Bogota');
    $this->SetXY(220, 15);
    $this->Cell(60, 5, 'Generado: ' . date('d/m/Y'), 0, 1, 'R');
    $this->SetX(220);
    $this->Cell(60, 5, 'Hora: ' . date('H:i:s'), 0, 1, 'R');

    // Título del reporte
    $this->Ln(8);
    $this->SetFont('Arial', 'B', 16);
    $this->SetTextColor(52, 73, 94); // Azul oscuro
    $this->Cell(0, 10, utf8_decode('REPORTE DE USUARIOS DEL SISTEMA'), 0, 1, 'C');

    // Línea decorativa bajo el título
    $this->SetDrawColor(156, 39, 176);
    $this->SetLineWidth(0.5);
    $this->Line(90, $this->GetY(), 207, $this->GetY());

    // Encabezados de tabla
    $this->Ln(8);
    $this->SetFont('Arial', 'B', 10);
    $this->SetFillColor(156, 39, 176); // Púrpura
    $this->SetTextColor(255, 255, 255); // Blanco
    $this->SetDrawColor(156, 39, 176);

    $this->Cell(10, 9, utf8_decode('N°'), 1, 0, 'C', true);
    $this->Cell(70, 9, 'Nombre Completo', 1, 0, 'C', true);
    $this->Cell(75, 9, 'Email', 1, 0, 'C', true);
    $this->Cell(40, 9, utf8_decode('Fecha Registro'), 1, 0, 'C', true);
    $this->Cell(45, 9, 'Rol', 1, 0, 'C', true);
    $this->Cell(27, 9, 'Estado', 1, 1, 'C', true);
  }

  // Pie de página
  function Footer()
  {
    // Línea decorativa
    $this->SetY(-25);
    $this->SetDrawColor(156, 39, 176);
    $this->SetLineWidth(0.5);
    $this->Line(10, $this->GetY(), 287, $this->GetY());

    // Información del pie
    $this->Ln(2);
    $this->SetFont('Arial', 'I', 8);
    $this->SetTextColor(100, 100, 100);

    // Total de usuarios (izquierda)
    $this->Cell(95, 5, utf8_decode('Total de usuarios: ') . $this->totalUsuarios, 0, 0, 'L');

    // Número de página (centro)
    $this->Cell(95, 5, utf8_decode('Página ') . $this->PageNo() . ' de {nb}', 0, 0, 'C');

    // Generado por (derecha)
    $this->Cell(95, 5, utf8_decode('Generado por: ') . Empresa::getNombre(), 0, 1, 'R');

    // Línea final
    $this->Ln(1);
    $this->SetFont('Arial', '', 7);
    $this->SetTextColor(150, 150, 150);
    $this->Cell(0, 3, utf8_decode('Este documento es generado automáticamente por el sistema'), 0, 0, 'C');
  }
}

// Obtener rol del usuario autenticado
$usuario = UsuarioModel::obtenerDatoUsuario($_SESSION['email']);
$rolDescripcion = $usuario[0]['rol_descripcion'] ?? '';

if ($rolDescripcion === 'ADMINISTRADOR') {
  // Obtener usuarios
  $usuarios = UsuarioModel::obtenerUsuarios();
  $totalUsuarios = count($usuarios);

  // Crear PDF en orientación horizontal para mejor visualización
  $pdf = new PDF('L', 'mm', 'A4'); // L = Landscape (horizontal)
  $pdf->setTotalUsuarios($totalUsuarios);
  $pdf->AliasNbPages();
  $pdf->AddPage();
  $pdf->SetFont('Arial', '', 9);
  $pdf->SetAutoPageBreak(true, 25);

  // Colores alternados para filas
  $pdf->SetDrawColor(200, 200, 200); // Borde gris claro
  $fill = false;
  $contador = 1;

  foreach ($usuarios as $value) {
    // Alternar colores de fondo
    if ($fill) {
      $pdf->SetFillColor(243, 229, 245); // Púrpura muy claro
      $pdf->SetTextColor(52, 73, 94); // Azul oscuro
    } else {
      $pdf->SetFillColor(255, 255, 255); // Blanco
      $pdf->SetTextColor(44, 62, 80); // Gris oscuro
    }

    $alturaFila = 8;

    // Número consecutivo
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(10, $alturaFila, $contador, 1, 0, 'C', true);

    // Nombres completos
    $pdf->SetFont('Arial', 'B', 9);
    $nombreUsuario = $value['nombres'];
    // Limitar longitud del nombre
    if (strlen($nombreUsuario) > 42) {
      $nombreUsuario = substr($nombreUsuario, 0, 39) . '...';
    }
    $pdf->Cell(70, $alturaFila, utf8_decode($nombreUsuario), 1, 0, 'L', true);

    // Email
    $pdf->SetFont('Arial', '', 8);
    $email = $value['email'];
    if (strlen($email) > 50) {
      $email = substr($email, 0, 47) . '...';
    }
    $pdf->Cell(75, $alturaFila, utf8_decode($email), 1, 0, 'L', true);

    // Fecha de registro (sin mostrar contraseña por seguridad)
    $pdf->SetFont('Arial', '', 8);
    $fechaCreacion = isset($value['fecha_creacion']) ? date('d/m/Y', strtotime($value['fecha_creacion'])) : '---';
    $pdf->Cell(40, $alturaFila, utf8_decode($fechaCreacion), 1, 0, 'C', true);

    // Rol
    $pdf->SetFont('Arial', 'B', 9);
    $rol = $value['descripcion'];
    // Color según el rol
    if ($rol === 'ADMINISTRADOR') {
      $pdf->SetTextColor(220, 53, 69); // Rojo
    } elseif ($rol === 'VENDEDOR') {
      $pdf->SetTextColor(0, 123, 255); // Azul
    } else {
      $pdf->SetTextColor(40, 167, 69); // Verde
    }
    $pdf->Cell(45, $alturaFila, utf8_decode($rol), 1, 0, 'C', true);

    // Estado
    $pdf->SetFont('Arial', 'B', 8);
    $estado = isset($value['estado']) && $value['estado'] == 1 ? 'ACTIVO' : 'INACTIVO';

    // Color según el estado
    if ($estado === 'ACTIVO') {
      $pdf->SetTextColor(40, 167, 69); // Verde
    } else {
      $pdf->SetTextColor(220, 53, 69); // Rojo
    }
    $pdf->Cell(27, $alturaFila, utf8_decode($estado), 1, 1, 'C', true);

    $fill = !$fill;
    $contador++;
  }

  // Resumen al final
  $pdf->Ln(5);
  $pdf->SetFont('Arial', 'B', 11);
  $pdf->SetTextColor(156, 39, 176);
  $pdf->Cell(0, 8, utf8_decode('Total de usuarios registrados: ') . $totalUsuarios, 0, 1, 'R');

  ob_end_clean(); // Limpia el buffer de salida
  $pdf->Output('I', 'reporte_usuarios_' . date('Y-m-d_H-i-s') . '.pdf');
} else {
  ob_end_clean();
  header('Location: ../../../../views/500.php');
  exit;
}
