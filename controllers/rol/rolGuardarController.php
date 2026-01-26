<?php
include_once  '../../models/rolModel.php';

if (RolModel::existeRolDescripcion($_POST['descripcion'])) {
    echo 2;
} else {
    if (RolModel::guardarRol($_POST['descripcion'])) {
        echo 1;
    } else {
        echo 0;
    }
}
