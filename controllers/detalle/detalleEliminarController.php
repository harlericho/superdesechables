<?php
include_once '../../models/detalleModel.php';
if (DetalleModel::eliminarDetalleId($_POST['detalle_id'])) {
    echo 1;
}
