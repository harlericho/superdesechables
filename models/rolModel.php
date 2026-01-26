<?php
include_once '../../config/db.php';

class RolModel
{
    public static function obtenerRoles()
    {
        try {
            $sql = "SELECT * FROM tbl_rol";
            $query = Db::dbConnection()->prepare($sql);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
    public static function guardarRol($descripcion)
    {
        try {
            $sql = "INSERT INTO tbl_rol (rol_descripcion) VALUES (:descripcion)";
            $query = Db::dbConnection()->prepare($sql);
            $query->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
            $query->execute();
            return true;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
    public static function existeRolDescripcion($descripcion)
    {
        try {
            $sql = "SELECT * FROM tbl_rol WHERE rol_descripcion = :descripcion";
            $query = Db::dbConnection()->prepare($sql);
            $query->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
    public static function inactivarRol($id)
    {
        try {
            $sql = "UPDATE tbl_rol SET rol_estado = 0 WHERE rol_id = :id";
            $query = Db::dbConnection()->prepare($sql);
            $query->bindParam(':id', $id, PDO::PARAM_INT);
            $query->execute();
            return true;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
    public static function activarRol($id)
    {
        try {
            $sql = "UPDATE tbl_rol SET rol_estado = 1 WHERE rol_id = :id";
            $query = Db::dbConnection()->prepare($sql);
            $query->bindParam(':id', $id, PDO::PARAM_INT);
            $query->execute();
            return true;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
    public static function contarRoles()
    {
        try {
            $sql = "SELECT COUNT(*) AS numRoles FROM tbl_rol";
            $query = Db::dbConnection()->prepare($sql);
            $query->execute();
            return $query->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
}
