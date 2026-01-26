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
</head>

<body class="hold-transition login-page  ">
  <div class="login-box">
    <div class="login-logo">
      <a href="#">
        <img src="../assets/image/SuperDesechablesLogo.PNG" alt="logo" width="80">
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
        <div class="form-group has-feedback">
          <input type="password" class="form-control" placeholder="Password" name="password" required>
          <span class="glyphicon glyphicon-lock form-control-feedback"></span>
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
        <!-- agregar este sistema fu creado por soluionesitec.com  footer-->
        <br />
        <footer class="text-center">
          <small>
            &copy; <?= date('Y') ?> SolucionesITEC
            <br />
            Desarrollado por <a href="https://solucionesitec.com" target="_blank">Soluciones ITEC</a>
          </small>
        </footer>
      </form>
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