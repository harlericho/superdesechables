<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <?php require_once '../config/empresa.php'; ?>
  <title><?= Empresa::getNombre() ?> - Recuperar Contraseña</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Bootstrap 3.3.7 -->
  <link rel="stylesheet" href="../assets/bower_components/bootstrap/dist/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../assets/bower_components/font-awesome/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="../assets/bower_components/Ionicons/css/ionicons.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../assets/dist/css/AdminLTE.min.css">
  <!-- iCheck -->
  <link rel="stylesheet" href="../assets/plugins/iCheck/square/blue.css">

  <!-- Favicon -->
  <link rel="shortcut icon" href="../assets/image/SuperDesechablesLogo.PNG" type="image/x-icon">
  <style>
    .login-logo {
      text-align: center;
    }

    .login-logo a {
      display: inline-block;
      max-width: 100%;
      word-wrap: break-word;
      line-height: 1.2;
    }

    @media (max-width: 480px) {
      .login-box {
        width: 90% !important;
        margin: 5% auto !important;
      }
    }

    .back-to-login {
      margin-top: 15px;
      text-align: center;
    }

    .back-to-login a {
      color: #3498db;
      text-decoration: none;
      font-size: 14px;
    }

    .back-to-login a:hover {
      text-decoration: underline;
    }
  </style>
</head>

<body class="hold-transition login-page">
  <div class="login-box">
    <div class="login-logo">
      <a href="#">
        <img src="../assets/image/<?= Empresa::getLogoLogin() ?>" alt="logo" width="80">
        <b><?= Empresa::getTitulo1() ?></b><?= Empresa::getTitulo2() ?></a>
    </div>
    <!-- /.login-logo -->
    <div class="login-box-body">
      <p class="login-box-msg">Recuperar Contraseña</p>

      <div class="alert alert-info">
        <i class="fa fa-info-circle"></i>
        Ingresa tu correo electrónico registrado. Se enviará una solicitud al administrador del sistema.
      </div>

      <form action="javascript:void(0);" method="post" onsubmit="app.recuperarPassword()">
        <div class="form-group has-feedback">
          <input type="email" class="form-control" placeholder="Ingresa tu correo electrónico" name="email" id="emailRecuperar" autofocus required>
          <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
        </div>

        <div class="row">
          <div class="col-xs-12">
            <button type="submit" class="btn btn-primary btn-block btn-flat">
              <i class="fa fa-paper-plane"></i>
              Enviar Solicitud
            </button>
          </div>
        </div>
      </form>

      <!-- Enlace para volver al login -->
      <div class="back-to-login">
        <a href="login.php">
          <i class="fa fa-arrow-circle-left" style="margin-right: 5px;"></i>
          Volver al inicio de sesión
        </a>
      </div>

      <!-- Footer del sistema -->
      <div class="text-center" style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px; border-top: 3px solid #3498db;">
        <div style="margin-bottom: 8px;">
          <small style="color: #666; font-weight: 600;">
            &copy; <?= date('Y') ?> <b style="color: #3498db;"><?= Empresa::getNombre() ?></b>
          </small>
        </div>
        <div>
          <small style="color: #888; font-size: 12px;">
            Sistema de Gestión Comercial v<?= Empresa::getVersion() ?> | Todos los derechos reservados
          </small>
        </div>
      </div>
    </div>
    <!-- /.login-box-body -->
  </div>
  <!-- /.login-box -->

  <!-- jQuery 3 -->
  <script src="../assets/bower_components/jquery/dist/jquery.min.js"></script>
  <!-- Bootstrap 3.3.7 -->
  <script src="../assets/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
  <!-- iCheck -->
  <script src="../assets/plugins/iCheck/icheck.min.js"></script>
  <!-- SweetAlert2 -->
  <script src="../assets/plugins/sweetalert/sweetalert.min.js"></script>
  <script src="../code/recuperar_password.js"></script>
</body>

</html>