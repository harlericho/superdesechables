<?php
include_once '../../config/db.php';

class CajaChicaModel
{
  public static function listarMovimientoCajachica()
  {
    try {
      $sql = "SELECT * FROM tbl_movimiento_caja WHERE mov_fecharegistro = CURDATE()";
      $query = Db::dbConnection()->prepare($sql);
      $query->execute();
      return $query->fetchAll();
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
  public static function guardarMovimientoCajachica($data)
  {
    try {
      $sql = "INSERT INTO tbl_movimiento_caja (mov_fecharegistro, mov_descripcion, mov_tipo, mov_monto) VALUES (:fecha, :descripcion, :tipo, :monto)";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(':fecha', $data['mov_fecharegistro'], PDO::PARAM_STR);
      $query->bindParam(':descripcion', $data['mov_descripcion'], PDO::PARAM_STR);
      $query->bindParam(':tipo', $data['mov_tipo'], PDO::PARAM_STR);
      $query->bindParam(':monto', $data['mov_monto'], PDO::PARAM_STR);
      return $query->execute();
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
}
