<?php
include_once '../../models/tempModel.php';

// Ya NO necesitamos devolver stock porque nunca se descontó
// Simplemente eliminamos el producto del carrito temporal
if (TempModel::eliminarTempId($_POST['temp_id'])) {
    echo 1;
}
