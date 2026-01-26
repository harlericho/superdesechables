<?php
include_once '../../models/proveedorModel.php';

$arrayName = array(
    'dni' => $_POST['dni'],
    'nombres' => $_POST['nombres'],
    'telefono' => $_POST['telefono'],
    'email' => $_POST['email']
);

if (ProveedorModel::existeProveedorDni($_POST['dni'])) {
    echo 2;
} else if (ProveedorModel::existeProveedorEmail($_POST['email'])) {
    echo 3;
} else if (ProveedorModel::existeProveedorTelefono($_POST['telefono'])) {
    echo 4;
} else {
    if (ProveedorModel::guardarProveedor($arrayName)) {
        echo 1;
    } else {
        echo 0;
    }
}
