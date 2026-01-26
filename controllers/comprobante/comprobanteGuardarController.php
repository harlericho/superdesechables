<?php
include_once '../../models/comprobanteModel.php';

if (ComprobanteModel::existeTipoComprobanteDescripcion($_POST['descripcion'])) {
    echo 2;
} else {
    if (ComprobanteModel::guardarTipoComprobante($_POST['descripcion'])) {
        echo 1;
    } else {
        echo 0;
    }
}
