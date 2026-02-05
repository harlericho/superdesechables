<?php
include_once '../../config/db.php';

class ImpuestoModel
{
  public static function obtenerImpuestos()
  {
    try {
      // Obtener todos los impuestos (incluyendo eliminados para mostrarlos en gris)
      $sql = "SELECT * FROM tbl_impuesto ORDER BY impuesto_id DESC";
      $query = Db::dbConnection()->prepare($sql);
      $query->execute();
      return $query->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }

  public static function obtenerImpuestoActivo()
  {
    try {
      $sql = "SELECT * FROM tbl_impuesto WHERE impuesto_activo = 1 AND impuesto_estado = 1 LIMIT 1";
      $query = Db::dbConnection()->prepare($sql);
      $query->execute();

      $result = $query->fetch(PDO::FETCH_ASSOC);
      return $result;
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }

  public static function activarImpuesto($impuesto_id)
  {
    try {
      // Primero desactivar todos los impuestos
      $sql = "UPDATE tbl_impuesto SET impuesto_activo = 0 WHERE impuesto_estado = 1";
      $query = Db::dbConnection()->prepare($sql);
      $query->execute();

      // Activar el impuesto seleccionado
      $sql = "UPDATE tbl_impuesto SET impuesto_activo = 1 WHERE impuesto_id = :impuesto_id AND impuesto_estado = 1";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(':impuesto_id', $impuesto_id, PDO::PARAM_INT);
      return $query->execute();
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }

  public static function guardarImpuesto($nombre, $porcentaje)
  {
    try {
      $sql = "INSERT INTO tbl_impuesto (impuesto_nombre, impuesto_porcentaje) VALUES (:nombre, :porcentaje)";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(':nombre', $nombre, PDO::PARAM_STR);
      $query->bindParam(':porcentaje', $porcentaje, PDO::PARAM_STR);
      return $query->execute();
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }

  public static function editarImpuesto($id, $nombre, $porcentaje)
  {
    try {
      $sql = "UPDATE tbl_impuesto SET impuesto_nombre = :nombre, impuesto_porcentaje = :porcentaje 
                    WHERE impuesto_id = :id AND impuesto_estado = 1";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(':id', $id, PDO::PARAM_INT);
      $query->bindParam(':nombre', $nombre, PDO::PARAM_STR);
      $query->bindParam(':porcentaje', $porcentaje, PDO::PARAM_STR);
      return $query->execute();
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }

  public static function eliminarImpuesto($id)
  {
    try {
      $sql = "UPDATE tbl_impuesto SET impuesto_estado = 0 WHERE impuesto_id = :id";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(':id', $id, PDO::PARAM_INT);
      return $query->execute();
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }

  public static function restaurarImpuesto($id)
  {
    try {
      $sql = "UPDATE tbl_impuesto SET impuesto_estado = 1 WHERE impuesto_id = :id";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(':id', $id, PDO::PARAM_INT);
      return $query->execute();
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
}
