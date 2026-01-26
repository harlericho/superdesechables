<?php
session_start();
include_once '../../models/facturaModel.php';
echo json_encode(FacturaModel::obtenerFacturasActivas($_SESSION["email"]));