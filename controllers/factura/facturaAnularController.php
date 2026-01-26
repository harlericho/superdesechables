<?php
include_once '../../models/detalleModel.php';
include_once '../../models/facturaModel.php';
include_once '../../models/productoModel.php';

foreach (DetalleModel::obtenerDetalleIdFactura($_POST['factura_id']) as $key => $value) {
    $stock = ProductoModel::obtenerProductoStockId($value['producto_id']);
    $arrayName = array(
        'id' => $value['producto_id'],
        'stock' => $stock['producto_stock'] + $value['detalle_cantidad'],
    );
    DetalleModel::inactivarDetalle($value['detalle_id']);
    FacturaModel::inactivarFactura($value['factura_id']);
    ProductoModel::actualizarProductoStockId($arrayName);
}
// echo json_encode(DetalleModel::obtenerDetalleIdFactura($_POST['factura_id']));
echo 1;
