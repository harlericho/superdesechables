<?php
include_once '../../config/db.php';

class ProductoModel
{

  public static function obtenerProductos()
  {
    try {
      $sql = "SELECT * FROM tbl_producto p
            INNER JOIN tbl_categoria c ON p.categoria_id = c.categoria_id
            INNER JOIN tbl_proveedor m ON p.proveedor_id = m.proveedor_id";
      $query = Db::dbConnection()->prepare($sql);
      $query->execute();
      return $query->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
  public static function guardarProducto($data)
  {
    try {
      $sql = "INSERT INTO tbl_producto (producto_codigo, producto_nombre, producto_descripcion,
            producto_precio_compra, producto_precio_venta, producto_stock, producto_imagen, categoria_id, proveedor_id)
            VALUES (:codigo, :nombre, :descripcion, :precio_c, :precio, :stock, :foto, :categoria_id, :proveedor_id)";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(":codigo", $data['codigo'], PDO::PARAM_STR);
      $query->bindParam(":nombre", $data['nombre'], PDO::PARAM_STR);
      $query->bindParam(":descripcion", $data['descripcion'], PDO::PARAM_STR);
      $query->bindParam(":precio_c", $data['precio_c'], PDO::PARAM_STR);
      $query->bindParam(":precio", $data['precio'], PDO::PARAM_STR);
      $query->bindParam(":stock", $data['stock'], PDO::PARAM_INT);
      $query->bindParam(":foto", $data['foto'], PDO::PARAM_STR);
      $query->bindParam(":categoria_id", $data['categoria_id'], PDO::PARAM_INT);
      $query->bindParam(":proveedor_id", $data['proveedor_id'], PDO::PARAM_INT);
      $query->execute();
      return true;
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
  public static function actualizarProducto($data)
  {
    try {
      $sql = "UPDATE tbl_producto SET producto_codigo = :codigo, producto_nombre = :nombre, 
            producto_descripcion = :descripcion, producto_precio_compra = :precio_c, producto_precio_venta = :precio, producto_stock = :stock, 
            producto_imagen = :foto, categoria_id = :categoria_id, proveedor_id = :proveedor_id
            WHERE producto_id = :id";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(":id", $data['id'], PDO::PARAM_INT);
      $query->bindParam(":codigo", $data['codigo'], PDO::PARAM_STR);
      $query->bindParam(":nombre", $data['nombre'], PDO::PARAM_STR);
      $query->bindParam(":descripcion", $data['descripcion'], PDO::PARAM_STR);
      $query->bindParam(":precio_c", $data['precio_c'], PDO::PARAM_STR);
      $query->bindParam(":precio", $data['precio'], PDO::PARAM_STR);
      $query->bindParam(":stock", $data['stock'], PDO::PARAM_INT);
      $query->bindParam(":foto", $data['foto'], PDO::PARAM_STR);
      $query->bindParam(":categoria_id", $data['categoria_id'], PDO::PARAM_INT);
      $query->bindParam(":proveedor_id", $data['proveedor_id'], PDO::PARAM_INT);
      $query->execute();
      return true;
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
  public static function existeProductoCodigo($codigo)
  {
    try {
      $sql = "SELECT * FROM tbl_producto WHERE producto_codigo = :codigo
            AND producto_estado = '1'";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(":codigo", $codigo, PDO::PARAM_STR);
      $query->execute();
      return $query->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
  public static function inactivarProducto($id)
  {
    try {
      $sql = "UPDATE tbl_producto SET producto_estado = 0 WHERE producto_id = :id";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(":id", $id, PDO::PARAM_INT);
      $query->execute();
      return true;
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
  public static function activarProducto($id)
  {
    try {
      $sql = "UPDATE tbl_producto SET producto_estado = 1 WHERE producto_id = :id";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(":id", $id, PDO::PARAM_INT);
      $query->execute();
      return true;
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
  public static function obtenerProductoId($id)
  {
    try {
      $sql = "SELECT * FROM tbl_producto p
            INNER JOIN tbl_categoria c ON p.categoria_id = c.categoria_id
            INNER JOIN tbl_proveedor m ON p.proveedor_id = m.proveedor_id
            WHERE producto_id = :id";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(":id", $id, PDO::PARAM_INT);
      $query->execute();
      return $query->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
  public static function existeProductoCodigoActualizar($codigo, $id)
  {
    try {
      $sql = "SELECT COUNT(*) FROM tbl_producto WHERE producto_codigo = :codigo OR producto_id = :id";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(":codigo", $codigo, PDO::PARAM_STR);
      $query->bindParam(":id", $id, PDO::PARAM_INT);
      $query->execute();
      return $query->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
  public static function obtenerProductoImagenId($id)
  {
    try {
      $sql = "SELECT producto_imagen FROM tbl_producto WHERE producto_id = :id";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(":id", $id, PDO::PARAM_INT);
      $query->execute();
      return $query->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
  public static function obtenerProductoStockId($id)
  {
    try {
      $sql = "SELECT producto_stock FROM tbl_producto WHERE producto_id = :id";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(":id", $id, PDO::PARAM_INT);
      $query->execute();
      return $query->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
  public static function actualizarProductoStockId($data)
  {
    try {
      $sql = "UPDATE tbl_producto SET producto_stock = :stock WHERE producto_id = :id";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(":id", $data['id'], PDO::PARAM_INT);
      $query->bindParam(":stock", $data['stock'], PDO::PARAM_INT);
      $query->execute();
      return true;
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
  public static function contarProductos()
  {
    try {
      $sql = "SELECT COUNT(*) as numProductos FROM tbl_producto";
      $query = Db::dbConnection()->prepare($sql);
      $query->execute();
      return $query->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
  public static function stockProductoInferior()
  {
    try {
      $sql = "SELECT COUNT(*) as stockProductos FROM tbl_producto WHERE producto_stock<10";
      $query = Db::dbConnection()->prepare($sql);
      $query->execute();
      return $query->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
  public static function dineroTotalProductoAdquirido()
  {
    try {
      $sql = "SELECT SUM(producto_precio_compra) as totalProductoAdquirido FROM tbl_producto";
      $query = Db::dbConnection()->prepare($sql);
      $query->execute();
      return $query->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
}
