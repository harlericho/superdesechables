<?php
// helpers/mailer_helper.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/empresa.php';

function enviarFacturaPorCorreo($destinatario, $pdfString, $nombreArchivo = 'factura.pdf')
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
    $mail->Body    = '<p>Estimado cliente,</p>'
      . '<p>Gracias por su compra en <strong>' . Empresa::getNombre() . '</strong>.</p>'
      . '<p>Adjunto encontrará su factura electrónica en formato PDF.</p>'
      . '<p>Si tiene alguna consulta, no dude en contactarnos respondiendo a este correo.</p>'
      . '<br><p>Saludos cordiales,<br><strong>' . Empresa::getNombre() . '</strong></p>';
    $mail->addStringAttachment($pdfString, $nombreArchivo);
    $mail->send();
    return true;
  } catch (Exception $e) {
    error_log('Mailer Error: ' . $mail->ErrorInfo);
    return false;
  }
}
