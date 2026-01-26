<?php
session_start();
include_once '../../models/detalleModel.php';
echo json_encode(DetalleModel::obtenerDetalleProductoActivo($_SESSION["email"]));