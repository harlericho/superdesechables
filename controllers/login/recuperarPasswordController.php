<?php
require_once '../../vendor/autoload.php';
require_once '../../config/db.php';
require_once '../../config/empresa.php';
require_once '../../config/encryption.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Establecer zona horaria de Ecuador
date_default_timezone_set('America/Guayaquil');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = isset($_POST['email']) ? trim(strtolower($_POST['email'])) : '';

  // Validar que el email no esté vacío
  if (empty($email)) {
    echo json_encode([
      'status' => 'error',
      'message' => 'El correo electrónico es requerido'
    ]);
    exit;
  }

  // Validar formato de email
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
      'status' => 'error',
      'message' => 'El formato del correo electrónico no es válido'
    ]);
    exit;
  }

  try {
    // Buscar el usuario en la base de datos
    $sql = "SELECT u.usuario_id, u.usuario_nombres, u.usuario_email, u.usuario_password, u.usuario_estado, r.rol_descripcion
            FROM tbl_usuario u
            INNER JOIN tbl_rol r ON u.rol_id = r.rol_id
            WHERE u.usuario_email = :email";

    $query = Db::dbConnection()->prepare($sql);
    $query->bindParam(':email', $email, PDO::PARAM_STR);
    $query->execute();
    $usuario = $query->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
      echo json_encode([
        'status' => 'not_found',
        'message' => 'No existe un usuario registrado con este correo electrónico'
      ]);
      exit;
    }

    // Verificar si el usuario está activo
    if ($usuario['usuario_estado'] != '1') {
      echo json_encode([
        'status' => 'error',
        'message' => 'Este usuario no está activo. Contacta al administrador del sistema.'
      ]);
      exit;
    }

    // Desencriptar la contraseña
    $passwordDesencriptado = Encryption::_desencryptacion($usuario['usuario_password']);

    // Obtener información de la empresa
    $nombreEmpresa = Empresa::getNombre();
    $rucEmpresa = Empresa::getRuc();
    $telefonoEmpresa = Empresa::getTelefono();

    // Configuración del correo
    $config = require '../../config/mailer.php';

    // Crear instancia de PHPMailer
    $mail = new PHPMailer(true);

    try {
      // Configuración del servidor SMTP
      $mail->isSMTP();
      $mail->Host = $config['host'];
      $mail->SMTPAuth = true;
      $mail->Username = $config['username'];
      $mail->Password = $config['password'];
      $mail->SMTPSecure = $config['secure'];
      $mail->Port = $config['port'];
      $mail->CharSet = 'UTF-8';

      // Remitente
      $mail->setFrom($config['from'], $config['from_name']);

      // Destinatario
      $mail->addAddress('info@solucionesitec.com', 'Administrador del Sistema');

      // Contenido del correo
      $mail->isHTML(true);
      $mail->Subject = 'Solicitud de Recuperación de Contraseña - ' . $nombreEmpresa;

      $mensajeHTML = '
      <!DOCTYPE html>
      <html>
      <head>
        <meta charset="UTF-8">
        <style>
          body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
          }
          .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 10px;
          }
          .header {
            background-color: #3498db;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 10px 10px 0 0;
          }
          .content {
            background-color: white;
            padding: 30px;
            border-radius: 0 0 10px 10px;
          }
          .info-box {
            background-color: #ecf0f1;
            padding: 15px;
            margin: 15px 0;
            border-left: 4px solid #3498db;
            border-radius: 5px;
          }
          .info-label {
            font-weight: bold;
            color: #2c3e50;
          }
          .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #7f8c8d;
            font-size: 12px;
          }
          .alert {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
          }
        </style>
      </head>
      <body>
        <div class="container">
          <div class="header">
            <h2>🔐 Solicitud de Recuperación de Contraseña</h2>
          </div>
          <div class="content">
            <p>Se ha recibido una solicitud de recuperación de contraseña desde el sistema de gestión.</p>
            
            <div class="alert">
              <strong>⚠️ Atención:</strong> Un usuario ha solicitado recuperar su contraseña. Por favor, verifica la información y proporciona asistencia.
            </div>

            <h3>Información de la Empresa:</h3>
            <div class="info-box">
              <p><span class="info-label">Empresa:</span> ' . htmlspecialchars($nombreEmpresa) . '</p>
              <p><span class="info-label">RUC:</span> ' . htmlspecialchars($rucEmpresa) . '</p>
              <p><span class="info-label">Teléfono:</span> ' . htmlspecialchars($telefonoEmpresa) . '</p>
            </div>

            <h3>Información del Usuario:</h3>
            <div class="info-box">
              <p><span class="info-label">Nombre:</span> ' . htmlspecialchars($usuario['usuario_nombres']) . '</p>
              <p><span class="info-label">Correo Electrónico:</span> ' . htmlspecialchars($usuario['usuario_email']) . '</p>
              <p><span class="info-label">Rol:</span> ' . htmlspecialchars($usuario['rol_descripcion']) . '</p>
              <p><span class="info-label">Contraseña Actual:</span> <code style="background-color: #f0f0f0; padding: 5px; border-radius: 3px;">' . htmlspecialchars($passwordDesencriptado) . '</code></p>
            </div>

            <h3>Detalles de la Solicitud:</h3>
            <div class="info-box">
              <p><span class="info-label">Fecha y Hora:</span> ' . date('d/m/Y H:i:s') . '</p>
              <p><span class="info-label">IP del Cliente:</span> ' . $_SERVER['REMOTE_ADDR'] . '</p>
            </div>

            <p><strong>Acción requerida:</strong> Por favor, contacta al usuario para verificar su identidad y proporcionar asistencia con la recuperación de su contraseña.</p>

            <div class="footer">
              <p>Este es un correo automático generado por el Sistema de Gestión Comercial.</p>
              <p>&copy; ' . date('Y') . ' ' . htmlspecialchars($nombreEmpresa) . ' - Todos los derechos reservados</p>
            </div>
          </div>
        </div>
      </body>
      </html>
      ';

      $mail->Body = $mensajeHTML;

      // Texto alternativo para clientes de correo que no soportan HTML
      $mail->AltBody = "Solicitud de Recuperación de Contraseña\n\n" .
        "Empresa: $nombreEmpresa\n" .
        "RUC: $rucEmpresa\n" .
        "Teléfono: $telefonoEmpresa\n\n" .
        "Usuario: {$usuario['usuario_nombres']}\n" .
        "Correo: {$usuario['usuario_email']}\n" .
        "Rol: {$usuario['rol_descripcion']}\n" .
        "Contraseña: $passwordDesencriptado\n\n" .
        "Fecha: " . date('d/m/Y H:i:s');

      // Enviar el correo
      $mail->send();

      echo json_encode([
        'status' => 'success',
        'message' => 'Solicitud enviada exitosamente. El administrador se pondrá en contacto contigo pronto.'
      ]);
    } catch (Exception $e) {
      error_log("Error al enviar correo de recuperación: " . $mail->ErrorInfo);
      echo json_encode([
        'status' => 'error',
        'message' => 'Error al enviar el correo. Por favor contacta directamente al administrador.'
      ]);
    }
  } catch (PDOException $e) {
    error_log("Error en la base de datos: " . $e->getMessage());
    echo json_encode([
      'status' => 'error',
      'message' => 'Error al procesar la solicitud. Por favor intenta nuevamente.'
    ]);
  }
} else {
  echo json_encode([
    'status' => 'error',
    'message' => 'Método no permitido'
  ]);
}
