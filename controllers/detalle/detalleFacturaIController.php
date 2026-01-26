<?php
session_start();
include_once '../../models/detalleModel.php';
echo json_encode(DetalleModel::obtenerDetalleProductoInactivo($_SESSION["email"]));