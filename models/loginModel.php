<?php
include_once '../config/db.php';
class LoginModel
{
  public static function existeUsuarioEmailLogin($email)
  {
    try {
      $sql = "SELECT * FROM tbl_usuario u
            INNER JOIN tbl_rol r ON u.rol_id = r.rol_id
            WHERE u.usuario_email = :email AND u.usuario_estado = '1'";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(':email', $email, PDO::PARAM_STR);
      $query->execute();
      return $query->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
}
