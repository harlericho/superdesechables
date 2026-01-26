<?php
include_once '../../config/db.php';

class DetalleModel
{
  public static function obtenerDetalleFacturaPdf($id)
  {
    try {
      $sql = "SELECT * FROM tbl_detalle d
      INNER JOIN tbl_factura f ON d.factura_id = f.factura_id
      INNER JOIN tbl_usuario u ON f.usuario_id = u.usuario_id
      INNER JOIN tbl_cliente c ON f.cliente_id = c.cliente_id
      INNER JOIN tbl_tipo_comprobante t ON f.tipo_comp_id = t.tipo_comp_id
      INNER JOIN tbl_producto p ON d.producto_id = p.producto_id
      WHERE d.detalle_estado = '1' AND d.factura_id=:factura_id";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(":factura_id", $id, PDO::PARAM_INT);
      $query->execute();
      return $query->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
  public static function obtenerDetalleProductoActivo($email)
  {
    try {
      $sql = "SELECT * FROM tbl_detalle d
            INNER JOIN tbl_factura f ON d.factura_id = f.factura_id
            INNER JOIN tbl_usuario u ON f.usuario_id = u.usuario_id
            INNER JOIN tbl_producto p ON d.producto_id = p.producto_id
            WHERE d.detalle_estado = '1' AND u.usuario_email=:usuario_email";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(":usuario_email", $email, PDO::PARAM_STR);
      $query->execute();
      return $query->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
  public static function obtenerDetalleProductoActivoGeneral()
  {
    try {
      $sql = "SELECT * FROM tbl_detalle d
            INNER JOIN tbl_factura f ON d.factura_id = f.factura_id
            INNER JOIN tbl_usuario u ON f.usuario_id = u.usuario_id
            INNER JOIN tbl_producto p ON d.producto_id = p.producto_id
            WHERE d.detalle_estado = '1'";
      $query = Db::dbConnection()->prepare($sql);
      $query->execute();
      return $query->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
  public static function obtenerDetalleProductoInactivo($email)
  {
    try {
      $sql = "SELECT * FROM tbl_detalle d
            INNER JOIN tbl_factura f ON d.factura_id = f.factura_id
            INNER JOIN tbl_usuario u ON f.usuario_id = u.usuario_id
            INNER JOIN tbl_producto p ON d.producto_id = p.producto_id
            WHERE d.detalle_estado = '0' AND u.usuario_email=:usuario_email";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(":usuario_email", $email, PDO::PARAM_STR);
      $query->execute();
      return $query->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
  public static function obtenerDetalleProductoInactivoGeneral()
  {
    try {
      $sql = "SELECT * FROM tbl_detalle d
            INNER JOIN tbl_factura f ON d.factura_id = f.factura_id
            INNER JOIN tbl_usuario u ON f.usuario_id = u.usuario_id
            INNER JOIN tbl_producto p ON d.producto_id = p.producto_id
            WHERE d.detalle_estado = '0'";
      $query = Db::dbConnection()->prepare($sql);
      $query->execute();
      return $query->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
  public static function guardarDetalle($data)
  {
    try {
      $sql = "INSERT INTO tbl_detalle (factura_id, producto_id, detalle_cantidad, detalle_precio_unit,
            detalle_descuento, detalle_total)
            VALUES (:factura_id, :producto_id, :cantidad, :precio_unitario, :descuento, :precio_total)";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(":factura_id", $data["factura_id"], PDO::PARAM_INT);
      $query->bindParam(":producto_id", $data["producto_id"], PDO::PARAM_INT);
      $query->bindParam(":cantidad", $data["cantidad"], PDO::PARAM_INT);
      $query->bindParam(":precio_unitario", $data["precio_unitario"], PDO::PARAM_STR);
      $query->bindParam(":descuento", $data["descuento"], PDO::PARAM_STR);
      $query->bindParam(":precio_total", $data["precio_total"], PDO::PARAM_STR);
      $query->execute();
      return true;
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
  public static function inactivarDetalle($id)
  {
    try {
      $sql = "UPDATE tbl_detalle SET detalle_estado = 0 WHERE detalle_id = :id";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(":id", $id, PDO::PARAM_INT);
      $query->execute();
      return true;
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
  public static function obtenerDetalleIdFactura($id)
  {
    try {
      $sql = "SELECT * FROM tbl_detalle WHERE factura_id = :id";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(":id", $id, PDO::PARAM_INT);
      $query->execute();
      return $query->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
  public static function eliminarDetalleId($id)
  {
    try {
      $sql = "DELETE FROM tbl_detalle WHERE detalle_id =:id";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(":id", $id, PDO::PARAM_INT);
      $query->execute();
      return true;
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
}
