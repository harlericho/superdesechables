<?php
require_once '../../assets/fpdf/fpdf.php';
require_once '../../models/productoModel.php';

class PDF extends FPDF
{
  // Función para generar código de barras Code 39
  function Code39($x, $y, $code, $baseline = 0.5, $height = 5)
  {
    $wide = $baseline;
    $narrow = $baseline / 3;
    $gap = $narrow;

    $code = strtoupper($code);

    // Tabla de codificación Code 39
    $barChar = array(
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
      'A' => '100001001',
      'B' => '001001001',
      'C' => '101001000',
      'D' => '000011001',
      'E' => '100011000',
      'F' => '001011000',
      'G' => '000001101',
      'H' => '100001100',
      'I' => '001001100',
      'J' => '000011100',
      'K' => '100000011',
      'L' => '001000011',
      'M' => '101000010',
      'N' => '000010011',
      'O' => '100010010',
      'P' => '001010010',
      'Q' => '000000111',
      'R' => '100000110',
      'S' => '001000110',
      'T' => '000010110',
      'U' => '110000001',
      'V' => '011000001',
      'W' => '111000000',
      'X' => '010010001',
      'Y' => '110010000',
      'Z' => '011010000',
      '-' => '010000101',
      '.' => '110000100',
      ' ' => '011000100',
      '*' => '010010100',
      '$' => '010101000',
      '/' => '010100010',
      '+' => '010001010',
      '%' => '000101010'
    );

    $this->SetFont('Arial', '', 10);
    $this->SetFillColor(0, 0, 0);

    $code = '*' . $code . '*';

    for ($i = 0; $i < strlen($code); $i++) {
      if (!isset($barChar[$code[$i]])) {
        continue;
      }

      $char = $barChar[$code[$i]];

      for ($j = 0; $j < 9; $j++) {
        $lineWidth = ($char[$j] == '1') ? $wide : $narrow;

        if ($j % 2 == 0) {
          $this->Rect($x, $y, $lineWidth, $height, 'F');
        }

        $x += $lineWidth;
      }

      $x += $gap;
    }
  }

  // Función para generar código de barras EAN-13
  function EAN13($x, $y, $code, $height = 16)
  {
    $code = str_pad($code, 12, '0', STR_PAD_LEFT);

    // Calcular dígito de control
    $sum = 0;
    for ($i = 0; $i < 12; $i++) {
      $sum += ($i % 2 == 0) ? (int)$code[$i] : (int)$code[$i] * 3;
    }
    $checksum = (10 - ($sum % 10)) % 10;
    $code .= $checksum;

    // Patrones EAN-13
    $leftOdd = array('0001101', '0011001', '0010011', '0111101', '0100011', '0110001', '0101111', '0111011', '0110111', '0001011');
    $leftEven = array('0100111', '0110011', '0011011', '0100001', '0011101', '0111001', '0000101', '0010001', '0001001', '0010111');
    $right = array('1110010', '1100110', '1101100', '1000010', '1011100', '1001110', '1010000', '1000100', '1001000', '1110100');
    $firstDigit = array('000000', '001011', '001101', '001110', '010011', '011001', '011100', '010101', '010110', '011010');

    $this->SetFillColor(0, 0, 0);

    // Barras de inicio
    $this->Rect($x, $y, 1, $height, 'F');
    $x += 2;
    $this->Rect($x, $y, 1, $height, 'F');
    $x += 1;

    // Primera parte
    $pattern = $firstDigit[(int)$code[0]];
    for ($i = 1; $i <= 6; $i++) {
      $bars = ($pattern[$i - 1] == '0') ? $leftOdd[(int)$code[$i]] : $leftEven[(int)$code[$i]];

      for ($j = 0; $j < 7; $j++) {
        if ($bars[$j] == '1') {
          $this->Rect($x, $y, 1, $height, 'F');
        }
        $x += 1;
      }
    }

    // Barras centrales
    $x += 1;
    $this->Rect($x, $y, 1, $height, 'F');
    $x += 2;
    $this->Rect($x, $y, 1, $height, 'F');
    $x += 2;

    // Segunda parte
    for ($i = 7; $i <= 12; $i++) {
      $bars = $right[(int)$code[$i]];

      for ($j = 0; $j < 7; $j++) {
        if ($bars[$j] == '1') {
          $this->Rect($x, $y, 1, $height, 'F');
        }
        $x += 1;
      }
    }

    // Barras finales
    $this->Rect($x, $y, 1, $height, 'F');
    $x += 2;
    $this->Rect($x, $y, 1, $height, 'F');

    // Mostrar código debajo
    $this->SetFont('Arial', '', 10);
    $this->Text($x - 60, $y + $height + 4, $code);
  }
}

// Procesar solicitud
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['productos'])) {
  $productos = json_decode($_POST['productos'], true);

  if (!empty($productos)) {
    $pdf = new PDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 14);

    $pdf->Cell(0, 10, 'CODIGOS DE BARRAS DE PRODUCTOS', 0, 1, 'C');
    $pdf->Ln(5);

    $x = 10;
    $y = 30;
    $contador = 0;
    $porFila = 3; // Códigos por fila

    foreach ($productos as $producto) {
      // Dibujar borde
      $pdf->Rect($x, $y, 60, 40);

      // Nombre del producto
      $pdf->SetFont('Arial', 'B', 8);
      $pdf->SetXY($x + 2, $y + 2);
      $pdf->MultiCell(56, 3.5, utf8_decode($producto['nombre']), 0, 'C');

      // Precio
      $pdf->SetFont('Arial', 'B', 10);
      $pdf->SetXY($x + 2, $y + 12);
      $pdf->Cell(56, 5, '$. ' . number_format($producto['precio'], 2), 0, 0, 'C');

      // Código de barras - centrado mejorado
      $codigoBarras = $producto['codigo'];
      $anchoBarras = strlen($codigoBarras) * 1.2; // Ajuste más preciso
      $centroX = $x + (60 - $anchoBarras) / 2 - 2; // Ajuste de -2 para corregir desplazamiento
      $pdf->Code39($centroX, $y + 20, $codigoBarras, 0.35, 10);

      // Código debajo del código de barras
      $pdf->SetFont('Arial', '', 8);
      $pdf->SetXY($x + 2, $y + 32);
      $pdf->Cell(56, 4, $codigoBarras, 0, 0, 'C');

      $contador++;

      // Posicionar siguiente código
      if ($contador % $porFila == 0) {
        $x = 10;
        $y += 45;

        // Nueva página si es necesario
        if ($y > 250) {
          $pdf->AddPage();
          $y = 10;
        }
      } else {
        $x += 65;
      }
    }

    // Generar PDF
    $pdf->Output('D', 'codigos_barras_' . date('Ymd_His') . '.pdf');
  } else {
    echo json_encode(['error' => 'No se recibieron productos']);
  }
} else {
  echo json_encode(['error' => 'Solicitud inválida']);
}
