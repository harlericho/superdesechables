<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <?php require_once '../config/empresa.php'; ?>
  <title><?= Empresa::getNombre() ?></title>
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

  <!-- Select2 -->
  <link rel="stylesheet" href="../assets/bower_components/select2/dist/css/select2.min.css">

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
  </style>
</head>

<body class="hold-transition login-page  ">
  <div class="login-box">
    <div class="login-logo">
      <a href="#">
        <img src="../assets/image/<?= Empresa::getLogoLogin() ?>" alt="logo" width="80">
        <b><?= Empresa::getTitulo1() ?></b><?= Empresa::getTitulo2() ?></a>
    </div>
    <!-- /.login-logo -->
    <div class="login-box-body">
      <p class="login-box-msg">Iniciar Sessión</p>

      <!-- Mensaje de sesión expirada -->
      <?php if (isset($_GET['session']) && $_GET['session'] == 'expired'): ?>
        <div class="alert alert-warning alert-dismissible">
          <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
          <h4><i class="icon fa fa-clock-o"></i> Sesión expirada</h4>
          Su sesión ha expirado por inactividad. Por favor, inicie sesión nuevamente.
        </div>
      <?php endif; ?>

      <form action="javascript:void(0);" method="post" onsubmit="app.login()">
        <div class="form-group has-feedback">
          <input type="email" class="form-control" placeholder="Email" name="email" autofocus required>
          <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
        </div>
        <div class="form-group has-feedback" style="position: relative;">
          <input type="password" class="form-control" placeholder="Password" name="password" id="passwordField" required style="padding-right: 45px;">
          <span class="glyphicon glyphicon-lock form-control-feedback" style="right: 35px;"></span>
          <span class="password-toggle" onclick="togglePassword()"
            style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #666; z-index: 10; padding: 5px;"
            onmouseover="this.style.color='#333'"
            onmouseout="this.style.color='#666'">
            <i class="fa fa-eye" id="eyeIcon"></i>
          </span>
        </div>
        <div class="row">
          <!-- /.col -->
          <div class="col-xs-6">
            <button type="submit" class="btn btn-primary btn-block btn-flat">
              <i class="fa fa-arrow-circle-right"></i>
              Ingresar</button>
          </div>
          <!-- /.col -->
        </div>
      </form>
      <!-- Enlace para recuperar contraseña -->
      <div class="text-center" style="margin-top: 15px;">
        <a href="#" onclick="mostrarRecuperarPassword()" style="color: #3498db; text-decoration: none; font-size: 14px;"
          onmouseover="this.style.textDecoration='underline'"
          onmouseout="this.style.textDecoration='none'">
          <i class="fa fa-key" style="margin-right: 5px;"></i>
          ¿Olvidaste tu contraseña?
        </a>
      </div>

      <script>
        function mostrarRecuperarPassword() {
          swal({
            title: 'Recuperar Contraseña',
            text: 'Contacta al administrador del sistema para recuperar tu contraseña.',
            icon: 'info',
            button: 'Entendido'
          });
        }

        function togglePassword() {
          const passwordField = document.getElementById('passwordField');
          const eyeIcon = document.getElementById('eyeIcon');

          if (passwordField.type === 'password') {
            passwordField.type = 'text';
            eyeIcon.className = 'fa fa-eye-slash';
            eyeIcon.style.color = '#3498db';
          } else {
            passwordField.type = 'password';
            eyeIcon.className = 'fa fa-eye';
            eyeIcon.style.color = '#666';
          }
        }
      </script>
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
      <!-- <br/>
                <a href="registro.php" class="text-center">Registrarse</a> -->
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
  <script src="../code/login.js"></script>
</body>

</html>