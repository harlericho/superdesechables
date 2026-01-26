<?php
include_once '../../models/facturaModel.php';
include_once '../../models/clienteModel.php';
include_once '../../models/productoModel.php';
include_once '../../models/proveedorModel.php';
include_once '../../models/rolModel.php';
include_once '../../models/usuarioModel.php';
include_once '../../models/categoriaModel.php';
session_start();
foreach (UsuarioModel::obtenerDatoUsuario($_SESSION['email']) as $key => $value) {
  $rolDescripcion = $value['rol_descripcion'];
}
if ($rolDescripcion == 'ADMINISTRADOR') {
  $numFacturasA = FacturaModel::contarFacturasActivasGeneral();
  $numFacturasI = FacturaModel::contarFacturasInactivasGeneral();
} else {
  $numFacturasA = FacturaModel::contarFacturasActivas($_SESSION["email"]);
  $numFacturasI = FacturaModel::contarFacturasInactivas($_SESSION["email"]);
}
$numClientes = ClienteModel::contarClientes();
$numProductos = ProductoModel::contarProductos();
$stockProductos = ProductoModel::stockProductoInferior();
$totalDineroFacturas = FacturaModel::dineroTotalFacturas();
$numProveedores = ProveedorModel::contarProveedores();
$numRoles = RolModel::contarRoles();
$numUsuarios = UsuarioModel::contarUsuarios();
$numCategorias = CategoriaModel::contarCategorias();


$arrayName = array(
  'numFacturasA' => $numFacturasA['numFacturasA'],
  'numFacturasI' => $numFacturasI['numFacturasI'],
  'ventas' => $totalDineroFacturas['totalFacturas'],
  'numClientes' => $numClientes['numClientes'],
  'numProductos' => $numProductos['numProductos'],
  'stockProductos' => $stockProductos['stockProductos'],
  'numProveedores' => $numProveedores['numProveedores'],
  'numRoles' => $numRoles['numRoles'],
  'numUsuarios' => $numUsuarios['numUsuarios'],
  'numCategorias' => $numCategorias['numCategorias'],

);
echo json_encode($arrayName);
