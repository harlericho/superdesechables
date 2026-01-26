<?php
include_once '../../models/productoModel.php';
if (ProductoModel::existeProductoCodigoActualizar(strtoupper($_POST['codigo']), $_POST['id'])['COUNT(*)']  >= 2) {
    echo 2;
} else {
    $imagenId = ProductoModel::obtenerProductoImagenId($_POST['id']);
    if (file_exists($_FILES['file']['tmp_name'])) {
        if ($imagenId['producto_imagen'] != "sin-imagen.png") {
            unlink("../../assets/uploads/" . $imagenId['producto_imagen']);
        }
        $randomMd5 = substr(md5(uniqid(rand())), 0, 8);
        $imagen = $randomMd5 . $_FILES['file']['name'];
        $url = "../../assets/uploads/";
        $target = $url . basename($imagen);
        copy($_FILES['file']['tmp_name'], $target);
    } else {
        $imagen = $imagenId['producto_imagen'];
    }

    $arrayName = array(
        'id' => $_POST['id'],
        'codigo' =>  strtoupper($_POST['codigo']),
        'nombre' => strtoupper($_POST['nombre']),
        'descripcion' => strtoupper($_POST['descripcion']),
        'precio_c' => $_POST['precio_c'],
        'precio' => $_POST['precio_v'],
        'stock' => $_POST['stock'],
        'foto' => $imagen,
        'categoria_id' => $_POST['categoria'],
        'proveedor_id' => $_POST['proveedor']
    );
    if (ProductoModel::actualizarProducto($arrayName)) {
        echo 1;
    } else {
        echo 0;
    }
}
