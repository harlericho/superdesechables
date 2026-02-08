<?php
date_default_timezone_set('America/Guayaquil');
include_once '../../config/db.php';

class CierreCajaModel
{
  /**
   * Obtener movimientos de caja del día actual
   */
  public static function obtenerMovimientosDia($fecha = null)
  {
    try {
      $fechaConsulta = $fecha ? $fecha : date('Y-m-d');
      $sql = "SELECT 
                mov_tipo,
                SUM(mov_monto) as total_monto,
                COUNT(*) as cantidad_movimientos
              FROM tbl_movimiento_caja 
              WHERE mov_fecharegistro = :fecha 
              GROUP BY mov_tipo";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(':fecha', $fechaConsulta, PDO::PARAM_STR);
      $query->execute();
      return $query->fetchAll();
    } catch (PDOException $e) {
      error_log("Error en obtenerMovimientosDia: " . $e->getMessage());
    }
  }

  /**
   * Obtener ventas por tipo de pago del día
   */
  public static function obtenerVentasPorTipoPago($fecha = null)
  {
    try {
      $fechaConsulta = $fecha ? $fecha : date('Y-m-d');
      $sql = "SELECT 
                tc.tipo_comp_descripcion as tipo_pago,
                COUNT(f.factura_id) as cantidad_facturas,
                SUM(f.factura_subtotal) as total_subtotal,
                SUM(f.factura_impuesto) as total_impuesto,
                SUM(f.factura_total) as total_ventas
              FROM tbl_factura f
              INNER JOIN tbl_tipo_comprobante tc ON f.tipo_comp_id = tc.tipo_comp_id
              WHERE DATE(f.factura_fecha) = :fecha AND f.factura_estado = 1
              GROUP BY tc.tipo_comp_id, tc.tipo_comp_descripcion";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(':fecha', $fechaConsulta, PDO::PARAM_STR);
      $query->execute();
      return $query->fetchAll();
    } catch (PDOException $e) {
      error_log("Error en obtenerVentasPorTipoPago: " . $e->getMessage());
      return [];
    }
  }

  /**
   * Obtener resumen de ventas con IVA
   */
  public static function obtenerResumenVentasIVA($fecha = null)
  {
    try {
      $fechaConsulta = $fecha ? $fecha : date('Y-m-d');
      $sql = "SELECT 
                i.impuesto_porcentaje,
                i.impuesto_nombre,
                SUM(f.factura_subtotal) as subtotal,
                SUM(f.factura_impuesto) as impuesto,
                SUM(f.factura_total) as total,
                COUNT(f.factura_id) as cantidad_facturas
              FROM tbl_factura f
              LEFT JOIN tbl_detalle d ON f.factura_id = d.factura_id
              LEFT JOIN tbl_producto p ON d.producto_id = p.producto_id
              LEFT JOIN tbl_impuesto i ON i.impuesto_activo = 1
              WHERE DATE(f.factura_fecha) = :fecha AND f.factura_estado = 1
              GROUP BY i.impuesto_id, i.impuesto_porcentaje, i.impuesto_nombre";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(':fecha', $fechaConsulta, PDO::PARAM_STR);
      $query->execute();
      return $query->fetchAll();
    } catch (PDOException $e) {
      error_log("Error en obtenerResumenVentasIVA: " . $e->getMessage());
      return [];
    }
  }

  /**
   * Calcular totales del día para cierre
   */
  public static function calcularTotalesDia($fecha = null)
  {
    try {
      $fechaConsulta = $fecha ? $fecha : date('Y-m-d');

      // Consulta separada para movimientos de caja
      $sqlMovimientos = "SELECT 
                COALESCE(SUM(CASE WHEN mov_tipo = 'INGRESO' THEN mov_monto ELSE 0 END), 0) as total_ingresos_mov,
                COALESCE(SUM(CASE WHEN mov_tipo = 'EGRESO' THEN mov_monto ELSE 0 END), 0) as total_egresos_mov
              FROM tbl_movimiento_caja
              WHERE mov_fecharegistro = :fecha";

      $queryMov = Db::dbConnection()->prepare($sqlMovimientos);
      $queryMov->bindParam(':fecha', $fechaConsulta, PDO::PARAM_STR);
      $queryMov->execute();
      $movimientos = $queryMov->fetch();

      // Consulta separada para ventas
      $sqlVentas = "SELECT 
                -- Ventas por tipo de pago
                COALESCE(SUM(CASE WHEN tc.tipo_comp_descripcion = 'EFECTIVO' THEN f.factura_total ELSE 0 END), 0) as ventas_efectivo,
                COALESCE(SUM(CASE WHEN tc.tipo_comp_descripcion = 'TRANSFERENCIA' THEN f.factura_total ELSE 0 END), 0) as ventas_transferencia,
                COALESCE(SUM(CASE WHEN tc.tipo_comp_descripcion = 'CHEQUE' THEN f.factura_total ELSE 0 END), 0) as ventas_cheque,
                COALESCE(SUM(CASE WHEN tc.tipo_comp_descripcion NOT IN ('EFECTIVO','TRANSFERENCIA','CHEQUE') THEN f.factura_total ELSE 0 END), 0) as ventas_otros,
                
                -- Totales de ventas
                COALESCE(SUM(f.factura_subtotal), 0) as total_subtotal,
                COALESCE(SUM(f.factura_impuesto), 0) as total_impuesto,
                COALESCE(SUM(f.factura_total), 0) as total_ventas,
                
                -- Ventas sin IVA (cuando el impuesto es 0)
                COALESCE(SUM(CASE WHEN f.factura_impuesto = 0 THEN f.factura_total ELSE 0 END), 0) as ventas_sin_iva,
                -- Ventas con IVA (cuando el impuesto es mayor a 0)
                COALESCE(SUM(CASE WHEN f.factura_impuesto > 0 THEN f.factura_total ELSE 0 END), 0) as ventas_con_iva
                
              FROM tbl_factura f
              LEFT JOIN tbl_tipo_comprobante tc ON f.tipo_comp_id = tc.tipo_comp_id
              WHERE DATE(f.factura_fecha) = :fecha AND f.factura_estado = 1";

      $queryVentas = Db::dbConnection()->prepare($sqlVentas);
      $queryVentas->bindParam(':fecha', $fechaConsulta, PDO::PARAM_STR);
      $queryVentas->execute();
      $ventas = $queryVentas->fetch();

      // Combinar resultados
      return array_merge($movimientos ?: [], $ventas ?: []);
    } catch (PDOException $e) {
      error_log("Error en calcularTotalesDia: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Verificar si ya existe un cierre para la fecha
   */
  public static function existeCierreFecha($fecha)
  {
    try {
      $sql = "SELECT COUNT(*) as existe FROM tbl_cierre_caja WHERE cierre_fecha = :fecha AND cierre_estado = 1";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(':fecha', $fecha, PDO::PARAM_STR);
      $query->execute();
      $result = $query->fetch();
      return $result['existe'] > 0;
    } catch (PDOException $e) {
      error_log("Error en existeCierreFecha: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Guardar cierre de caja
   */
  public static function guardarCierreCaja($data)
  {
    try {
      $sql = "INSERT INTO tbl_cierre_caja (
                cierre_fecha,
                cierre_hora,
                cierre_saldo_inicial,
                cierre_ingresos_efectivo,
                cierre_ingresos_transferencia,
                cierre_ingresos_cheque,
                cierre_ingresos_otros,
                cierre_total_ingresos,
                cierre_total_egresos,
                cierre_ventas_sin_iva,
                cierre_ventas_con_iva,
                cierre_iva_cobrado,
                cierre_saldo_final,
                cierre_observaciones,
                cierre_usuario_id
              ) VALUES (
                :fecha, :hora, :saldo_inicial, :ing_efectivo, :ing_transferencia,
                :ing_cheque, :ing_otros, :total_ingresos, :total_egresos,
                :ventas_sin_iva, :ventas_con_iva, :iva_cobrado, :saldo_final,
                :observaciones, :usuario_id
              )";

      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(':fecha', $data['cierre_fecha'], PDO::PARAM_STR);
      $query->bindParam(':hora', $data['cierre_hora'], PDO::PARAM_STR);
      $query->bindParam(':saldo_inicial', $data['cierre_saldo_inicial'], PDO::PARAM_STR);
      $query->bindParam(':ing_efectivo', $data['cierre_ingresos_efectivo'], PDO::PARAM_STR);
      $query->bindParam(':ing_transferencia', $data['cierre_ingresos_transferencia'], PDO::PARAM_STR);
      $query->bindParam(':ing_cheque', $data['cierre_ingresos_cheque'], PDO::PARAM_STR);
      $query->bindParam(':ing_otros', $data['cierre_ingresos_otros'], PDO::PARAM_STR);
      $query->bindParam(':total_ingresos', $data['cierre_total_ingresos'], PDO::PARAM_STR);
      $query->bindParam(':total_egresos', $data['cierre_total_egresos'], PDO::PARAM_STR);
      $query->bindParam(':ventas_sin_iva', $data['cierre_ventas_sin_iva'], PDO::PARAM_STR);
      $query->bindParam(':ventas_con_iva', $data['cierre_ventas_con_iva'], PDO::PARAM_STR);
      $query->bindParam(':iva_cobrado', $data['cierre_iva_cobrado'], PDO::PARAM_STR);
      $query->bindParam(':saldo_final', $data['cierre_saldo_final'], PDO::PARAM_STR);
      $query->bindParam(':observaciones', $data['cierre_observaciones'], PDO::PARAM_STR);
      $query->bindParam(':usuario_id', $data['cierre_usuario_id'], PDO::PARAM_INT);

      if ($query->execute()) {
        $insertId = Db::dbConnection()->lastInsertId();
        return $insertId > 0 ? intval($insertId) : 1; // Retorna el ID o 1 si es exitoso
      } else {
        return false;
      }
    } catch (PDOException $e) {
      error_log("Error en guardarCierreCaja: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Guardar detalles del cierre de caja
   */
  public static function guardarDetalleCierre($detalles)
  {
    try {
      if (empty($detalles)) {
        return true; // Si no hay detalles, se considera exitoso
      }

      $sql = "INSERT INTO tbl_detalle_cierre_caja (
                cierre_id, detalle_tipo, detalle_concepto, detalle_monto,
                detalle_tipo_pago, detalle_porcentaje_iva
              ) VALUES (
                :cierre_id, :tipo, :concepto, :monto, :tipo_pago, :porcentaje_iva
              )";

      $query = Db::dbConnection()->prepare($sql);

      foreach ($detalles as $detalle) {
        $query->bindParam(':cierre_id', $detalle['cierre_id'], PDO::PARAM_INT);
        $query->bindParam(':tipo', $detalle['detalle_tipo'], PDO::PARAM_STR);
        $query->bindParam(':concepto', $detalle['detalle_concepto'], PDO::PARAM_STR);
        $query->bindParam(':monto', $detalle['detalle_monto'], PDO::PARAM_STR);
        $query->bindParam(':tipo_pago', $detalle['detalle_tipo_pago'], PDO::PARAM_STR);
        $query->bindParam(':porcentaje_iva', $detalle['detalle_porcentaje_iva'], PDO::PARAM_STR);
        $query->execute();
      }

      return true;
    } catch (PDOException $e) {
      error_log("Error en guardarDetalleCierre: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Obtener historial de cierres de caja
   */
  public static function obtenerHistorialCierres($limite = 30)
  {
    try {
      $sql = "SELECT 
                cc.*,
                u.usuario_nombres
              FROM tbl_cierre_caja cc
              INNER JOIN tbl_usuario u ON cc.cierre_usuario_id = u.usuario_id
              WHERE cc.cierre_estado = 1
              ORDER BY cc.cierre_fecha DESC, cc.cierre_hora DESC
              LIMIT :limite";

      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(':limite', $limite, PDO::PARAM_INT);
      $query->execute();
      return $query->fetchAll();
    } catch (PDOException $e) {
      error_log("Error en obtenerHistorialCierres: " . $e->getMessage());
    }
  }

  /**
   * Obtener saldo inicial (último cierre + ingresos - egresos)
   */
  public static function calcularSaldoInicial($fecha)
  {
    try {
      // Obtener el último cierre antes de la fecha actual
      $sql = "SELECT cierre_saldo_final 
              FROM tbl_cierre_caja 
              WHERE cierre_fecha < :fecha AND cierre_estado = 1
              ORDER BY cierre_fecha DESC, cierre_hora DESC 
              LIMIT 1";

      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(':fecha', $fecha, PDO::PARAM_STR);
      $query->execute();
      $ultimoCierre = $query->fetch();

      return $ultimoCierre ? $ultimoCierre['cierre_saldo_final'] : 0.00;
    } catch (PDOException $e) {
      error_log("Error en calcularSaldoInicial: " . $e->getMessage());
      return 0.00;
    }
  }
}
