<?php
// helpers/mailer_helper.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/empresa.php';

function enviarFacturaPorCorreo($destinatario, $pdfString, $nombreArchivo = 'factura.pdf', $datosFactura = [], $xmlString = null)
{
  $config = require __DIR__ . '/../config/mailer.php';
  $mail = new PHPMailer(true);
  try {
    $mail->isSMTP();
    $mail->Host = $config['host'];
    $mail->SMTPAuth = true;
    $mail->Username = $config['username'];
    $mail->Password = $config['password'];
    $mail->SMTPSecure = $config['secure'];
    $mail->Port = $config['port'];
    $mail->setFrom($config['from'], $config['from_name']);
    $mail->addAddress($destinatario);
    $mail->CharSet = 'UTF-8';
    $mail->isHTML(true);
    $mail->Subject = 'Factura electrónica de ' . Empresa::getNombre();
    // Extraer datos de la factura
    $numFactura = isset($datosFactura['numero']) ? htmlspecialchars($datosFactura['numero']) : '';
    $fechaFactura = isset($datosFactura['fecha']) ? htmlspecialchars($datosFactura['fecha']) : '';
    $montoFactura = isset($datosFactura['monto']) ? htmlspecialchars($datosFactura['monto']) : '';
    $clienteNombre = isset($datosFactura['cliente']) ? htmlspecialchars($datosFactura['cliente']) : '';

    $mail->Body = '
    <div style="background:#f4f6f8;padding:30px 0;">
      <div style="max-width:600px;margin:0 auto;background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 2px 8px #e0e0e0;">
        <div style="background:#3498db;color:#fff;padding:24px 20px 18px 20px;text-align:center;">
          <h2 style="margin:0;font-size:22px;">🧾 Factura Electrónica</h2>
        </div>
        <div style="padding:30px 25px 10px 25px;">
          <p style="font-size:16px;color:#333;margin-bottom:18px;">Estimado cliente' . ($clienteNombre ? ", <b>" . $clienteNombre . "</b>" : "") . ',</p>
          <p style="font-size:15px;color:#333;margin-bottom:18px;">Gracias por su compra en <b>' . htmlspecialchars(Empresa::getNombre()) . '</b>. Adjuntamos su factura electrónica en formato PDF.</p>
          <div style="background:#f1f7fa;padding:18px 15px;border-radius:7px;margin-bottom:18px;">
            <b style="color:#2c3e50;">Datos de la Empresa:</b><br>
            <span style="color:#555;">Empresa: <b>' . htmlspecialchars(Empresa::getNombre()) . '</b></span><br>
            <span style="color:#555;">RUC: <b>' . htmlspecialchars(Empresa::getRuc()) . '</b></span><br>
            <span style="color:#555;">Teléfono: <b>' . htmlspecialchars(Empresa::getTelefono()) . '</b></span>
          </div>
          <div style="background:#eaf6e8;padding:15px 15px;border-radius:7px;margin-bottom:18px;">
            <b style="color:#2c3e50;">Detalles de la Factura:</b><br>
            <span style="color:#555;">Número: <b>' . $numFactura . '</b></span><br>
            <span style="color:#555;">Fecha de emisión: <b>' . $fechaFactura . '</b></span><br>
            <span style="color:#555;">Monto total: <b>$ ' . $montoFactura . '</b></span>
          </div>
          <p style="font-size:14px;color:#333;margin-bottom:18px;">Si tiene alguna consulta, no dude en contactarnos respondiendo a este correo.</p>
          <p style="font-size:15px;color:#333;margin-bottom:0;">Saludos cordiales,<br><b>' . htmlspecialchars(Empresa::getNombre()) . '</b></p>
        </div>
        <div style="background:#f8f9fa;text-align:center;padding:15px 10px 10px 10px;border-top:1px solid #e0e0e0;">
          <span style="color:#888;font-size:12px;">Este es un correo automático generado por el Sistema de Gestión Comercial.<br>&copy; ' . date('Y') . ' ' . htmlspecialchars(Empresa::getNombre()) . ' - Todos los derechos reservados</span>
        </div>
      </div>
    </div>';
    $mail->addStringAttachment($pdfString, $nombreArchivo, 'base64', 'application/pdf');
    if (!empty($xmlString)) {
      $xmlFilename = preg_replace('/\.pdf$/i', '.xml', $nombreArchivo);
      $mail->addStringAttachment($xmlString, $xmlFilename, 'base64', 'application/xml');
    }
    $mail->send();
    return true;
  } catch (Exception $e) {
    error_log('Mailer Error: ' . $mail->ErrorInfo);
    return false;
  }
}
