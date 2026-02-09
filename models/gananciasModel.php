<?php
include_once '../../config/db.php';

class GananciasModel
{
  /**
   * Obtiene las ganancias generales o filtradas por fechas y/o producto
   */
  public static function obtenerGanancias($fechaDesde = null, $fechaHasta = null, $codigoProducto = null)
  {
    try {
      // Log para debugging
      error_log("=== GANANCIAS MODEL ===");
      error_log("Params - Fecha desde: " . ($fechaDesde ?? 'null'));
      error_log("Params - Fecha hasta: " . ($fechaHasta ?? 'null'));
      error_log("Params - Código producto: " . ($codigoProducto ?? 'null'));

      // Consulta ultra-simple cuando hay filtro de producto
      if ($codigoProducto) {
        $sql = "SELECT 
                          p.producto_id,
                          p.producto_codigo,
                          p.producto_nombre,
                          p.producto_precio_compra,
                          p.producto_precio_venta,
                          COALESCE(c.categoria_descripcion, 'Sin categoría') as categoria_descripcion,
                          'Sin proveedor' as proveedor_nombre,
                          SUM(d.detalle_cantidad) as cantidad_vendida,
                          SUM(d.detalle_total) as total_ventas,
                          0 as total_descuentos,
                          (SUM(d.detalle_cantidad) * p.producto_precio_compra) as costo_total,
                          (SUM(d.detalle_total) - (SUM(d.detalle_cantidad) * p.producto_precio_compra)) as ganancia_bruta,
                          (SUM(d.detalle_total) - (SUM(d.detalle_cantidad) * p.producto_precio_compra)) as ganancia_neta,
                          ROUND(((SUM(d.detalle_total) - (SUM(d.detalle_cantidad) * p.producto_precio_compra)) / SUM(d.detalle_total)) * 100, 2) as margen_porcentaje
                      FROM tbl_detalle d
                      INNER JOIN tbl_producto p ON d.producto_id = p.producto_id
                      INNER JOIN tbl_categoria c ON p.categoria_id = c.categoria_id
                      INNER JOIN tbl_factura f ON d.factura_id = f.factura_id
                      WHERE f.factura_estado = 1 
                        AND d.detalle_estado = 1 
                        AND p.producto_codigo = :codigo_producto";
        $params = [':codigo_producto' => $codigoProducto];

        // Agregar filtro por fechas si están presentes
        if ($fechaDesde && $fechaHasta) {
          $sql .= " AND f.factura_fecha BETWEEN :fecha_desde AND :fecha_hasta";
          $params[':fecha_desde'] = $fechaDesde;
          $params[':fecha_hasta'] = $fechaHasta;
        }

        $sql .= " GROUP BY p.producto_id";
      } else {
        // Consulta completa cuando no hay filtro
        $sql = "SELECT 
                          p.producto_id,
                          p.producto_codigo,
                          p.producto_nombre,
                          p.producto_precio_compra,
                          p.producto_precio_venta,
                          COALESCE(c.categoria_descripcion, 'Sin categoría') as categoria_descripcion,
                          COALESCE(pr.proveedor_nombre, 'Sin proveedor') as proveedor_nombre,
                          SUM(d.detalle_cantidad) as cantidad_vendida,
                          SUM(d.detalle_total) as total_ventas,
                          COALESCE(SUM(d.detalle_descuento), 0) as total_descuentos,
                          (SUM(d.detalle_cantidad) * p.producto_precio_compra) as costo_total,
                          (SUM(d.detalle_total) - (SUM(d.detalle_cantidad) * p.producto_precio_compra)) as ganancia_bruta,
                          (SUM(d.detalle_total) - (SUM(d.detalle_cantidad) * p.producto_precio_compra) - COALESCE(SUM(d.detalle_descuento), 0)) as ganancia_neta,
                          CASE 
                            WHEN SUM(d.detalle_total) > 0 
                            THEN ROUND(((SUM(d.detalle_total) - (SUM(d.detalle_cantidad) * p.producto_precio_compra) - COALESCE(SUM(d.detalle_descuento), 0)) / SUM(d.detalle_total)) * 100, 2)
                            ELSE 0 
                          END as margen_porcentaje
                      FROM tbl_detalle d
                      INNER JOIN tbl_producto p ON d.producto_id = p.producto_id
                      INNER JOIN tbl_factura f ON d.factura_id = f.factura_id
                      LEFT JOIN tbl_categoria c ON p.categoria_id = c.categoria_id
                      LEFT JOIN tbl_proveedor pr ON p.proveedor_id = pr.proveedor_id
                      WHERE f.factura_estado = 1 AND d.detalle_estado = 1";
        $params = [];

        // Agregar filtro por fechas
        if ($fechaDesde && $fechaHasta) {
          $sql .= " AND f.factura_fecha BETWEEN :fecha_desde AND :fecha_hasta";
          $params[':fecha_desde'] = $fechaDesde;
          $params[':fecha_hasta'] = $fechaHasta;
        }

        $sql .= " GROUP BY p.producto_id, p.producto_codigo, p.producto_nombre, 
                                  p.producto_precio_compra, p.producto_precio_venta,
                                  c.categoria_descripcion, pr.proveedor_nombre
                        HAVING SUM(d.detalle_cantidad) > 0
                        ORDER BY ganancia_neta DESC";
      }

      $query = Db::dbConnection()->prepare($sql);
      foreach ($params as $param => $value) {
        $query->bindValue($param, $value);
      }
      $query->execute();
      $result = $query->fetchAll(PDO::FETCH_ASSOC);

      return $result;
    } catch (PDOException $e) {
      return [];
    }
  }

  /**
   * Función de prueba simple para verificar datos básicos
   */
  public static function pruebaSimple()
  {
    try {
      // Consulta básica sin GROUP BY para verificar datos
      $sql = "SELECT 
                p.producto_codigo,
                p.producto_nombre, 
                d.detalle_cantidad,
                d.detalle_total,
                p.producto_precio_compra,
                p.producto_precio_venta
              FROM tbl_producto p
              INNER JOIN tbl_detalle d ON p.producto_id = d.producto_id
              INNER JOIN tbl_factura f ON d.factura_id = f.factura_id
              WHERE f.factura_estado = 1 AND d.detalle_estado = 1
              LIMIT 10";

      $query = Db::dbConnection()->prepare($sql);
      $query->execute();
      $result = $query->fetchAll(PDO::FETCH_ASSOC);
      return $result;
    } catch (PDOException $e) {
      return [];
    }
  }

  /**
   * Consulta más simple para ganancias (diagnóstico)
   */
  public static function obtenerGananciasSimple()
  {
    try {
      $sql = "SELECT 
                p.producto_codigo,
                p.producto_nombre,
                SUM(d.detalle_cantidad) as cantidad_vendida,
                SUM(d.detalle_total) as total_ventas,
                p.producto_precio_compra,
                p.producto_precio_venta,
                c.categoria_descripcion as categoria_descripcion
              FROM tbl_detalle d
              INNER JOIN tbl_producto p ON d.producto_id = p.producto_id
              INNER JOIN tbl_categoria c ON p.categoria_id = c.categoria_id
              INNER JOIN tbl_factura f ON d.factura_id = f.factura_id
              WHERE f.factura_estado = 1 AND d.detalle_estado = 1
              GROUP BY p.producto_id, p.producto_codigo, p.producto_nombre, p.producto_precio_compra, p.producto_precio_venta
              ORDER BY total_ventas DESC";

      $query = Db::dbConnection()->prepare($sql);
      $query->execute();
      $result = $query->fetchAll(PDO::FETCH_ASSOC);
      return $result;
    } catch (PDOException $e) {
      return [];
    }
  }

  /**
   * Obtiene el resumen general de ganancias
   */
  public static function obtenerResumenGanancias($fechaDesde = null, $fechaHasta = null, $codigoProducto = null)
  {
    try {
      $sql = "SELECT 
                        COUNT(DISTINCT p.producto_id) as total_productos,
                        SUM(d.detalle_cantidad) as cantidad_total_vendida,
                        SUM(d.detalle_total) as total_ventas_general,
                        SUM(d.detalle_descuento) as total_descuentos_general,
                        SUM(d.detalle_cantidad * p.producto_precio_compra) as costo_total_general,
                        SUM(d.detalle_total - (d.detalle_cantidad * p.producto_precio_compra)) as ganancia_bruta_general,
                        SUM(d.detalle_total - (d.detalle_cantidad * p.producto_precio_compra) - d.detalle_descuento) as ganancia_neta_general
                    FROM tbl_producto p
                    INNER JOIN tbl_detalle d ON p.producto_id = d.producto_id
                    INNER JOIN tbl_factura f ON d.factura_id = f.factura_id
                    WHERE f.factura_estado = 1 AND d.detalle_estado = 1";

      $params = [];

      // Agregar filtros si existen
      if ($fechaDesde && $fechaHasta) {
        $sql .= " AND f.factura_fecha BETWEEN :fecha_desde AND :fecha_hasta";
        $params[':fecha_desde'] = $fechaDesde;
        $params[':fecha_hasta'] = $fechaHasta;
      }

      if ($codigoProducto) {
        $sql .= " AND p.producto_codigo = :codigo_producto";
        $params[':codigo_producto'] = $codigoProducto;
      }

      $query = Db::dbConnection()->prepare($sql);
      foreach ($params as $param => $value) {
        $query->bindValue($param, $value);
      }
      $query->execute();
      return $query->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      return [];
    }
  }



  /**
   * Obtiene la lista de códigos de productos para el filtro
   */
  public static function obtenerProductosParaFiltro()
  {
    try {
      $sql = "SELECT producto_codigo, producto_nombre FROM tbl_producto 
                   WHERE producto_estado = 1 
                   ORDER BY producto_nombre";
      $query = Db::dbConnection()->prepare($sql);
      $query->execute();
      return $query->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      return [];
    }
  }

  /**
   * Obtiene ganancias por periodo (diario, semanal, mensual)
   */
  public static function obtenerGananciasPorPeriodo($periodo = 'diario', $fechaDesde = null, $fechaHasta = null)
  {
    try {
      $formatoFecha = '';
      switch ($periodo) {
        case 'mensual':
          $formatoFecha = 'DATE_FORMAT(f.factura_fecha, "%Y-%m")';
          break;
        case 'semanal':
          $formatoFecha = 'YEARWEEK(f.factura_fecha)';
          break;
        default: // diario
          $formatoFecha = 'DATE(f.factura_fecha)';
          break;
      }

      $sql = "SELECT 
                        $formatoFecha as periodo,
                        COUNT(DISTINCT d.detalle_id) as total_ventas,
                        SUM(d.detalle_cantidad) as cantidad_vendida,
                        SUM(d.detalle_total) as total_ingresos,
                        SUM(d.detalle_descuento) as total_descuentos,
                        SUM(d.detalle_cantidad * p.producto_precio_compra) as total_costos,
                        SUM(d.detalle_total - (d.detalle_cantidad * p.producto_precio_compra) - d.detalle_descuento) as ganancia_neta
                    FROM tbl_producto p
                    INNER JOIN tbl_detalle d ON p.producto_id = d.producto_id
                    INNER JOIN tbl_factura f ON d.factura_id = f.factura_id
                    WHERE f.factura_estado = 1 AND d.detalle_estado = 1";

      $params = [];

      if ($fechaDesde && $fechaHasta) {
        $sql .= " AND f.factura_fecha BETWEEN :fecha_desde AND :fecha_hasta";
        $params[':fecha_desde'] = $fechaDesde;
        $params[':fecha_hasta'] = $fechaHasta;
      }

      $sql .= " GROUP BY $formatoFecha ORDER BY periodo DESC";

      $query = Db::dbConnection()->prepare($sql);
      foreach ($params as $param => $value) {
        $query->bindValue($param, $value);
      }
      $query->execute();
      return $query->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      return [];
    }
  }

  /**
   * Obtiene solo el total de ganancias netas para el dashboard
   */
  public static function obtenerTotalGanancias()
  {
    try {
      $sql = "SELECT 
                SUM(d.detalle_total - (d.detalle_cantidad * p.producto_precio_compra)) as total_ganancias
              FROM tbl_detalle d
              INNER JOIN tbl_producto p ON d.producto_id = p.producto_id
              INNER JOIN tbl_factura f ON d.factura_id = f.factura_id
              WHERE f.factura_estado = 1 AND d.detalle_estado = 1";

      $query = Db::dbConnection()->prepare($sql);
      $query->execute();
      $result = $query->fetch(PDO::FETCH_ASSOC);

      return [
        'totalGanancias' => $result['total_ganancias'] ? round($result['total_ganancias'], 2) : 0
      ];
    } catch (PDOException $e) {
      return ['totalGanancias' => 0];
    }
  }

  /**
   * Devuelve el reporte mensual para un año
   */
  public static function getReporteMensual($anio)
  {
    $fechaDesde = $anio . '-01-01';
    $fechaHasta = $anio . '-12-31';
    $datos = self::obtenerGananciasPorPeriodo('mensual', $fechaDesde, $fechaHasta);
    $meses = [
      '01' => 'Enero',
      '02' => 'Feb',
      '03' => 'Mar',
      '04' => 'Abr',
      '05' => 'May',
      '06' => 'Jun',
      '07' => 'Jul',
      '08' => 'Ago',
      '09' => 'Sep',
      '10' => 'Oct',
      '11' => 'Nov',
      '12' => 'Dic'
    ];
    $reporte = [];
    foreach ($datos as $item) {
      $periodo = $item['periodo']; // Ejemplo: 2026-02
      $mesNum = substr($periodo, 5, 2);
      $reporte[] = [
        'mes' => $meses[$mesNum] ?? $mesNum,
        'ventas' => $item['total_ventas'],
        'cantidad_vendida' => $item['cantidad_vendida'],
        'ingresos' => $item['total_ingresos'],
        'descuentos' => $item['total_descuentos'],
        'costos' => $item['total_costos'],
        'ganancia_neta' => $item['ganancia_neta'],
      ];
    }
    return $reporte;
  }
}
