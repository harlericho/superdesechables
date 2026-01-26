<?php
include_once '../../config/db.php';

class CategoriaModel
{
    public static function obtenerCategorias()
    {
        try {
            $sql = "SELECT * FROM tbl_categoria";
            $query = Db::dbConnection()->prepare($sql);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
    public static function guardarCategoria($descripcion)
    {
        try {
            $sql = "INSERT INTO tbl_categoria (categoria_descripcion) VALUES (:descripcion)";
            $query = Db::dbConnection()->prepare($sql);
            $query->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
            $query->execute();
            return true;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
    public static function existeCategoriaDescripcion($descripcion)
    {
        try {
            $sql = "SELECT * FROM tbl_categoria WHERE categoria_descripcion = :descripcion";
            $query = Db::dbConnection()->prepare($sql);
            $query->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
            $query->execute();
            return $query->rowCount() > 0;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
    public static function inactivarCategoria($id)
    {
        try {
            $sql = "UPDATE tbl_categoria SET categoria_estado = 0 WHERE categoria_id = :id";
            $query = Db::dbConnection()->prepare($sql);
            $query->bindParam(':id', $id, PDO::PARAM_INT);
            $query->execute();
            return true;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
    public static function activarCategoria($id)
    {
        try {
            $sql = "UPDATE tbl_categoria SET categoria_estado = 1 WHERE categoria_id = :id";
            $query = Db::dbConnection()->prepare($sql);
            $query->bindParam(':id', $id, PDO::PARAM_INT);
            $query->execute();
            return true;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
    public static function contarCategorias()
    {
        try {
            $sql = "SELECT COUNT(*) as numCategorias FROM tbl_categoria";
            $query = Db::dbConnection()->prepare($sql);
            $query->execute();
            return $query->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
}
