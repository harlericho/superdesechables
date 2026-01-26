<?php
session_start();
include_once '../../models/facturaModel.php';
echo json_encode(FacturaModel::obtenerFacturasInactivas($_SESSION['email']));