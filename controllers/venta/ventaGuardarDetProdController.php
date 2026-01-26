<?php
include_once '../../models/tempModel.php';
include_once '../../models/productoModel.php';

// Verificar si el producto ya existe en el detalle temporal
$productoExistente = TempModel::verificarProductoExistente($_POST['id']);

// Obtener el stock actual del producto
$productoActual = ProductoModel::obtenerProductoStockId($_POST['id']);
$stockDisponible = $productoActual['producto_stock'];

// Calcular la cantidad total que se quiere tener en el carrito
$cantidadTotalEnCarrito = $productoExistente ?
    $productoExistente['temp_cantidad_vender'] + $_POST['cantidad'] :
    $_POST['cantidad'];

// Validar que haya suficiente stock disponible
if ($cantidadTotalEnCarrito <= $stockDisponible) {
    if ($productoExistente) {
        // Si el producto ya existe, actualizar la cantidad
        $nuevaCantidad = $productoExistente['temp_cantidad_vender'] + $_POST['cantidad'];
        $nuevoTotal = ((($nuevaCantidad * $_POST['precio_v']) - (($nuevaCantidad * $_POST['precio_v']) * $_POST['descuento']) / 100));

        $arrayActualizar = array(
            'cantidad' => $nuevaCantidad,
            'descuento' => $_POST['descuento'],
            'total' => $nuevoTotal,
            'producto_id' => $_POST['id']
        );

        if (TempModel::actualizarCantidadProducto($arrayActualizar)) {
            // Ya NO descontamos el stock aquí, se descontará al finalizar la venta
            echo 1;
        }
    } else {
        // Si el producto no existe, insertarlo
        $arrayName = array(
            'codigo' => $_POST['codigo'],
            'nombre' => $_POST['nombre'],
            'cantidad' => $_POST['cantidad'],
            'precio' => $_POST['precio_v'],
            'descuento' => $_POST['descuento'],
            'total' => ((($_POST['cantidad'] * $_POST['precio_v']) - (($_POST['cantidad'] * $_POST['precio_v']) * $_POST['descuento']) / 100)),
            'producto_id' => $_POST['id']
        );

        if (TempModel::guardarTempDetalleProducto($arrayName)) {
            // Ya NO descontamos el stock aquí, se descontará al finalizar la venta
            echo 1;
        }
    }
} else {
    echo 2;
}
