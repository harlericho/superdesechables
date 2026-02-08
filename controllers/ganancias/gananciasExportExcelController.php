<?php
// Configurar zona horaria de Ecuador
date_default_timezone_set('America/Guayaquil');

require '../../vendor/autoload.php';
include_once '../../models/gananciasModel.php';
include_once '../../config/empresa.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

// Obtener datos de la empresa
$nombreEmpresa = Empresa::getNombre();


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
      'producto_id' => $item['producto_id'] ?? 0,
      'producto_codigo' => $item['producto_codigo'],
      'producto_nombre' => $item['producto_nombre'],
      'producto_precio_compra' => $item['producto_precio_compra'],
      'producto_precio_venta' => $item['producto_precio_venta'],
      'categoria_descripcion' => $item['categoria_descripcion'], // Campo faltante
      'proveedor_nombre' => 'Sin proveedor', // Campo faltante
      'cantidad_vendida' => $item['cantidad_vendida'],
      'total_ventas' => $item['total_ventas'],
      'total_descuentos' => 0, // Campo faltante
      'costo_total' => $costoTotal, // Campo faltante
      'ganancia_bruta' => $gananciBruta, // Campo faltante
      'ganancia_neta' => $gananciaNeta,
      'margen_porcentaje' => round($margen, 2)
    ];
  }
}

// Calcular totales desde los datos reales como respaldo/verificación
$totalVentasCalculado = 0;
$totalCostosCalculado = 0;
$totalProductosCalculado = 0;
$cantidadTotalCalculada = 0;
$gananciaBrutaCalculada = 0;
$gananciaNetaCalculada = 0;

foreach ($ganancias as $item) {
  $totalVentasCalculado += $item['total_ventas'] ?? 0;
  $costoItem = ($item['cantidad_vendida'] ?? 0) * ($item['producto_precio_compra'] ?? 0);
  $totalCostosCalculado += $costoItem;
  $cantidadTotalCalculada += $item['cantidad_vendida'] ?? 0;
  $gananciaNetaCalculada += $item['ganancia_neta'] ?? 0;
  $totalProductosCalculado++;
}

$gananciaBrutaCalculada = $totalVentasCalculado - $totalCostosCalculado;

// Mejorar el resumen con datos calculados si están vacíos
if (empty($resumen) || !isset($resumen['total_ventas_general'])) {
  $resumen = [
    'total_productos' => $totalProductosCalculado,
    'cantidad_total_vendida' => $cantidadTotalCalculada,
    'total_ventas_general' => $totalVentasCalculado,
    'costo_total_general' => $totalCostosCalculado,
    'ganancia_bruta_general' => $gananciaBrutaCalculada,
    'ganancia_neta_general' => $gananciaNetaCalculada,
    'total_descuentos_general' => 0
  ];
}

// Crear nuevo Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Reporte Ganancias');

// Configurar nombre de archivo
$filename = 'reporte_ganancias';
if ($codigoProducto) {
  $filename .= '_producto_' . $codigoProducto;
}
$filename .= '_' . date('Y-m-d_H-i-s') . '.xlsx';

$logoPath = '../../assets/image/' . Empresa::getLogoEmpresa();

$row = 1;

// AGREGAR LOGO si existe
if (file_exists($logoPath)) {
  $drawing = new Drawing();
  $drawing->setName('Logo');
  $drawing->setDescription('Logo de la Empresa');
  $drawing->setPath($logoPath);
  $drawing->setCoordinates('A1');
  $drawing->setHeight(60);
  $drawing->setWorksheet($sheet);
  $row = 4; // Dejar espacio para el logo
}

// ENCABEZADO
$sheet->setCellValue('B' . $row, $nombreEmpresa);
$sheet->mergeCells('B' . $row . ':H' . $row);
$sheet->getStyle('B' . $row)->applyFromArray([
  'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '1F4E78']],
  'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
]);
$row++;

$sheet->setCellValue('B' . $row, 'REPORTE DE GANANCIAS');
$sheet->mergeCells('B' . $row . ':H' . $row);
$sheet->getStyle('B' . $row)->applyFromArray([
  'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '1F4E78']],
  'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
]);
$row++;

$sheet->setCellValue('B' . $row, 'Fecha de generación: ' . date('d/m/Y H:i:s'));
$sheet->mergeCells('B' . $row . ':H' . $row);
$sheet->getStyle('B' . $row)->applyFromArray([
  'font' => ['size' => 10, 'italic' => true],
  'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
]);
$row += 2;

// FILTROS APLICADOS
$sheet->setCellValue('A' . $row, 'FILTROS APLICADOS:');
$sheet->getStyle('A' . $row)->applyFromArray([
  'font' => ['bold' => true, 'size' => 12],
  'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E7E6E6']]
]);
$row++;

if (!empty($fechaDesde) && !empty($fechaHasta)) {
  $sheet->setCellValue('A' . $row, 'Período: ' . $fechaDesde . ' al ' . $fechaHasta);
} else {
  $sheet->setCellValue('A' . $row, 'Período: Todos los registros');
}
$row++;

if (!empty($codigoProducto)) {
  $sheet->setCellValue('A' . $row, '*** PRODUCTO FILTRADO: ' . $codigoProducto . ' ***');
  $sheet->getStyle('A' . $row)->applyFromArray([
    'font' => ['bold' => true, 'color' => ['rgb' => 'FF0000']]
  ]);
  $row++;
  $sheet->setCellValue('A' . $row, 'Productos encontrados: ' . $totalProductosCalculado);
} else {
  $sheet->setCellValue('A' . $row, 'Productos: Todos los productos (' . $totalProductosCalculado . ' productos)');
}
$row += 2;

// RESUMEN EJECUTIVO
$sheet->setCellValue('A' . $row, 'RESUMEN EJECUTIVO');
$sheet->mergeCells('A' . $row . ':L' . $row);
$sheet->getStyle('A' . $row)->applyFromArray([
  'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
  'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '3498DB']],
  'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
]);
$row += 2;

// Cajas de métricas
$metricas = [
  ['label' => 'Total Productos', 'value' => $resumen['total_productos'] ?? $totalProductosCalculado, 'color' => 'E6F3FF'],
  ['label' => 'Total Ventas', 'value' => '$ ' . number_format($resumen['total_ventas_general'] ?? $totalVentasCalculado, 2), 'color' => 'E6FFE6'],
  ['label' => 'Total Costos', 'value' => '$ ' . number_format($resumen['costo_total_general'] ?? $totalCostosCalculado, 2), 'color' => 'FFE6E6'],
  ['label' => 'Cantidad Vendida', 'value' => ($resumen['cantidad_total_vendida'] ?? $cantidadTotalCalculada) . ' unidades', 'color' => 'FFF3E6'],
];

$col = 'A';
foreach ($metricas as $metrica) {
  $sheet->setCellValue($col . $row, $metrica['label']);
  $sheet->getStyle($col . $row)->applyFromArray([
    'font' => ['bold' => true, 'size' => 9],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $metrica['color']]],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
  ]);

  $sheet->setCellValue($col . ($row + 1), $metrica['value']);
  $sheet->getStyle($col . ($row + 1))->applyFromArray([
    'font' => ['bold' => true, 'size' => 12],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $metrica['color']]],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
  ]);

  $sheet->getColumnDimension($col)->setWidth(20);
  $col++;
}
$row += 3;

// Ganancias
$margen = 0;
if (($resumen['total_ventas_general'] ?? $totalVentasCalculado) > 0) {
  $margen = ((($resumen['ganancia_neta_general'] ?? $gananciaNetaCalculada) / ($resumen['total_ventas_general'] ?? $totalVentasCalculado)) * 100);
}

$sheet->setCellValue('A' . $row, 'Ganancia Bruta: $ ' . number_format($resumen['ganancia_bruta_general'] ?? $gananciaBrutaCalculada, 2));
$sheet->mergeCells('A' . $row . ':D' . $row);
$sheet->getStyle('A' . $row)->applyFromArray([
  'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '2E7D32']],
  'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C8FFC8']],
  'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
  'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
]);
$row++;

$sheet->setCellValue('A' . $row, 'Ganancia Neta: $ ' . number_format($resumen['ganancia_neta_general'] ?? $gananciaNetaCalculada, 2));
$sheet->mergeCells('A' . $row . ':D' . $row);
$sheet->getStyle('A' . $row)->applyFromArray([
  'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '1565C0']],
  'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'B4E0FF']],
  'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
  'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
]);
$row++;

$sheet->setCellValue('A' . $row, 'Margen de Ganancia: ' . number_format($margen, 2) . '%');
$sheet->mergeCells('A' . $row . ':D' . $row);
$sheet->getStyle('A' . $row)->applyFromArray([
  'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'F57C00']],
  'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF8B4']],
  'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
  'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
]);
$row += 3;

// VERIFICAR SI HAY DATOS
if (empty($ganancias)) {
  $sheet->setCellValue('A' . $row, '*** NO HAY DATOS DISPONIBLES PARA LOS FILTROS APLICADOS ***');
  $sheet->mergeCells('A' . $row . ':L' . $row);
  $sheet->getStyle('A' . $row)->applyFromArray([
    'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FF0000']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFE6E6']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
  ]);
  $row += 2;

  $sheet->setCellValue('A' . $row, 'RECOMENDACIONES:');
  $sheet->getStyle('A' . $row)->applyFromArray(['font' => ['bold' => true]]);
  $row++;
  $sheet->setCellValue('A' . $row, '• Verifique que las fechas de filtro sean correctas');
  $row++;
  $sheet->setCellValue('A' . $row, '• Asegúrese de tener ventas registradas en el período seleccionado');
  $row++;
  $sheet->setCellValue('A' . $row, '• Considere ampliar el rango de fechas para obtener más datos');
  $row++;
  $sheet->setCellValue('A' . $row, '• Si filtró por producto, verifique que el código sea correcto');
} else {
  // TABLA DETALLADA DE PRODUCTOS
  $sheet->setCellValue('A' . $row, 'DETALLE POR PRODUCTOS');
  $sheet->mergeCells('A' . $row . ':L' . $row);
  $sheet->getStyle('A' . $row)->applyFromArray([
    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4CAF50']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
  ]);
  $row += 2;

  // Headers de tabla
  $headers = ['Código', 'Producto', 'Categoría', 'Cant.', 'P.Compra', 'P.Venta', 'T.Ventas', 'Desc.', 'Costo Total', 'G.Bruta', 'G.Neta', 'Margen%'];
  $col = 'A';
  foreach ($headers as $header) {
    $sheet->setCellValue($col . $row, $header);
    $sheet->getStyle($col . $row)->applyFromArray([
      'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
      'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '5B9BD5']],
      'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
      'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
    ]);
    $col++;
  }
  $row++;

  // Datos
  $startDataRow = $row;
  foreach ($ganancias as $item) {
    $margenProducto = 0;
    if ($item['total_ventas'] > 0) {
      $margenProducto = (($item['ganancia_neta'] / $item['total_ventas']) * 100);
    }

    $sheet->setCellValue('A' . $row, $item['producto_codigo'] ?? 'N/A');
    $sheet->setCellValue('B' . $row, $item['producto_nombre'] ?? 'N/A');
    $sheet->setCellValue('C' . $row, $item['categoria_descripcion'] ?? 'N/A');
    $sheet->setCellValue('D' . $row, $item['cantidad_vendida'] ?? 0);
    $sheet->setCellValue('E' . $row, $item['producto_precio_compra'] ?? 0);
    $sheet->setCellValue('F' . $row, $item['producto_precio_venta'] ?? 0);
    $sheet->setCellValue('G' . $row, $item['total_ventas'] ?? 0);
    $sheet->setCellValue('H' . $row, $item['total_descuentos'] ?? 0);
    $sheet->setCellValue('I' . $row, $item['costo_total'] ?? 0);
    $sheet->setCellValue('J' . $row, $item['ganancia_bruta'] ?? 0);
    $sheet->setCellValue('K' . $row, $item['ganancia_neta'] ?? 0);
    $sheet->setCellValue('L' . $row, number_format($margenProducto, 2));

    // Aplicar formato de números
    $sheet->getStyle('E' . $row . ':K' . $row)->getNumberFormat()->setFormatCode('$#,##0.00');

    // Bordes y alineación
    $sheet->getStyle('A' . $row . ':L' . $row)->applyFromArray([
      'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
      'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
    ]);

    // Color alterno
    if (($row - $startDataRow) % 2 == 0) {
      $sheet->getStyle('A' . $row . ':L' . $row)->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setRGB('F5F5F5');
    }

    $row++;
  }

  $row += 2;

  // ESTADÍSTICAS DEL PERÍODO
  $sheet->setCellValue('A' . $row, 'ANÁLISIS POR PERÍODO');
  $sheet->mergeCells('A' . $row . ':L' . $row);
  $sheet->getStyle('A' . $row)->applyFromArray([
    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '9C27B0']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
  ]);
  $row += 2;

  // Calcular estadísticas
  $promedioVentas = $totalProductosCalculado > 0 ? ($totalVentasCalculado / $totalProductosCalculado) : 0;
  $promedioGanancia = $totalProductosCalculado > 0 ? ($gananciaNetaCalculada / $totalProductosCalculado) : 0;

  $sheet->setCellValue('A' . $row, 'Productos Analizados:');
  $sheet->setCellValue('B' . $row, $totalProductosCalculado);
  $sheet->getStyle('A' . $row)->applyFromArray(['font' => ['bold' => true]]);
  $row++;

  $sheet->setCellValue('A' . $row, 'Promedio Ventas por Producto:');
  $sheet->setCellValue('B' . $row, $promedioVentas);
  $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('$#,##0.00');
  $sheet->getStyle('A' . $row)->applyFromArray(['font' => ['bold' => true]]);
  $row++;

  $sheet->setCellValue('A' . $row, 'Promedio Ganancia por Producto:');
  $sheet->setCellValue('B' . $row, $promedioGanancia);
  $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('$#,##0.00');
  $sheet->getStyle('A' . $row)->applyFromArray(['font' => ['bold' => true]]);
  $row += 2;

  // PRODUCTOS DESTACADOS
  if (!empty($ganancias) && count($ganancias) > 0) {
    $mayorGanancia = $ganancias[0];
    $menorGanancia = $ganancias[0];

    foreach ($ganancias as $producto) {
      if ($producto['ganancia_neta'] > $mayorGanancia['ganancia_neta']) {
        $mayorGanancia = $producto;
      }
      if ($producto['ganancia_neta'] < $menorGanancia['ganancia_neta']) {
        $menorGanancia = $producto;
      }
    }

    $sheet->setCellValue('A' . $row, 'PRODUCTOS DESTACADOS');
    $sheet->mergeCells('A' . $row . ':D' . $row);
    $sheet->getStyle('A' . $row)->applyFromArray([
      'font' => ['bold' => true, 'size' => 11],
      'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E0E0E0']]
    ]);
    $row += 2;

    // Mayor ganancia
    $sheet->setCellValue('A' . $row, 'MAYOR GANANCIA:');
    $sheet->getStyle('A' . $row)->applyFromArray([
      'font' => ['bold' => true, 'color' => ['rgb' => '2E7D32']]
    ]);
    $row++;
    $sheet->setCellValue('A' . $row, 'Producto:');
    $sheet->setCellValue('B' . $row, $mayorGanancia['producto_codigo'] . ' - ' . $mayorGanancia['producto_nombre']);
    $sheet->mergeCells('B' . $row . ':E' . $row);
    $row++;
    $sheet->setCellValue('A' . $row, 'Ganancia:');
    $sheet->setCellValue('B' . $row, $mayorGanancia['ganancia_neta']);
    $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('$#,##0.00');
    $row++;
    $sheet->setCellValue('A' . $row, 'Unidades Vendidas:');
    $sheet->setCellValue('B' . $row, $mayorGanancia['cantidad_vendida']);
    $row += 2;

    // Menor ganancia
    $sheet->setCellValue('A' . $row, 'MENOR GANANCIA:');
    $sheet->getStyle('A' . $row)->applyFromArray([
      'font' => ['bold' => true, 'color' => ['rgb' => 'C62828']]
    ]);
    $row++;
    $sheet->setCellValue('A' . $row, 'Producto:');
    $sheet->setCellValue('B' . $row, $menorGanancia['producto_codigo'] . ' - ' . $menorGanancia['producto_nombre']);
    $sheet->mergeCells('B' . $row . ':E' . $row);
    $row++;
    $sheet->setCellValue('A' . $row, 'Ganancia:');
    $sheet->setCellValue('B' . $row, $menorGanancia['ganancia_neta']);
    $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('$#,##0.00');
    $row++;
    $sheet->setCellValue('A' . $row, 'Unidades Vendidas:');
    $sheet->setCellValue('B' . $row, $menorGanancia['cantidad_vendida']);
    $row += 2;

    // Rango de ganancias
    $sheet->setCellValue('A' . $row, 'Rango de Ganancias:');
    $sheet->setCellValue('B' . $row, '$' . number_format($menorGanancia['ganancia_neta'], 2) . ' - $' . number_format($mayorGanancia['ganancia_neta'], 2));
    $sheet->mergeCells('B' . $row . ':D' . $row);
    $sheet->getStyle('A' . $row)->applyFromArray(['font' => ['bold' => true]]);
    $row += 2;
  }

  // RECOMENDACIONES DE NEGOCIO
  $sheet->setCellValue('A' . $row, 'RECOMENDACIONES DE NEGOCIO');
  $sheet->mergeCells('A' . $row . ':L' . $row);
  $sheet->getStyle('A' . $row)->applyFromArray([
    'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FF9800']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
  ]);
  $row += 2;

  if ($gananciaNetaCalculada > 0) {
    $sheet->setCellValue('A' . $row, '• El negocio muestra ganancias positivas en este período');
  } else {
    $sheet->setCellValue('A' . $row, '• Revisar estrategias de precios y costos');
  }
  $sheet->mergeCells('A' . $row . ':L' . $row);
  $row++;
  $sheet->setCellValue('A' . $row, '• Enfocarse en productos con mayor margen de ganancia');
  $sheet->mergeCells('A' . $row . ':L' . $row);
  $row++;
  $sheet->setCellValue('A' . $row, '• Considerar promociones para productos de baja rotación');
  $sheet->mergeCells('A' . $row . ':L' . $row);
  $row++;
  $sheet->setCellValue('A' . $row, '• Monitorear regularmente el rendimiento por producto');
  $sheet->mergeCells('A' . $row . ':L' . $row);
  $row++;
  $sheet->setCellValue('A' . $row, '• Analizar la relación precio-calidad de productos');
  $sheet->mergeCells('A' . $row . ':L' . $row);
  $row += 2;

  // CONCLUSIONES FINALES
  $sheet->setCellValue('A' . $row, 'CONCLUSIONES FINALES');
  $sheet->mergeCells('A' . $row . ':L' . $row);
  $sheet->getStyle('A' . $row)->applyFromArray([
    'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '607D8B']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
  ]);
  $row += 2;

  $sheet->setCellValue('A' . $row, 'Fecha de Análisis:');
  $sheet->setCellValue('B' . $row, date('d/m/Y H:i:s'));
  $sheet->mergeCells('B' . $row . ':D' . $row);
  $sheet->getStyle('A' . $row)->applyFromArray(['font' => ['bold' => true]]);
  $row++;

  $sheet->setCellValue('A' . $row, 'Período Evaluado:');
  $periodoText = !empty($fechaDesde) && !empty($fechaHasta) ? $fechaDesde . ' al ' . $fechaHasta : 'Todos los registros';
  $sheet->setCellValue('B' . $row, $periodoText);
  $sheet->mergeCells('B' . $row . ':D' . $row);
  $sheet->getStyle('A' . $row)->applyFromArray(['font' => ['bold' => true]]);
  $row++;

  $sheet->setCellValue('A' . $row, 'Total de Ventas Analizadas:');
  $sheet->setCellValue('B' . $row, $totalVentasCalculado);
  $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('$#,##0.00');
  $sheet->getStyle('A' . $row)->applyFromArray(['font' => ['bold' => true]]);
  $row++;

  $sheet->setCellValue('A' . $row, 'Rentabilidad General:');
  $sheet->setCellValue('B' . $row, number_format($margen, 2) . '%');
  $sheet->getStyle('A' . $row)->applyFromArray(['font' => ['bold' => true]]);
  $row++;

  // Estado del negocio
  $sheet->setCellValue('A' . $row, 'Estado del Negocio:');
  $estadoNegocio = '';
  $colorEstado = '000000';

  if ($margen >= 40) {
    $estadoNegocio = 'EXCELENTE - Margen muy saludable';
    $colorEstado = '2E7D32';
  } elseif ($margen >= 25) {
    $estadoNegocio = 'BUENO - Margen aceptable';
    $colorEstado = '388E3C';
  } elseif ($margen >= 10) {
    $estadoNegocio = 'REGULAR - Revisar costos';
    $colorEstado = 'F57C00';
  } else {
    $estadoNegocio = 'CRÍTICO - Acción inmediata requerida';
    $colorEstado = 'C62828';
  }

  $sheet->setCellValue('B' . $row, $estadoNegocio);
  $sheet->mergeCells('B' . $row . ':E' . $row);
  $sheet->getStyle('A' . $row)->applyFromArray(['font' => ['bold' => true]]);
  $sheet->getStyle('B' . $row)->applyFromArray([
    'font' => ['bold' => true, 'color' => ['rgb' => $colorEstado]]
  ]);
}

// Ajustar anchos de columna
$sheet->getColumnDimension('A')->setWidth(35);
$sheet->getColumnDimension('B')->setWidth(35);
$sheet->getColumnDimension('C')->setWidth(20);
$sheet->getColumnDimension('D')->setWidth(10);
$sheet->getColumnDimension('E')->setWidth(12);
$sheet->getColumnDimension('F')->setWidth(12);
$sheet->getColumnDimension('G')->setWidth(12);
$sheet->getColumnDimension('H')->setWidth(10);
$sheet->getColumnDimension('I')->setWidth(12);
$sheet->getColumnDimension('J')->setWidth(12);
$sheet->getColumnDimension('K')->setWidth(12);
$sheet->getColumnDimension('L')->setWidth(10);

// Configurar headers para descarga
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
