<?php
// controllers/factura/facturaTicketDataController.php
// Devuelve el ticket en texto plano para impresión con QZ Tray
header('Content-Type: application/json');

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/detalleModel.php';
require_once __DIR__ . '/../../models/usuarioModel.php';

if (!isset($_GET['factura_id'])) {
  echo json_encode(['error' => 'Falta el parámetro factura_id']);
  exit;
}
$factura_id = intval($_GET['factura_id']);

// Leer información de la empresa desde empresa.ini (igual que facturaGuardarController.php)
$configPath = dirname(__DIR__, 2) . '/empresa.ini';
$configEmpresa = parse_ini_file($configPath, true);
$empresa = $configEmpresa['empresa'] ?? [
  'nombre' => 'EMPRESA',
  'direccion' => '',
  'telefono' => ''
];

// Obtener datos de la factura
$con = Db::dbConnection();
$sql = "SELECT f.factura_id, f.factura_num_comprobante as numero, f.factura_fecha as fecha,
        f.usuario_id, 
         f.factura_subtotal as subtotal, f.factura_impuesto as impuesto, f.factura_total as total,
         c.cliente_id, c.cliente_nombres as nombres, c.cliente_apellidos as apellidos, 
         c.cliente_direccion as direccion, c.cliente_telefono as telefono, 
         COALESCE(tc.tipo_comp_descripcion, 'EFECTIVO') as comprobante
    FROM tbl_factura f
    LEFT JOIN tbl_cliente c ON f.cliente_id = c.cliente_id
    LEFT JOIN tbl_tipo_comprobante tc ON f.tipo_comp_id = tc.tipo_comp_id
    WHERE f.factura_id = :factura_id";
$stmt = $con->prepare($sql);
$stmt->execute([':factura_id' => $factura_id]);
$factura = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$factura) {
  echo json_encode(['error' => 'Factura no encontrada']);
  exit;
}

// obtener usuario_id
$usuarioId = isset($factura['usuario_id']) ? intval($factura['usuario_id']) : null;

// Obtener detalles de productos
$sql = "SELECT d.detalle_precio_unit as precio_unitario, d.detalle_cantidad as cantidad,
               d.detalle_total as precio_total, p.producto_nombre as nombre
        FROM tbl_detalle d
        INNER JOIN tbl_producto p ON d.producto_id = p.producto_id
        WHERE d.factura_id = :factura_id";
$stmt = $con->prepare($sql);
$stmt->execute([':factura_id' => $factura_id]);
$detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Funciones de formato
function txtL($text, $len)
{
  return str_pad(substr($text, 0, $len), $len, ' ', STR_PAD_RIGHT);
}
function txtR($text, $len)
{
  return str_pad(substr($text, 0, $len), $len, ' ', STR_PAD_LEFT);
}



$ticket = '';
$ticket .= "\x1B\x40"; // ESC @ - Inicializar
$ticket .= "\x1B\x61\x01"; // ESC a 1 - Centrar
$ticket .= "\x1B\x45\x01"; // ESC E 1 - Negrita ON
$ticket .= "Chimbolema Guaman Segundo Manuel\n";
// agregar una linea con el nombre: CONTRIBUYENTE NEGOCIO POPULAR - REGIMEN RIMPE
$ticket .= "CONTRIBUYENTE NEGOCIO POPULAR - REGIMEN RIMPE\n";
$ticket .= "\x1B\x45\x00"; // ESC E 0 - Negrita OFF
$ticket .= ($empresa['direccion'] ?? '') . "\n";
$ticket .= "RUC: " . ($empresa['ruc'] ?? '') . "\n";
$ticket .= "AUTSRI: " . ($empresa['autsri'] ?? '') . "\n";
$ticket .= "Tel: " . ($empresa['telefono'] ?? '') . "\n";
$ticket .= "Email: segundochimbolema781@gmail.com\n";
$ticket .= "\n";
$ticket .= "\x1B\x61\x00"; // ESC a 0 - Alinear a la izquierda

// Encabezado de recibo
$ticket .= txtL("No. Factura:", 12) . $factura['numero'] . "\n";
$ticket .= txtL("Fecha:", 12) . date('d/m/Y', strtotime($factura['fecha'])) . "\n";

// Cliente
if ($factura['cliente_id']) {
  $nombreCompleto = substr($factura['nombres'] . " " . $factura['apellidos'], 0, 25);
  $ticket .= txtL("Cliente:", 12) . $nombreCompleto . "\n";
  $direccion = $factura['direccion'] ? substr($factura['direccion'], 0, 25) : "Sin direccion";
  $ticket .= txtL("Direccion:", 12) . $direccion . "\n";
  $ticket .= txtL("Telefono:", 12) . ($factura['telefono'] ?: "000000") . "\n\n";
} else {
  $ticket .= txtL("Cliente:", 12) . "Consumidor Final\n";
  $ticket .= txtL("Direccion:", 12) . "Sin direccion\n";
  $ticket .= txtL("Telefono:", 12) . "000000\n\n";
}

// Encabezado productos
$ticket .= txtL("Cant", 5) .
  txtL("Producto", 15) .
  txtR("V.Unit", 10) .
  txtR("Total", 10) . "\n";
$ticket .= str_repeat('-', 40) . "\n";


// Productos
foreach ($detalles as $item) {

  $cantidad = $item['cantidad'];
  $producto = substr($item['nombre'], 0, 15); // recorte para ticket
  $vunit = "$" . number_format($item['precio_unitario'], 2);
  $total = "$" . number_format($item['precio_total'], 2);

  $ticket .= txtL($cantidad, 5) .
    txtL($producto, 15) .
    txtR($vunit, 10) .
    txtR($total, 10) . "\n";
}

$ticket .= str_repeat('-', 40) . "\n\n";

// Totales
$ticket .= txtL("Subtotal.", 20) . txtR("$" . number_format($factura['subtotal'], 2), 20) . "\n";
$ticket .= txtL("Descuento", 15) . txtR("$0,00", 12) . txtR("$0,00", 13) . "\n";


if ($factura['impuesto'] > 0) {
  $iva = ($factura['subtotal'] * $factura['impuesto']) / 100;
  $ticket .= txtL("IVA(15%)", 15) .
    txtR("$" . number_format($factura['subtotal'], 2), 12) .
    txtR("$" . number_format($iva, 2), 13) . "\n";
} else {
  $ticket .= txtL("IVA(15%)", 15) . txtR("$0,00", 12) . txtR("$0,00", 13) . "\n";
}


$impuestoTotal = ($factura['subtotal'] * $factura['impuesto']) / 100;
//$ticket .= txtL("Impuestos", 20) . txtR("$" . number_format($impuestoTotal, 2), 20) . "\n\n";
$ticket .= txtL("Total.", 20) . txtR("$" . number_format($factura['total'], 2), 20) . "\n\n";

// Pago
$ticket .= strtoupper($factura['comprobante']) . "\n";
$ticket .= txtL("Debito:", 10) . txtR("$" . number_format($factura['total'], 2), 15) . "\n";

// Pie
$nombreCajero = "Administrador";
if ($usuarioId) {
  $usuario = UsuarioModel::obtenerUsuarioId($usuarioId);
  if ($usuario && isset($usuario[0]['nombres'])) {
    $nombreCajero = $usuario[0]['nombres'];
  }
}
$ticket .= txtL("Cajero:", 15) . $nombreCajero . "\n\n";
$ticket .= "Terminos y condiciones: 'El cliente debe revisar el producto que recibe, una vez recibida la factura no se recibiran reclamos referentes o atribuibles'.\n";
$ticket .= "Sistema desarrollado por: www.solucionesitec.com\n";
$ticket .= "\n\n\n\n";
// Comando de corte ESC/POS (GS V 0)
$ticket .= "\x1D\x56\x00";

echo json_encode(['ticket_escpos' => $ticket]);
