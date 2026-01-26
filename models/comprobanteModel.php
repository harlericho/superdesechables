<?php
include_once '../../config/db.php';

class ComprobanteModel
{
    public static function obtenerTipoComprobante()
    {
        try {
            $sql = "SELECT * FROM tbl_tipo_comprobante";
            $query = Db::dbConnection()->prepare($sql);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
    public static function guardarTipoComprobante($descripcion)
    {
        try {
            $sql = "INSERT INTO tbl_tipo_comprobante (tipo_comp_descripcion) VALUES (:descripcion)";
            $query = Db::dbConnection()->prepare($sql);
            $query->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
            $query->execute();
            return true;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
    public static function existeTipoComprobanteDescripcion($descripcion)
    {
        try {
            $sql = "SELECT * FROM tbl_tipo_comprobante WHERE tipo_comp_descripcion = :descripcion";
            $query = Db::dbConnection()->prepare($sql);
            $query->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
            $query->execute();
            return $query->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
}
