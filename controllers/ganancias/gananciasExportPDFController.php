<?php
// Configurar zona horaria de Ecuador
date_default_timezone_set('America/Guayaquil');

require_once '../../assets/fpdf/fpdf.php';
include_once '../../models/gananciasModel.php';
include_once '../../config/empresa.php';

class GananciasPDF extends FPDF
{
  function Header()
  {
    try {
      // Obtener logo de la empresa
      $logoPath = '../../assets/image/' . Empresa::getLogoEmpresa();
      $nombreEmpresa = Empresa::getNombre();

      // Verificar si el logo existe
      if (file_exists($logoPath)) {
        // Logo a la izquierda
        $this->Image($logoPath, 10, 6, 30);

        // Mover a la derecha para el texto
        $this->SetX(50);
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, $nombreEmpresa, 0, 1, 'L');
        $this->SetX(50);
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 5, 'REPORTE DE GANANCIAS', 0, 1, 'L');
      } else {
        // Si no hay logo, centrar todo
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, $nombreEmpresa, 0, 1, 'C');
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(0, 5, 'REPORTE DE GANANCIAS', 0, 1, 'C');
      }

      $this->SetFont('Arial', '', 10);
      $this->Cell(0, 5, 'Fecha: ' . date('d/m/Y H:i:s'), 0, 1, 'C');
    } catch (Exception $e) {
      // Fallback si hay error cargando la empresa
      $this->SetFont('Arial', 'B', 15);
      $this->Cell(0, 10, 'REPORTE DE GANANCIAS', 0, 1, 'C');
      $this->Cell(0, 5, 'Fecha: ' . date('d/m/Y H:i:s'), 0, 1, 'C');
    }

    $this->Ln(10);
  }

  function Footer()
  {
    $this->SetY(-15);
    $this->SetFont('Arial', 'I', 8);
    $this->Cell(0, 10, 'Pagina ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
  }

  function ResumenEjecutivo($resumen, $filtros = [], $datosGanancias = [])
  {
    // Calcular totales desde los datos reales
    $totalVentasReal = 0;
    $totalCostosReal = 0;
    $totalProductosReal = 0;
    $cantidadTotalReal = 0;
    $gananciaBrutaReal = 0;
    $gananciaNetaReal = 0;

    foreach ($datosGanancias as $item) {
      $totalVentasReal += floatval($item['total_ventas'] ?? 0);
      // Usar costo_total del modelo si existe, sino calcular
      $costoItem = floatval($item['costo_total'] ?? (floatval($item['cantidad_vendida'] ?? 0) * floatval($item['producto_precio_compra'] ?? 0)));
      $totalCostosReal += $costoItem;
      $cantidadTotalReal += intval($item['cantidad_vendida'] ?? 0);
      $gananciaNetaReal += floatval($item['ganancia_neta'] ?? 0);
      $totalProductosReal++;
    }

    $gananciaBrutaReal = $totalVentasReal - $totalCostosReal;
    // Título de la sección
    $this->SetFont('Arial', 'B', 16);
    $this->SetFillColor(52, 152, 219);
    $this->SetTextColor(255, 255, 255);
    $this->Cell(0, 12, 'RESUMEN EJECUTIVO', 0, 1, 'C', true);
    $this->SetTextColor(0, 0, 0);
    $this->Ln(5);

    // Filtros aplicados
    $this->SetFont('Arial', 'B', 10);
    $this->Cell(0, 5, 'FILTROS APLICADOS:', 0, 1);
    $this->SetFont('Arial', '', 9);

    if (!empty($filtros['fecha_desde']) && !empty($filtros['fecha_hasta'])) {
      $this->Cell(0, 5, 'Periodo: ' . $filtros['fecha_desde'] . ' al ' . $filtros['fecha_hasta'], 0, 1);
    } else {
      $this->Cell(0, 5, 'Periodo: Todos los registros', 0, 1);
    }

    if (!empty($filtros['codigo_producto'])) {
      // Resaltar cuando hay filtro de producto específico
      $this->SetFont('Arial', 'B', 9);
      $this->SetTextColor(255, 0, 0); // Texto rojo para destacar
      $this->Cell(0, 5, 'PRODUCTO FILTRADO: ' . $filtros['codigo_producto'], 0, 1);
      $this->SetTextColor(0, 0, 0); // Resetear color
      $this->SetFont('Arial', '', 9);

      // Mostrar total de productos encontrados
      $this->Cell(0, 5, 'Productos encontrados: ' . $totalProductosReal, 0, 1);
    } else {
      $this->Cell(0, 5, 'Productos: Todos los productos (' . $totalProductosReal . ' productos)', 0, 1);
    }
    $this->Ln(8);

    // Métricas principales en boxes
    $this->SetFont('Arial', 'B', 12);

    // Primera fila de métricas
    $boxWidth = 85;
    $boxHeight = 20;
    $startX = 10;
    $startY = $this->GetY();

    // Total Productos (primer box)
    $this->SetXY($startX, $startY);
    $this->SetFillColor(230, 246, 255);
    $this->Rect($startX, $startY, $boxWidth, $boxHeight, 'F');
    $this->SetFont('Arial', 'B', 9);
    $this->Cell($boxWidth, 6, 'TOTAL PRODUCTOS', 0, 0, 'C');
    $this->SetXY($startX, $startY + 6);
    $this->SetFont('Arial', 'B', 14);
    $this->SetTextColor(52, 152, 219);
    $this->Cell($boxWidth, 8, $totalProductosReal, 0, 0, 'C');
    $this->SetTextColor(0, 0, 0);

    // Total Ventas (segundo box)
    $ventas_x = $startX + $boxWidth + 10;
    $this->SetXY($ventas_x, $startY);
    $this->SetFillColor(230, 255, 230);
    $this->Rect($ventas_x, $startY, $boxWidth, $boxHeight, 'F');
    $this->SetFont('Arial', 'B', 9);
    $this->Cell($boxWidth, 6, 'TOTAL VENTAS', 0, 0, 'C');
    $this->SetXY($ventas_x, $startY + 6);
    $this->SetFont('Arial', 'B', 11);
    $this->SetTextColor(46, 125, 50);
    $this->Cell($boxWidth, 8, '$ ' . number_format($totalVentasReal, 2), 0, 0, 'C');
    $this->SetTextColor(0, 0, 0);

    // Total Costos (tercer box)
    $costos_x = $ventas_x + $boxWidth + 10;
    $this->SetXY($costos_x, $startY);
    $this->SetFillColor(255, 230, 230);
    $this->Rect($costos_x, $startY, $boxWidth, $boxHeight, 'F');
    $this->SetFont('Arial', 'B', 9);
    $this->Cell($boxWidth, 6, 'TOTAL COSTOS', 0, 0, 'C');
    $this->SetXY($costos_x, $startY + 6);
    $this->SetFont('Arial', 'B', 11);
    $this->SetTextColor(198, 40, 40);
    $this->Cell($boxWidth, 8, '$ ' . number_format($totalCostosReal, 2), 0, 0, 'C');
    $this->SetTextColor(0, 0, 0);

    // Segunda fila de métricas
    $startY2 = $startY + $boxHeight + 8;

    // Cantidad Vendida
    $this->SetXY($startX, $startY2);
    $this->SetFillColor(255, 243, 230);
    $this->Rect($startX, $startY2, $boxWidth, $boxHeight, 'F');
    $this->SetFont('Arial', 'B', 9);
    $this->Cell($boxWidth, 6, 'CANTIDAD VENDIDA', 0, 0, 'C');
    $this->SetXY($startX, $startY2 + 6);
    $this->SetFont('Arial', 'B', 12);
    $this->SetTextColor(255, 152, 0);
    $this->Cell($boxWidth, 8, $cantidadTotalReal . ' unidades', 0, 0, 'C');
    $this->SetTextColor(0, 0, 0);

    // Posicionarse para las métricas grandes
    $this->SetY($startY2 + $boxHeight + 8);
    // Métricas de ganancia (centradas)
    $fullWidth = 180;
    $boxHeightGrande = 15;

    // Ganancia Bruta
    $this->SetX(($this->w - $fullWidth) / 2);
    $this->SetFillColor(200, 255, 200);
    $this->Rect($this->GetX(), $this->GetY(), $fullWidth, $boxHeightGrande, 'F');
    $this->SetFont('Arial', 'B', 9);
    $this->Cell($fullWidth, 4, 'GANANCIA BRUTA', 0, 0, 'C');
    $this->Ln(4);
    $this->SetX(($this->w - $fullWidth) / 2);
    $this->SetFont('Arial', 'B', 12);
    $this->SetTextColor(76, 175, 80);
    $this->Cell($fullWidth, 6, '$ ' . number_format($gananciaBrutaReal, 2), 0, 0, 'C');
    $this->SetTextColor(0, 0, 0);
    $this->Ln(7);

    // Ganancia Neta
    $this->SetX(($this->w - $fullWidth) / 2);
    $this->SetFillColor(180, 240, 255);
    $this->Rect($this->GetX(), $this->GetY(), $fullWidth, $boxHeightGrande, 'F');
    $this->SetFont('Arial', 'B', 9);
    $this->Cell($fullWidth, 4, 'GANANCIA NETA', 0, 0, 'C');
    $this->Ln(4);
    $this->SetX(($this->w - $fullWidth) / 2);
    $this->SetFont('Arial', 'B', 12);
    $this->SetTextColor(33, 150, 243);
    $this->Cell($fullWidth, 6, '$ ' . number_format($gananciaNetaReal, 2), 0, 0, 'C');
    $this->SetTextColor(0, 0, 0);
    $this->Ln(7);

    // Margen de Ganancia
    $margen = 0;
    if ($totalVentasReal > 0) {
      $margen = ($gananciaNetaReal / $totalVentasReal) * 100;
    }

    $this->SetX(($this->w - $fullWidth) / 2);
    $this->SetFillColor(255, 248, 180);
    $this->Rect($this->GetX(), $this->GetY(), $fullWidth, $boxHeightGrande, 'F');
    $this->SetFont('Arial', 'B', 9);
    $this->Cell($fullWidth, 4, 'MARGEN DE GANANCIA', 0, 0, 'C');
    $this->Ln(4);
    $this->SetX(($this->w - $fullWidth) / 2);
    $this->SetFont('Arial', 'B', 12);
    $this->SetTextColor(255, 193, 7);
    $this->Cell($fullWidth, 6, number_format($margen, 2) . '%', 0, 0, 'C');
    $this->SetTextColor(0, 0, 0);
  }

  function TablaProductos($header, $data)
  {
    // Título de la sección
    $this->SetFont('Arial', 'B', 16);
    $this->SetFillColor(76, 175, 80);
    $this->SetTextColor(255, 255, 255);
    $this->Cell(0, 12, 'DETALLE POR PRODUCTOS', 0, 1, 'C', true);
    $this->SetTextColor(0, 0, 0);
    $this->Ln(8);

    // Verificar si hay datos
    if (empty($data)) {
      $this->SetFont('Arial', 'I', 12);
      $this->SetTextColor(180, 180, 180);
      $this->Cell(0, 10, 'No hay datos disponibles para los filtros aplicados', 0, 1, 'C');
      $this->SetTextColor(0, 0, 0);
      return;
    }

    // Headers de tabla
    $this->SetFont('Arial', 'B', 9);
    $this->SetFillColor(200, 220, 255);

    // Anchos ajustados para 8 columnas (landscape: 297mm)
    $w = array(25, 75, 20, 25, 25, 30, 35, 25); // Total: 260mm
    for ($i = 0; $i < count($header); $i++) {
      $this->Cell($w[$i], 8, utf8_decode($header[$i]), 1, 0, 'C', true);
    }
    $this->Ln();

    // Datos
    $this->SetFont('Arial', '', 8);
    $this->SetFillColor(245, 245, 245);
    $fill = false;

    foreach ($data as $row) {
      $this->Cell($w[0], 6, utf8_decode($row['producto_codigo']), 'LR', 0, 'L', $fill);
      $this->Cell($w[1], 6, utf8_decode(substr($row['producto_nombre'], 0, 45)), 'LR', 0, 'L', $fill);
      $this->Cell($w[2], 6, $row['cantidad_vendida'], 'LR', 0, 'C', $fill);
      $this->Cell($w[3], 6, number_format($row['producto_precio_compra'], 2), 'LR', 0, 'R', $fill);
      $this->Cell($w[4], 6, number_format($row['producto_precio_venta'], 2), 'LR', 0, 'R', $fill);
      $this->Cell($w[5], 6, number_format($row['total_ventas'], 2), 'LR', 0, 'R', $fill);
      $this->Cell($w[6], 6, number_format($row['ganancia_neta'] ?? 0, 2), 'LR', 0, 'R', $fill);
      $this->Cell($w[7], 6, number_format($row['margen_porcentaje'] ?? 0, 2) . '%', 'LR', 0, 'R', $fill);
      $this->Ln();
      $fill = !$fill;
    }

    // Línea final
    $this->Cell(array_sum($w), 0, '', 'T');
  }

  function AnalisisPorPeriodo($data, $filtros = [])
  {
    // Título de la sección
    $this->SetFont('Arial', 'B', 16);
    $this->SetFillColor(156, 39, 176);
    $this->SetTextColor(255, 255, 255);
    $this->Cell(0, 12, 'ANALISIS POR PERIODO', 0, 1, 'C', true);
    $this->SetTextColor(0, 0, 0);
    $this->Ln(8);

    if (empty($data)) {
      $this->SetFont('Arial', 'I', 12);
      $this->Cell(0, 10, 'No hay suficientes datos para generar el analisis por periodo.', 0, 1, 'C');
      $this->Ln(5);
      $this->SetFont('Arial', '', 9);
      $this->Cell(0, 5, 'Recomendaciones:', 0, 1, 'L');
      $this->Cell(0, 5, '• Asegurese de tener ventas registradas en el periodo seleccionado', 0, 1);
      $this->Cell(0, 5, '• Verifique que las fechas de filtro sean correctas', 0, 1);
      $this->Cell(0, 5, '• Considere ampliar el rango de fechas para obtener mas datos', 0, 1);
      return;
    }

    // Calcular estadísticas del análisis
    $totalProductos = count($data);
    $promedioVentas = 0;
    $promedioGanancia = 0;
    $mejorProducto = null;
    $peorProducto = null;
    $maxGanancia = -999999;
    $minGanancia = 999999;

    foreach ($data as $producto) {
      $promedioVentas += $producto['total_ventas'];
      $promedioGanancia += $producto['ganancia_neta'];

      if ($producto['ganancia_neta'] > $maxGanancia) {
        $maxGanancia = $producto['ganancia_neta'];
        $mejorProducto = $producto;
      }

      if ($producto['ganancia_neta'] < $minGanancia) {
        $minGanancia = $producto['ganancia_neta'];
        $peorProducto = $producto;
      }
    }

    if ($totalProductos > 0) {
      $promedioVentas = $promedioVentas / $totalProductos;
      $promedioGanancia = $promedioGanancia / $totalProductos;
    }

    // Mostrar estadísticas
    $this->SetFont('Arial', 'B', 12);
    $this->Cell(0, 7, 'ESTADISTICAS DEL PERIODO', 0, 1, 'L');
    $this->SetFont('Arial', '', 10);

    $boxWidth = 85;
    $boxHeight = 18;
    $startX = 10;
    $startY = $this->GetY();

    // Primera fila de estadísticas
    // Productos Analizados (primer box)
    $this->SetXY($startX, $startY);
    $this->SetFillColor(240, 248, 255);
    $this->Rect($startX, $startY, $boxWidth, $boxHeight, 'F');
    $this->SetFont('Arial', 'B', 8);
    $this->Cell($boxWidth, 5, 'PRODUCTOS ANALIZADOS', 0, 0, 'C');
    $this->SetXY($startX, $startY + 5);
    $this->SetFont('Arial', 'B', 11);
    $this->Cell($boxWidth, 7, $totalProductos, 0, 0, 'C');

    // Promedio Ventas (segundo box)
    $ventas_x = $startX + $boxWidth + 10;
    $this->SetXY($ventas_x, $startY);
    $this->SetFillColor(240, 255, 240);
    $this->Rect($ventas_x, $startY, $boxWidth, $boxHeight, 'F');
    $this->SetFont('Arial', 'B', 8);
    $this->Cell($boxWidth, 5, 'PROMEDIO VENTAS', 0, 0, 'C');
    $this->SetXY($ventas_x, $startY + 5);
    $this->SetFont('Arial', 'B', 10);
    $this->Cell($boxWidth, 7, '$ ' . number_format($promedioVentas, 2), 0, 0, 'C');

    // Segunda fila de estadísticas
    $startY2 = $startY + $boxHeight + 10;

    // Promedio Ganancia (primer box)
    $this->SetXY($startX, $startY2);
    $this->SetFillColor(255, 248, 240);
    $this->Rect($startX, $startY2, $boxWidth, $boxHeight, 'F');
    $this->SetFont('Arial', 'B', 8);
    $this->Cell($boxWidth, 5, 'PROMEDIO GANANCIA', 0, 0, 'C');
    $this->SetXY($startX, $startY2 + 5);
    $this->SetFont('Arial', 'B', 10);
    $this->Cell($boxWidth, 7, '$ ' . number_format($promedioGanancia, 2), 0, 0, 'C');

    // Rango Ganancias (segundo box)
    $this->SetXY($ventas_x, $startY2);
    $this->SetFillColor(255, 240, 240);
    $this->Rect($ventas_x, $startY2, $boxWidth, $boxHeight, 'F');
    $this->SetFont('Arial', 'B', 8);
    $this->Cell($boxWidth, 5, 'RANGO GANANCIAS', 0, 0, 'C');
    $this->SetXY($ventas_x, $startY2 + 5);
    $this->SetFont('Arial', 'B', 9);
    $this->Cell($boxWidth, 7, '$' . number_format($minGanancia, 2) . ' - $' . number_format($maxGanancia, 2), 0, 0, 'C');

    // Posicionarse para la siguiente sección
    $this->SetY($startY2 + $boxHeight + 10);

    // Mejor y peor producto
    if ($mejorProducto && $peorProducto) {
      $this->SetFont('Arial', 'B', 11);
      $this->Cell(0, 5, 'PRODUCTOS DESTACADOS', 0, 1, 'L');
      $this->Ln(2);

      // Mejor producto
      $this->SetFont('Arial', 'B', 9);
      $this->SetTextColor(76, 175, 80);
      $this->Cell(0, 4, 'MAYOR GANANCIA:', 0, 1, 'L');
      $this->SetTextColor(0, 0, 0);
      $this->SetFont('Arial', '', 8);
      $this->Cell(0, 3, utf8_decode('Producto: ' . $mejorProducto['producto_codigo'] . ' - ' . $mejorProducto['producto_nombre']), 0, 1);
      $this->Cell(0, 3, utf8_decode('Ganancia: $' . number_format($mejorProducto['ganancia_neta'], 2) . ' | Vendido: ' . $mejorProducto['cantidad_vendida'] . ' unidades'), 0, 1);
      $this->Ln(2);

      // Peor producto
      $this->SetFont('Arial', 'B', 9);
      $this->SetTextColor(244, 67, 54);
      $this->Cell(0, 4, 'MENOR GANANCIA:', 0, 1, 'L');
      $this->SetTextColor(0, 0, 0);
      $this->SetFont('Arial', '', 8);
      $this->Cell(0, 3, utf8_decode('Producto: ' . $peorProducto['producto_codigo'] . ' - ' . $peorProducto['producto_nombre']), 0, 1);
      $this->Cell(0, 3, utf8_decode('Ganancia: $' . number_format($peorProducto['ganancia_neta'], 2) . ' | Vendido: ' . $peorProducto['cantidad_vendida'] . ' unidades'), 0, 1);
      $this->Ln(3);
    }

    // Recomendaciones
    $this->SetFont('Arial', 'B', 11);
    $this->Cell(0, 5, 'RECOMENDACIONES', 0, 1, 'L');
    $this->SetFont('Arial', '', 8);

    if ($promedioGanancia > 0) {
      $this->Cell(0, 3, 'El negocio muestra ganancias positivas en este periodo', 0, 1);
    } else {
      $this->Cell(0, 3, 'Revisar estrategias de precios y costos', 0, 1);
    }

    $this->Cell(0, 3, 'Enfocarse en productos con mayor margen de ganancia', 0, 1);
    $this->Cell(0, 3, 'Considerar promociones para productos de baja rotacion', 0, 1);
    $this->Cell(0, 3, 'Monitorear regularmente el rendimiento por producto', 0, 1);
  }
}

// Obtener datos
$fechaDesde = isset($_POST['fecha_desde']) && !empty($_POST['fecha_desde']) ? $_POST['fecha_desde'] : null;
$fechaHasta = isset($_POST['fecha_hasta']) && !empty($_POST['fecha_hasta']) ? $_POST['fecha_hasta'] : null;
$codigoProducto = isset($_POST['codigo_producto']) && !empty($_POST['codigo_producto']) ? $_POST['codigo_producto'] : null;

$ganancias = GananciasModel::obtenerGanancias($fechaDesde, $fechaHasta, $codigoProducto);
$resumen = GananciasModel::obtenerResumenGanancias($fechaDesde, $fechaHasta, $codigoProducto);

// Si no hay datos y NO hay filtros aplicados, usar consulta simple como respaldo
if (empty($ganancias) && !$fechaDesde && !$fechaHasta && !$codigoProducto) {
  $gananciasSimple = GananciasModel::obtenerGananciasSimple();

  foreach ($gananciasSimple as $item) {
    $costoTotal = $item['cantidad_vendida'] * $item['producto_precio_compra'];
    $gananciBruta = $item['total_ventas'] - $costoTotal;
    $gananciaNeta = $gananciBruta;
    $margen = $item['total_ventas'] > 0 ? ($gananciaNeta / $item['total_ventas']) * 100 : 0;
    $ganancias[] = [
      'producto_codigo' => $item['producto_codigo'],
      'producto_nombre' => $item['producto_nombre'],
      'categoria_descripcion' => 'Sin categoría',
      'producto_precio_compra' => $item['producto_precio_compra'],
      'producto_precio_venta' => $item['producto_precio_venta'],
      'cantidad_vendida' => $item['cantidad_vendida'],
      'total_ventas' => $item['total_ventas'],
      'total_descuentos' => 0,
      'costo_total' => $costoTotal,
      'ganancia_bruta' => $gananciBruta,
      'ganancia_neta' => $gananciaNeta,
      'margen_porcentaje' => round($margen, 2)
    ];
  }
}

// Calcular totales desde los datos reales como respaldo
$totalVentasCalculado = 0;
$totalCostosCalculado = 0;
$totalProductosCalculado = 0;
$cantidadTotalCalculada = 0;
$gananciaBrutaCalculada = 0;
$gananciaNetaCalculada = 0;

foreach ($ganancias as $item) {
  $totalVentasCalculado += $item['total_ventas'] ?? 0;
  // Usar costo_total del modelo si existe, sino calcular
  $costoItem = $item['costo_total'] ?? (($item['cantidad_vendida'] ?? 0) * ($item['producto_precio_compra'] ?? 0));
  $totalCostosCalculado += $costoItem;
  $cantidadTotalCalculada += $item['cantidad_vendida'] ?? 0;
  $gananciaNetaCalculada += $item['ganancia_neta'] ?? 0;
  $totalProductosCalculado++;
}

$gananciaBrutaCalculada = $totalVentasCalculado - $totalCostosCalculado;

// Mejorar el resumen con datos calculados
if (empty($resumen) || !isset($resumen['total_ventas_general'])) {
  $resumen = [
    'total_productos' => $totalProductosCalculado,
    'cantidad_total_vendida' => $cantidadTotalCalculada,
    'total_ventas_general' => $totalVentasCalculado,
    'costo_total_general' => $totalCostosCalculado,
    'ganancia_bruta_general' => $gananciaBrutaCalculada,
    'ganancia_neta_general' => $gananciaNetaCalculada
  ];
}

// Headers de la tabla - 8 columnas esenciales
$header = array('Codigo', 'Producto', 'Cant.', 'P.Comp', 'P.Venta', 'T.Ventas', 'Ganancia', 'Margen%');

// Preparar filtros para el PDF
$filtros = [
  'fecha_desde' => $fechaDesde,
  'fecha_hasta' => $fechaHasta,
  'codigo_producto' => $codigoProducto
];

// Crear PDF con múltiples páginas
$pdf = new GananciasPDF('L', 'mm', 'A4'); // Landscape
$pdf->AliasNbPages();

// Página 1: Resumen Ejecutivo
$pdf->AddPage();
$pdf->ResumenEjecutivo($resumen, $filtros, $ganancias);

// Página 2: Tabla de Productos
$pdf->AddPage();
$pdf->TablaProductos($header, $ganancias);

// Página 3: Análisis por Período
$pdf->AddPage();
$pdf->AnalisisPorPeriodo($ganancias, $filtros);

$filename = 'reporte_ganancias_' . date('Y-m-d_H-i-s') . '.pdf';
$pdf->Output('D', $filename);
