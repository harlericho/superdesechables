<?php

require_once '../../vendor/autoload.php';
require_once '../../config/db.php';
include_once '../../models/facturaModel.php';
include_once '../../models/detalleModel.php';
include_once '../../models/tempModel.php';
include_once '../../models/productoModel.php';
include_once '../../models/clienteModel.php';
include_once '../../models/usuarioModel.php';
include_once '../../models/facturacionElectronicaModel.php';
include_once '../../helpers/facturacion_electronica_service.php';

use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;

function imprimirTicketPOS($facturaId, $usuarioId = null)
{
    try {
        // Leer información de la empresa
        $configPath = dirname(__DIR__, 2) . '/empresa.ini';
        $configEmpresa = parse_ini_file($configPath, true);
        $empresa = $configEmpresa['empresa'] ?? [];

        // Obtener datos de la factura
        $con = Db::dbConnection();

        $sql = "SELECT f.factura_id, f.factura_num_comprobante as numero, f.factura_fecha as fecha,
                       f.factura_subtotal as subtotal, f.factura_impuesto as impuesto, f.factura_total as total,
                       c.cliente_id, c.cliente_nombres as nombres, c.cliente_apellidos as apellidos, 
                       c.cliente_direccion as direccion, c.cliente_telefono as telefono, 
                       COALESCE(tc.tipo_comp_descripcion, 'EFECTIVO') as comprobante
                FROM tbl_factura f
                LEFT JOIN tbl_cliente c ON f.cliente_id = c.cliente_id
                LEFT JOIN tbl_tipo_comprobante tc ON f.tipo_comp_id = tc.tipo_comp_id
                WHERE f.factura_id = :factura_id";

        $stmt = $con->prepare($sql);
        $stmt->execute([':factura_id' => $facturaId]);
        $factura = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$factura) {
            error_log("No se encontró la factura ID: " . $facturaId);
            return false;
        }

        // Obtener detalles
        $sql = "SELECT d.detalle_precio_unit as precio_unitario, d.detalle_cantidad as cantidad,
                       d.detalle_total as precio_total, p.producto_nombre as nombre
                FROM tbl_detalle d
                INNER JOIN tbl_producto p ON d.producto_id = p.producto_id
                WHERE d.factura_id = :factura_id";

        $stmt = $con->prepare($sql);
        $stmt->execute([':factura_id' => $facturaId]);
        $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Construir comandos ESC/POS manualmente
        $ticket = "";
        $ticket .= "\x1B\x40"; // ESC @ - Initialize
        $ticket .= "\x1B\x61\x01"; // ESC a 1 - Center alignment

        // Encabezado de la empresa centrado
        $ticket .= "\x1B\x45\x01"; // Bold ON
        $ticket .= strtoupper($empresa['nombre'] ?? 'EMPRESA') . "\n";
        $ticket .= "\x1B\x45\x00"; // Bold OFF
        $ticket .= ($empresa['direccion'] ?? '') . "\n";
        $ticket .= "Tel: " . ($empresa['telefono'] ?? '') . "\n";
        $ticket .= "\n";

        // Volver a alineación izquierda para el resto del ticket
        $ticket .= "\x1B\x61\x00"; // ESC a 0 - Align LEFT

        if (strpos($factura['numero'], 'TK') !== false) {
            $ticket .= "NOTA DE VENTA\n";
        } else {
            $ticket .= txtL("Factura:", 12) . $factura['numero'] . "\n";
        }
        $ticket .= txtL("Fecha:", 12) . date('d/m/Y', strtotime($factura['fecha'])) . "\n";

        // Cliente
        if ($factura['cliente_id']) {
            $nombreCompleto = substr($factura['nombres'] . " " . $factura['apellidos'], 0, 25);
            $ticket .= txtL("Cliente:", 12) . $nombreCompleto . "\n";
            $direccion = $factura['direccion'] ? substr($factura['direccion'], 0, 25) : "Sin direccion";
            $ticket .= txtL("Direccion:", 12) . $direccion . "\n";
            $ticket .= txtL("Telefono:", 12) . ($factura['telefono'] ?: "000000") . "\n";
        } else {
            $ticket .= txtL("Cliente:", 12) . "Consumidor Final\n";
            $ticket .= txtL("Direccion:", 12) . "Sin direccion\n";
            $ticket .= txtL("Telefono:", 12) . "000000\n";
        }

        // Encabezado productos
        $ticket .= txtL("Item", 17) . txtR("Precio", 9) . txtR("", 3) . txtR("Valor", 9) . "\n";
        $ticket .= str_repeat('-', 40) . "\n";

        // Productos
        foreach ($detalles as $item) {
            $ticket .= txtL($item['nombre'], 17) .
                txtR("$" . number_format($item['precio_unitario'], 2), 9) .
                txtR("x" . $item['cantidad'], 3) .
                txtR("$" . number_format($item['precio_total'], 2), 9) . "\n";
        }

        $ticket .= str_repeat('-', 40) . "\n\n";

        // Totales
        $ticket .= txtL("IVA(0%)", 15) . txtR("$0,00", 12) . txtR("$0,00", 13) . "\n";

        if ($factura['impuesto'] > 0) {
            $iva = ($factura['subtotal'] * $factura['impuesto']) / 100;
            $ticket .= txtL("IVA({$factura['impuesto']}%)", 15) .
                txtR("$" . number_format($factura['subtotal'], 2), 12) .
                txtR("$" . number_format($iva, 2), 13) . "\n";
        }

        $ticket .= txtL("Subtotal.", 20) . txtR("$" . number_format($factura['subtotal'], 2), 20) . "\n";
        $impuestoTotal = ($factura['subtotal'] * $factura['impuesto']) / 100;
        $ticket .= txtL("Impuestos", 20) . txtR("$" . number_format($impuestoTotal, 2), 20) . "\n\n";
        $ticket .= txtL("Total.", 20) . txtR("$" . number_format($factura['total'], 2), 20) . "\n";

        // Pago con negrita
        $ticket .= "\x1B\x45\x01"; // ESC E 1 - Bold ON
        $ticket .= strtoupper($factura['comprobante']) . "\n";
        $ticket .= "\x1B\x45\x00"; // ESC E 0 - Bold OFF
        $ticket .= txtL("Debito:", 10) . txtR("$" . number_format($factura['total'], 2), 15) . "\n";
        $ticket .= txtL("Cambio:", 10) . txtR("$0,00", 15) . "\n\n";

        // Pie
        $nombreCajero = "Administrador";
        if ($usuarioId) {
            $usuario = UsuarioModel::obtenerUsuarioId($usuarioId);
            if ($usuario && isset($usuario[0]['nombres'])) {
                $nombreCajero = $usuario[0]['nombres'];
            }
        }
        $ticket .= txtL("Cajero:", 15) . $nombreCajero . "\n\n";
        $ticket .= "Sistema Facturacion www.solucionesitec.com\n";
        $ticket .= "\n\n\n\n";
        $ticket .= "\x1D\x56\x00"; // GS V 0 - Cut

        // Enviar bytes binarios a impresora compartida
        $tempFile = sys_get_temp_dir() . '\\ticket_' . time() . '.prn';
        $bytesWritten = file_put_contents($tempFile, $ticket);

        error_log("Ticket generado: " . $tempFile . " (" . $bytesWritten . " bytes)");

        // Usar COPY /B a impresora compartida (como funcionó en el test)
        $configPath = dirname(__DIR__, 2) . '/empresa.ini';
        $configEmp = parse_ini_file($configPath, true);
        $impresoraName = $configEmp['empresa']['impresora_ticket'] ?? 'XP-80C';
        $hostname = gethostname();
        $command = 'copy /B "' . $tempFile . '" "\\\\' . $hostname . '\\' . $impresoraName . '" 2>&1';
        error_log("Ejecutando comando: " . $command);

        exec($command, $output, $returnCode);

        error_log("Return code: " . $returnCode);
        error_log("Output: " . implode(", ", $output));

        sleep(1);
        @unlink($tempFile);

        return ($returnCode === 0);
    } catch (Exception $e) {
        error_log("Error al imprimir: " . $e->getMessage());
        return false;
    }
}

function txtL($text, $len)
{
    return str_pad(substr($text, 0, $len), $len, ' ', STR_PAD_RIGHT);
}

function txtR($text, $len)
{
    return str_pad(substr($text, 0, $len), $len, ' ', STR_PAD_LEFT);
}




$factura_subtotal = floatval($_POST['subtotal_factura']);
$factura_descuento = isset($_POST['descuento_global']) ? floatval($_POST['descuento_global']) : 0;
$factura_descuento_porcentaje = isset($_POST['descuento_global_porcentaje']) ? floatval($_POST['descuento_global_porcentaje']) : 0;
$factura_impuesto = floatval($_POST['impuesto_factura']);

$facturaIncluyeIva = (!empty($_POST['incluye_iva']) && $_POST['incluye_iva'] == '1') ? 1 : 0;

if ($facturaIncluyeIva) {
    // Toggle ON: subtotal_factura ya es la base neta (post-descuento, sin IVA).
    // El descuento_global se guarda solo para mostrarlo en PDF, no se vuelve a restar.
    $base = $factura_subtotal;
    // El total real cobrado es la suma exacta de los precios brutos (rawSum - descuento),
    // ya calculada sin divisiones en el frontend (venta.js: totalFinal). Recalcular
    // total = base*(1+IVA/100) a partir del subtotal YA REDONDEADO reintroduce un redondeo
    // doble (dividir para sacar el neto y volver a multiplicar) que puede desviar el total
    // en 1 centavo del monto realmente cobrado — por eso "Forma de Pago" no cuadraba con
    // "VALOR TOTAL". Se usa el total del frontend como fuente de verdad y el IVA se calcula
    // por diferencia, para que base + iva = total cuadre exacto siempre.
    $factura_total = isset($_POST['total_factura']) && $_POST['total_factura'] !== ''
        ? floatval($_POST['total_factura'])
        : round($base + ($base * $factura_impuesto / 100), 2);
    $factura_iva = round($factura_total - $base, 2);
} else {
    $base = $factura_subtotal - $factura_descuento;
    if ($base < 0) $base = 0;
    $factura_iva = ($base * $factura_impuesto) / 100;
    $factura_total = $base + $factura_iva;
}

$arrayName = array(
    'factura_num_comprobante' => $_POST['numero_factura'],
    'factura_fecha' => $_POST['fecha_factura'],
    'factura_impuesto' => $factura_impuesto,
    'factura_incluye_iva' => $facturaIncluyeIva,
    'factura_subtotal' => $factura_subtotal,
    'factura_descuento_global' => $factura_descuento,
    'factura_descuento_global_porcentaje' => $factura_descuento_porcentaje,
    'factura_iva' => $factura_iva,
    'factura_total' =>  $factura_total,
    'cliente_id' => $_POST['cliente'],
    'usuario_id' =>  $_POST['idUsuario'],
    'tipo_comp_id' => $_POST['comprobante']
);

if (FacturaModel::existeFacturaNumComprobante(strtolower($_POST['numero_factura']))) {
    echo json_encode(array('status' => 2));
} else {
    if (FacturaModel::guardarFactura($arrayName)) {
        $numFactura = FacturaModel::maximoFactura();
        foreach (TempModel::obtenerTempDetalle() as $key => $value) {
            // Guardar el detalle de la venta
            $arrayName = array(
                'cantidad' => $value['temp_cantidad_vender'],
                'precio_unitario' => $value['temp_precio_producto'],
                'descuento' => $value['temp_descuento'],
                'precio_total' => $value['temp_total'],
                'factura_id' => $numFactura['factura_id'],
                'producto_id' => $value['temp_id_producto'],
            );
            DetalleModel::guardarDetalle($arrayName);

            // AHORA SÍ descontamos el stock del producto
            $productoActual = ProductoModel::obtenerProductoStockId($value['temp_id_producto']);
            $nuevoStock = $productoActual['producto_stock'] - $value['temp_cantidad_vender'];

            $arrayStock = array(
                'id' => $value['temp_id_producto'],
                'stock' => $nuevoStock
            );
            ProductoModel::actualizarProductoStockId($arrayStock);
        }
        if (TempModel::eliminarDatosTemp()) {
            $es_factura_sri = isset($_POST['es_factura_sri']) ? intval($_POST['es_factura_sri']) : 1;
            
            // FACTURACIÓN ELECTRÓNICA: Verificar si está activa y si se solicitó
            $facturaElectronica = null;
            $configFE = FacturacionElectronicaModel::obtenerConfiguracionActiva();

            if ($configFE && $es_factura_sri === 1) {
                try {
                    // Procesar factura electrónica automáticamente
                    $resultado = FacturacionElectronicaService::procesarFacturaElectronica(
                        $numFactura['factura_id'],
                        $_POST['idUsuario']
                    );

                    if ($resultado['success']) {
                        $estadoSRI = $resultado['estado_sri'] ?? 'PENDIENTE';
                        $facturaElectronica = [
                            'autorizado' => $resultado['autorizado'] ?? false,
                            'clave_acceso' => $resultado['clave_acceso'],
                            'numero_autorizacion' => $resultado['numero_autorizacion'] ?? null,
                            'fecha_autorizacion' => $resultado['fecha_autorizacion'] ?? null,
                            'estado_sri' => $estadoSRI,
                            'mensaje' => $estadoSRI !== 'AUTORIZADO'
                                ? 'Factura guardada pero el SRI respondió con estado: ' . $estadoSRI . '. Puede reenviarla desde el módulo de facturas.'
                                : null
                        ];
                    } else {
                        // Si falla, registrar error pero NO bloquear la venta
                        error_log("Error FE: " . $resultado['mensaje']);
                        $facturaElectronica = [
                            'autorizado' => false,
                            'error' => $resultado['mensaje']
                        ];
                    }
                } catch (Exception $e) {
                    error_log("Excepción FE: " . $e->getMessage());
                    $facturaElectronica = [
                        'autorizado' => false,
                        'error' => $e->getMessage()
                    ];
                }
            }

            // Incrementar el secuencial DESPUÉS de procesar la factura electrónica
            if ($es_factura_sri === 1) {
                FacturaModel::aumentarSecuencialSerie();
            } else {
                FacturaModel::aumentarSecuencialTicket();
            }

            // Imprimir ticket si está marcado el checkbox
            $ticketImpreso = false;

            if (isset($_POST['imprimir_ticket'])) {
                $usuarioId = isset($_POST['idUsuario']) ? $_POST['idUsuario'] : null;
                $ticketImpreso = imprimirTicketPOS($numFactura['factura_id'], $usuarioId);
            }

            echo json_encode(array(
                'status' => 1,
                'factura_id' => $numFactura['factura_id'],
                'ticket_impreso' => $ticketImpreso,
                'factura_electronica' => $facturaElectronica
            ));
        } else {
            echo json_encode(array('status' => 0));
        }
    }
}
