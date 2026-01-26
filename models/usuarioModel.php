<?php
include_once '../../config/db.php';
include_once '../../config/encryption.php';

class UsuarioModel
{
  public static function obtenerUsuarios()
  {
    try {
      $sql = "SELECT * FROM tbl_usuario u
            INNER JOIN tbl_rol r ON u.rol_id = r.rol_id";
      $query = Db::dbConnection()->prepare($sql);
      $query->execute();
      while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
        $json[] = array(
          'id' => $row['usuario_id'],
          'nombres' => $row['usuario_nombres'],
          'email' => $row['usuario_email'],
          'password' => Encryption::_desencryptacion($row['usuario_password']),
          'rol' => $row['rol_id'],
          'descripcion' => $row['rol_descripcion'],
          'estado' => $row['usuario_estado']
        );
      }
      if (!empty($json)) {
        return $json;
      }
      return null;
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
  public static function obtenerDatoUsuario($email)
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
  public static function guardarUsuario($data)
  {
    try {
      $sql = "INSERT INTO tbl_usuario (usuario_nombres, usuario_email, usuario_password, rol_id) 
            VALUES (:nombres, :email, :password, :rol)";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(':nombres', $data['nombres'], PDO::PARAM_STR);
      $query->bindParam(':email', $data['email'], PDO::PARAM_STR);
      $query->bindParam(':password', $data['password'], PDO::PARAM_STR);
      $query->bindParam(':rol', $data['rol'], PDO::PARAM_INT);
      return $query->execute();
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
  public static function existeUsuarioEmail($email)
  {
    try {
      $sql = "SELECT usuario_email FROM tbl_usuario WHERE usuario_email = :email";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(':email', $email, PDO::PARAM_STR);
      $query->execute();
      return $query->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
  public static function existeUsuarioEmailActualizar($email, $id)
  {
    try {
      $sql = "SELECT COUNT(*) FROM tbl_usuario WHERE usuario_email = :email OR usuario_id = :id";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(':email', $email, PDO::PARAM_STR);
      $query->bindParam(':id', $id, PDO::PARAM_INT);
      $query->execute();
      return $query->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }

  public static function actualizarUsuario($data)
  {
    try {
      $sql = "UPDATE tbl_usuario SET usuario_nombres = :nombres, usuario_email = :email, usuario_password = :password, rol_id = :rol WHERE usuario_id = :id";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(':nombres', $data['nombres'], PDO::PARAM_STR);
      $query->bindParam(':email', $data['email'], PDO::PARAM_STR);
      $query->bindParam(':password', $data['password'], PDO::PARAM_STR);
      $query->bindParam(':rol', $data['rol'], PDO::PARAM_INT);
      $query->bindParam(':id', $data['id'], PDO::PARAM_INT);
      return $query->execute();
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
  public static function actualizarUsuarioDatos($nombres, $email)
  {
    try {
      $sql = "UPDATE tbl_usuario SET usuario_nombres = :nombres WHERE usuario_email = :email";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(':nombres', $nombres, PDO::PARAM_STR);
      $query->bindParam(':email', $email, PDO::PARAM_STR);
      return $query->execute();
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
  public static function activarUsuario($id)
  {
    try {
      $sql = "UPDATE tbl_usuario SET usuario_estado = 1 WHERE usuario_id = :id";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(':id', $id, PDO::PARAM_INT);
      $query->execute();
      return true;
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
  public static function inactivarUsuario($id)
  {
    try {
      $sql = "UPDATE tbl_usuario SET usuario_estado = 0 WHERE usuario_id = :id";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(':id', $id, PDO::PARAM_INT);
      $query->execute();
      return true;
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
  public static function obtenerUsuarioId($id)
  {
    try {
      $sql = "SELECT * FROM tbl_usuario u
            INNER JOIN tbl_rol r ON u.rol_id = r.rol_id
            WHERE u.usuario_id = :id";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(':id', $id, PDO::PARAM_INT);
      $query->execute();
      $row = $query->fetch(PDO::FETCH_ASSOC);
      $json[] = array(
        'id' => $row['usuario_id'],
        'nombres' => $row['usuario_nombres'],
        'email' => $row['usuario_email'],
        'password' => Encryption::_desencryptacion($row['usuario_password']),
        'rol' => $row['rol_id']
      );
      return $json;
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }

  public static function existeUsuarioEmailPassword($data)
  {
    try {
      $sql = "SELECT * FROM tbl_usuario WHERE usuario_email = :email
            AND usuario_password = :password";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(':email', $data['email'], PDO::PARAM_STR);
      $query->bindParam(':password', $data['password'], PDO::PARAM_STR);
      $query->execute();
      return $query->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
  public static function existeUsuarioEmailPasswordInactivo($data)
  {
    try {
      $sql = "SELECT * FROM tbl_usuario WHERE usuario_email = :email
            AND usuario_password = :password AND usuario_estado = '0'";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(':email', $data['email'], PDO::PARAM_STR);
      $query->bindParam(':password', $data['password'], PDO::PARAM_STR);
      $query->execute();
      return $query->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
  public static function contarUsuarios()
  {
    try {
      $sql = "SELECT COUNT(*) as numUsuarios FROM tbl_usuario";
      $query = Db::dbConnection()->prepare($sql);
      $query->execute();
      return $query->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
}
