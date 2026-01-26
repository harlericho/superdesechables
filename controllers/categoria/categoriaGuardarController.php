<?php
include_once  '../../models/categoriaModel.php';

if (CategoriaModel::existeCategoriaDescripcion($_POST['descripcion'])) {
    echo 2;
} else {
    if (CategoriaModel::guardarCategoria($_POST['descripcion'])) {
        echo 1;
    } else {
        echo 0;
    }
}
