<?php
include_once '../../config/db.php';

class TempModel
{
    public static function obtenerTempDetalle()
    {
        try {
            $sql = "SELECT * FROM tbl_temp_detalle";
            $query = Db::dbConnection()->prepare($sql);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
    public static function guardarTempDetalleProducto($data)
    {
        try {
            $sql = "INSERT INTO tbl_temp_detalle(temp_cod_producto, temp_nombre_producto, 
            temp_cantidad_vender, temp_precio_producto, temp_descuento, temp_total, temp_id_producto)
            VALUES(:codigo, :nombre, :cantidad, :precio, :descuento, :total, :producto_id)";
            $query = Db::dbConnection()->prepare($sql);
            $query->bindParam(":codigo", $data['codigo'], PDO::PARAM_STR);
            $query->bindParam(":nombre", $data['nombre'], PDO::PARAM_STR);
            $query->bindParam(":cantidad", $data['cantidad'], PDO::PARAM_INT);
            $query->bindParam(":precio", $data['precio'], PDO::PARAM_STR);
            $query->bindParam(":descuento", $data['descuento'], PDO::PARAM_STR);
            $query->bindParam(":total", $data['total'], PDO::PARAM_STR);
            $query->bindParam(":producto_id", $data['producto_id'], PDO::PARAM_INT);
            $query->execute();
            return true;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
    public static function eliminarTempId($id)
    {
        try {
            $sql = "DELETE FROM tbl_temp_detalle WHERE temp_id =:id";
            $query = Db::dbConnection()->prepare($sql);
            $query->bindParam(":id", $id, PDO::PARAM_INT);
            $query->execute();
            return true;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
    public static function sumarTempTotal()
    {
        try {
            $sql = "SELECT SUM(temp_total) AS total FROM tbl_temp_detalle";
            $query = Db::dbConnection()->prepare($sql);
            $query->execute();
            return $query->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
    public static function verificarProductoExistente($producto_id)
    {
        try {
            $sql = "SELECT * FROM tbl_temp_detalle WHERE temp_id_producto = :producto_id";
            $query = Db::dbConnection()->prepare($sql);
            $query->bindParam(":producto_id", $producto_id, PDO::PARAM_INT);
            $query->execute();
            return $query->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
    public static function actualizarCantidadProducto($data)
    {
        try {
            $sql = "UPDATE tbl_temp_detalle SET 
                    temp_cantidad_vender = :cantidad,
                    temp_descuento = :descuento,
                    temp_total = :total
                    WHERE temp_id_producto = :producto_id";
            $query = Db::dbConnection()->prepare($sql);
            $query->bindParam(":cantidad", $data['cantidad'], PDO::PARAM_INT);
            $query->bindParam(":descuento", $data['descuento'], PDO::PARAM_STR);
            $query->bindParam(":total", $data['total'], PDO::PARAM_STR);
            $query->bindParam(":producto_id", $data['producto_id'], PDO::PARAM_INT);
            $query->execute();
            return true;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
    public static function eliminarDatosTemp()
    {
        try {
            $sql = "DELETE FROM tbl_temp_detalle";
            $query = Db::dbConnection()->prepare($sql);
            $query->execute();
            return true;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
}
