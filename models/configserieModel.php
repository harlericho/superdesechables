<?php
include_once '../../config/db.php';

class ConfigserieModel
{

  public static function listarConfigSerie()
  {
    try {
      $sql = "SELECT * FROM tbl_config_serie";
      $query = Db::dbConnection()->prepare($sql);
      $query->execute();
      return $query->fetchAll();
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
  public static function obtenerConfigSerieId($id)
  {
    try {
      $sql = "SELECT * FROM tbl_config_serie WHERE config_id = :id";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(':id', $id, PDO::PARAM_INT);
      $query->execute();
      $row = $query->fetch(PDO::FETCH_ASSOC);
      $json[] = array(
        'id' => $row['config_id'],
        'primera_serie' => $row['config_primera_serie'],
        'segunda_serie' => $row['config_segunda_serie'],
        'secuencial' => ltrim($row['config_secuencial'], '0')
      );
      return $json;
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
  public static function guardarConfigSerie($data)
  {
    try {
      $sql = "INSERT INTO tbl_config_serie (config_primera_serie,config_segunda_serie,config_secuencial) VALUES (:primera_serie, :segunda_serie, :secuencial)";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(':primera_serie', $data['primera_serie'], PDO::PARAM_STR);
      $query->bindParam(':segunda_serie', $data['segunda_serie'], PDO::PARAM_STR);
      $query->bindParam(':secuencial', $data['secuencial'], PDO::PARAM_STR);
      return $query->execute();
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
  public static function contarConfigSerie()
  {
    try {
      $sql = "SELECT COUNT(*) FROM tbl_config_serie";
      $query = Db::dbConnection()->prepare($sql);
      $query->execute();
      return $query->fetchColumn();
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
  public static function actualizarConfigSerie($data)
  {
    try {
      $sql = "UPDATE tbl_config_serie SET config_primera_serie = :primera_serie, config_segunda_serie = :segunda_serie, config_secuencial = :secuencial WHERE config_id = :id";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(':primera_serie', $data['primera_serie'], PDO::PARAM_STR);
      $query->bindParam(':segunda_serie', $data['segunda_serie'], PDO::PARAM_STR);
      $query->bindParam(':secuencial', $data['secuencial'], PDO::PARAM_STR);
      $query->bindParam(':id', $data['id'], PDO::PARAM_INT);
      return $query->execute();
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
}
