<?php
include_once '../../config/db.php';

class ProveedorModel
{
    public static function obtenerProveedores()
    {
        try {
            $sql = "SELECT * FROM tbl_proveedor";
            $query = Db::dbConnection()->prepare($sql);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
    public static function guardarProveedor($data)
    {
        try {
            $sql = "INSERT INTO tbl_proveedor (proveedor_dni, proveedor_nombres, proveedor_telefono, proveedor_email) VALUES (:dni, :nombres, :telefono, :email)";
            $query = Db::dbConnection()->prepare($sql);
            $query->bindParam(':dni', $data['dni'], PDO::PARAM_STR);
            $query->bindParam(':nombres', $data['nombres'], PDO::PARAM_STR);
            $query->bindParam(':telefono', $data['telefono'], PDO::PARAM_STR);
            $query->bindParam(':email', $data['email'], PDO::PARAM_STR);
            $query->execute();
            return true;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
    public static function existeProveedorDni($dni)
    {
        try {
            $sql = "SELECT * FROM tbl_proveedor WHERE proveedor_dni = :dni";
            $query = Db::dbConnection()->prepare($sql);
            $query->bindParam(':dni', $dni, PDO::PARAM_STR);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
    public static function existeProveedorEmail($email)
    {
        try {
            $sql = "SELECT * FROM tbl_proveedor WHERE proveedor_email = :email";
            $query = Db::dbConnection()->prepare($sql);
            $query->bindParam(':email', $email, PDO::PARAM_STR);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
    public static function existeProveedorTelefono($telefono)
    {
        try {
            $sql = "SELECT * FROM tbl_proveedor WHERE proveedor_telefono = :telefono";
            $query = Db::dbConnection()->prepare($sql);
            $query->bindParam(':telefono', $telefono, PDO::PARAM_STR);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
    public static function contarProveedores()
    {
        try {
            $sql = "SELECT COUNT(*) as numProveedores FROM tbl_proveedor";
            $query = Db::dbConnection()->prepare($sql);
            $query->execute();
            return $query->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
}
