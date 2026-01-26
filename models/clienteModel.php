<?php
include_once '../../config/db.php';

class ClienteModel
{
  public static function obtenerClientes()
  {
    try {
      $sql = "SELECT * FROM tbl_cliente c
            INNER JOIN tbl_rol r ON c.rol_id = r.rol_id";
      $query = Db::dbConnection()->prepare($sql);
      $query->execute();
      return $query->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
  public static function guardarCliente($data)
  {
    try {
      $sql = "INSERT INTO tbl_cliente (cliente_dni,cliente_nombres,cliente_apellidos,cliente_direccion,cliente_email,cliente_telefono,rol_id)
             VALUES (:dni,:nombres,:apellidos,:direccion,:email,:telefono,:rol)";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(':dni', $data['dni'], PDO::PARAM_STR);
      $query->bindParam(':nombres', $data['nombres'], PDO::PARAM_STR);
      $query->bindParam(':apellidos', $data['apellidos'], PDO::PARAM_STR);
      $query->bindParam(':direccion', $data['direccion'], PDO::PARAM_STR);
      $query->bindParam(':email', $data['email'], PDO::PARAM_STR);
      $query->bindParam(':telefono', $data['telefono'], PDO::PARAM_STR);
      $query->bindParam(':rol', $data['rol'], PDO::PARAM_INT);
      if ($query->execute()) {
        $id = Db::dbConnection()->lastInsertId();
        return $id ? (int)$id : false;
      }
      return false;
    } catch (PDOException $e) {
      error_log($e->getMessage());
      return false;
    }
  }
  public static function actualizarCliente($data)
  {
    try {
      $sql = "UPDATE tbl_cliente SET cliente_dni = :dni, cliente_nombres = :nombres, cliente_apellidos = :apellidos,
            cliente_direccion = :direccion, cliente_email = :email, cliente_telefono = :telefono, 
            rol_id = :rol WHERE cliente_id = :id";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(':dni', $data['dni'], PDO::PARAM_STR);
      $query->bindParam(':nombres', $data['nombres'], PDO::PARAM_STR);
      $query->bindParam(':apellidos', $data['apellidos'], PDO::PARAM_STR);
      $query->bindParam(':direccion', $data['direccion'], PDO::PARAM_STR);
      $query->bindParam(':email', $data['email'], PDO::PARAM_STR);
      $query->bindParam(':telefono', $data['telefono'], PDO::PARAM_STR);
      $query->bindParam(':rol', $data['rol'], PDO::PARAM_INT);
      $query->bindParam(':id', $data['id'], PDO::PARAM_INT);
      $query->execute();
      return true;
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
  public static function obtenerClienteId($id)
  {
    try {
      $sql = "SELECT * FROM tbl_cliente c
            INNER JOIN tbl_rol r ON c.rol_id = r.rol_id
            WHERE cliente_id = :id";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(':id', $id, PDO::PARAM_INT);
      $query->execute();
      return $query->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
  public static function existeClienteDni($dni)
  {
    try {
      $sql = "SELECT * FROM tbl_cliente WHERE cliente_dni = :dni";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(':dni', $dni, PDO::PARAM_STR);
      $query->execute();
      return $query->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
  public static function existeClienteEmail($email)
  {
    try {
      $sql = "SELECT * FROM tbl_cliente WHERE cliente_email = :email";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(':email', $email, PDO::PARAM_STR);
      $query->execute();
      return $query->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
  public static function existeClienteTelefono($telefono)
  {
    try {
      $sql = "SELECT * FROM tbl_cliente WHERE cliente_telefono = :telefono";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(':telefono', $telefono, PDO::PARAM_STR);
      $query->execute();
      return $query->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
  public static function existeClienteDniActulizar($dni, $id)
  {
    try {
      $sql = "SELECT COUNT(*) FROM tbl_cliente WHERE cliente_dni = :dni OR cliente_id = :id";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(":dni", $dni, PDO::PARAM_STR);
      $query->bindParam(":id", $id, PDO::PARAM_INT);
      $query->execute();
      return $query->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
  public static function existeClienteEmailActualizar($email, $id)
  {
    try {
      $sql = "SELECT COUNT(*) FROM tbl_cliente WHERE cliente_email = :email OR cliente_id = :id";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(":email", $email, PDO::PARAM_STR);
      $query->bindParam(":id", $id, PDO::PARAM_INT);
      $query->execute();
      return $query->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
  public static function existeClienteTelefonoActualizar($telefono, $id)
  {
    try {
      $sql = "SELECT COUNT(*) FROM tbl_cliente WHERE cliente_telefono = :telefono OR cliente_id = :id";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(":telefono", $telefono, PDO::PARAM_STR);
      $query->bindParam(":id", $id, PDO::PARAM_INT);
      $query->execute();
      return $query->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
  public static function inactivarCliente($id)
  {
    try {
      $sql = "UPDATE tbl_cliente SET cliente_estado = 0 WHERE cliente_id = :id";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(':id', $id, PDO::PARAM_INT);
      $query->execute();
      return true;
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
  public static function activarCliente($id)
  {
    try {
      $sql = "UPDATE tbl_cliente SET cliente_estado = 1 WHERE cliente_id = :id";
      $query = Db::dbConnection()->prepare($sql);
      $query->bindParam(':id', $id, PDO::PARAM_INT);
      $query->execute();
      return true;
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
  public static function contarClientes()
  {
    try {
      $sql = "SELECT COUNT(*) as numClientes FROM tbl_cliente";
      $query = Db::dbConnection()->prepare($sql);
      $query->execute();
      return $query->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      echo $e->getMessage();
    }
  }
}
