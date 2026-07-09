<?php
include_once '../../config/db.php';

class FacturaModel
{
  public static function obtenerFacturasActivas($email)
  {
    try {
      $sql = "SELECT f.factura_id, f.factura_num_comprobante, f.factura_fecha, f.factura_impuesto, 
                     f.factura_subtotal, f.factura_total, f.factura_estado,f.factura_descuento_global,
                     c.cliente_dni, c.cliente_nombres, c.cliente_apellidos, c.cliente_email,
                     u.usuario_nombres, u.usuario_email,
                     t.tipo_comp_descripcion,
                     fe.fe_clave_acceso, fe.fe_estado_sri
            FROM tbl_factura f
            INNER JOIN tbl_cliente c ON f.cliente_id = c.cliente_id
            INNER JOIN tbl_usuario u ON f.usuario_id = u.usuario_id
            INNER JOIN tbl_tipo_comprobante t ON f.tipo_comp_id = t.tipo_comp_id
            LEFT JOIN tbl_factura_electronica fe ON f.factura_id = fe.factura_id
            WHERE f.factura_estado = '1' AND u.usuario_email=:usuario_email 
            ORDER BY f.factura_fecha DESC";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(":usuario_email", $email, PDO::PARAM_STR);
      $query->execute();
      return $query->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
  public static function obtenerFacturasActivasGeneral()
  {
    try {
      $sql = "SELECT f.factura_id, f.factura_num_comprobante, f.factura_fecha, f.factura_impuesto, 
                     f.factura_subtotal, f.factura_total, f.factura_estado,f.factura_descuento_global,
                     c.cliente_dni, c.cliente_nombres, c.cliente_apellidos, c.cliente_email,
                     u.usuario_nombres, u.usuario_email,
                     t.tipo_comp_descripcion,
                     fe.fe_clave_acceso, fe.fe_estado_sri
            FROM tbl_factura f
            INNER JOIN tbl_cliente c ON f.cliente_id = c.cliente_id
            INNER JOIN tbl_usuario u ON f.usuario_id = u.usuario_id
            INNER JOIN tbl_tipo_comprobante t ON f.tipo_comp_id = t.tipo_comp_id
            LEFT JOIN tbl_factura_electronica fe ON f.factura_id = fe.factura_id
            WHERE f.factura_estado = '1' 
            ORDER BY f.factura_id DESC";
      $query = Db::dbConnection()->prepare($sql);
      $query->execute();
      return $query->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
  public static function obtenerFacturasInactivas($email)
  {
    try {
      $sql = "SELECT * FROM tbl_factura f
            INNER JOIN tbl_cliente c ON f.cliente_id = c.cliente_id
            INNER JOIN tbl_usuario u ON f.usuario_id = u.usuario_id
            INNER JOIN tbl_tipo_comprobante t ON f.tipo_comp_id = t.tipo_comp_id
            WHERE f.factura_estado = '0' AND u.usuario_email=:usuario_email";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(":usuario_email", $email, PDO::PARAM_STR);
      $query->execute();
      return $query->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
  public static function obtenerFacturasInactivasGeneral()
  {
    try {
      $sql = "SELECT * FROM tbl_factura f
            INNER JOIN tbl_cliente c ON f.cliente_id = c.cliente_id
            INNER JOIN tbl_usuario u ON f.usuario_id = u.usuario_id
            INNER JOIN tbl_tipo_comprobante t ON f.tipo_comp_id = t.tipo_comp_id
            WHERE f.factura_estado = '0' order by f.factura_id desc";
      $query = Db::dbConnection()->prepare($sql);
      $query->execute();
      return $query->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
  public static function guardarFactura($data)
  {
    try {
      $sql = "INSERT INTO tbl_factura (factura_num_comprobante,
        factura_fecha, factura_subtotal, factura_descuento_global, factura_descuento_global_porcentaje, factura_impuesto, factura_incluye_iva, factura_iva, factura_total, cliente_id, usuario_id, tipo_comp_id)
        VALUES (:factura_num_comprobante, :factura_fecha, :factura_subtotal, :factura_descuento_global, :factura_descuento_global_porcentaje,
        :factura_impuesto, :factura_incluye_iva, :factura_iva, :factura_total, :cliente_id, :usuario_id, :tipo_comp_id)";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(":factura_num_comprobante", $data["factura_num_comprobante"], PDO::PARAM_STR);
      $query->bindParam(":factura_fecha", $data["factura_fecha"], PDO::PARAM_STR);
      $query->bindParam(":factura_subtotal", $data["factura_subtotal"], PDO::PARAM_STR);
      $query->bindParam(":factura_descuento_global", $data["factura_descuento_global"], PDO::PARAM_STR);
      $query->bindParam(":factura_descuento_global_porcentaje", $data["factura_descuento_global_porcentaje"], PDO::PARAM_STR);
      $query->bindParam(":factura_impuesto", $data["factura_impuesto"], PDO::PARAM_STR);
      $query->bindParam(":factura_incluye_iva", $data["factura_incluye_iva"], PDO::PARAM_INT);
      $query->bindParam(":factura_iva", $data["factura_iva"], PDO::PARAM_STR);
      $query->bindParam(":factura_total", $data["factura_total"], PDO::PARAM_STR);
      $query->bindParam(":cliente_id", $data["cliente_id"], PDO::PARAM_INT);
      $query->bindParam(":usuario_id", $data["usuario_id"], PDO::PARAM_INT);
      $query->bindParam(":tipo_comp_id", $data["tipo_comp_id"], PDO::PARAM_INT);
      $query->execute();
      return true;
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
  public static function maximoFactura()
  {
    try {
      $sql = "SELECT MAX(factura_id) AS factura_id FROM tbl_factura";
      $query = Db::dbConnection()->prepare($sql);
      $query->execute();
      return $query->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }

  /**
   * Obtener factura por ID con datos del cliente
   */
  public static function obtenerFacturaPorId($facturaId)
  {
    try {
      $sql = "SELECT f.*, 
                     c.cliente_dni, c.cliente_nombres, c.cliente_apellidos,
                     c.cliente_direccion, c.cliente_email, c.cliente_telefono,
                     c.cliente_tipo_identificacion,
                     u.usuario_nombres, u.usuario_email as usuario_email
              FROM tbl_factura f
              LEFT JOIN tbl_cliente c ON f.cliente_id = c.cliente_id
              LEFT JOIN tbl_usuario u ON f.usuario_id = u.usuario_id
              WHERE f.factura_id = :factura_id";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(":factura_id", $facturaId, PDO::PARAM_INT);
      $query->execute();
      return $query->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      error_log("Error al obtener factura: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Actualizar factura con datos de facturación electrónica
   */
  public static function actualizarFacturaElectronica($facturaId, $claveAcceso, $estadoSRI)
  {
    try {
      $sql = "UPDATE tbl_factura SET 
              factura_electronica = 1,
              factura_clave_acceso = :clave_acceso,
              factura_estado_sri = :estado_sri
              WHERE factura_id = :factura_id";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(":clave_acceso", $claveAcceso, PDO::PARAM_STR);
      $query->bindParam(":estado_sri", $estadoSRI, PDO::PARAM_STR);
      $query->bindParam(":factura_id", $facturaId, PDO::PARAM_INT);
      return $query->execute();
    } catch (PDOException $e) {
      error_log("Error al actualizar factura electrónica: " . $e->getMessage());
      return false;
    }
  }

  public static function inactivarFactura($id)
  {
    try {
      $sql = "UPDATE tbl_factura SET factura_estado = 0 WHERE factura_id = :id";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(":id", $id, PDO::PARAM_INT);
      $query->execute();
      return true;
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
  public static function existeFacturaNumComprobante($factura_num_comprobante)
  {
    try {
      $sql = "SELECT * FROM tbl_factura WHERE factura_num_comprobante = :factura_num_comprobante";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(":factura_num_comprobante", $factura_num_comprobante, PDO::PARAM_STR);
      $query->execute();
      return $query->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
  public static function contarFacturasActivas($email)
  {
    try {
      $sql = "SELECT COUNT(*) AS numFacturasA FROM tbl_factura f
                INNER JOIN tbl_usuario u ON f.usuario_id = u.usuario_id
            WHERE f.factura_estado = '1' AND u.usuario_email=:usuario_email";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(":usuario_email", $email, PDO::PARAM_STR);
      $query->execute();
      return $query->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
  public static function contarFacturasActivasGeneral()
  {
    try {
      $sql = "SELECT COUNT(*) AS numFacturasA FROM tbl_factura
            WHERE factura_estado = '1'";
      $query = Db::dbConnection()->prepare($sql);
      $query->execute();
      return $query->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
  public static function contarFacturasInactivas($email)
  {
    try {
      $sql = "SELECT COUNT(*) as numFacturasI FROM tbl_factura f
          INNER JOIN tbl_usuario u ON f.usuario_id = u.usuario_id
            WHERE f.factura_estado = '0' AND u.usuario_email=:usuario_email";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(":usuario_email", $email, PDO::PARAM_STR);
      $query->execute();
      return $query->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
  public static function contarFacturasInactivasGeneral()
  {
    try {
      $sql = "SELECT COUNT(*) as numFacturasI FROM tbl_factura
            WHERE factura_estado = '0'";
      $query = Db::dbConnection()->prepare($sql);
      $query->execute();
      return $query->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
  public static function dineroTotalFacturas()
  {
    try {
      $sql = "SELECT SUM(factura_total) as totalFacturas FROM tbl_factura
            WHERE factura_estado = '1'
            AND MONTH(factura_fecha) = MONTH(CURDATE())
            AND YEAR(factura_fecha) = YEAR(CURDATE())";
      $query = Db::dbConnection()->prepare($sql);
      $query->execute();
      return $query->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
  public static function aumentarSecuencialSerie()
  {
    try {
      $sql = "UPDATE tbl_config_serie SET config_secuencial = config_secuencial + 1";
      $query = Db::dbConnection()->prepare($sql);
      $query->execute();
      return true;
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }

  // Obtener ventas agrupadas por mes para el gráfico
  public static function obtenerVentasMensuales()
  {
    try {
      $sql = "SELECT 
                DATE_FORMAT(factura_fecha, '%Y-%m') as periodo,
                COUNT(*) as cantidad_ventas,
                SUM(factura_total) as total_ventas,
                SUM(factura_subtotal) as subtotal_ventas
              FROM tbl_factura
              WHERE factura_estado = '1'
                AND factura_fecha >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
              GROUP BY DATE_FORMAT(factura_fecha, '%Y-%m')
              ORDER BY periodo ASC";
      $query = Db::dbConnection()->prepare($sql);
      $query->execute();
      return $query->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      echo $e->getMessage();
      return [];
    }
  }
}
