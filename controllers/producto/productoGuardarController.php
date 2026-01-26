<?php
include_once  '../../models/productoModel.php';

if (ProductoModel::existeProductoCodigo(strtoupper($_POST['codigo']))) {
    echo 2;
} else {

    if (file_exists($_FILES['file']['tmp_name'])) {
        $randomMd5 = substr(md5(uniqid(rand())), 0, 8);
        $imagen = $randomMd5 . $_FILES['file']['name'];
        $url = "../../assets/uploads/";
        $target = $url . basename($imagen);
        copy($_FILES['file']['tmp_name'], $target);
    } else {
        $imagen = "sin-imagen.png";
    }

    $arrayName = array(
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
    if (ProductoModel::guardarProducto($arrayName)) {
        echo 1;
    } else {
        echo 0;
    }
}
