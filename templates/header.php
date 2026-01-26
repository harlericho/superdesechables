<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <?php
  date_default_timezone_set('America/Guayaquil'); // Zona horaria para todo el proyecto
  require_once '../config/empresa.php';
  ?>
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
  <!-- AdminLTE Skins. Choose a skin from the css/skins
       folder instead of downloading all of them to reduce the load. -->
  <link rel="stylesheet" href="../assets/dist/css/skins/_all-skins.min.css">
  <!-- Morris chart -->
  <link rel="stylesheet" href="../assets/bower_components/morris.js/morris.css">
  <!-- jvectormap -->
  <link rel="stylesheet" href="../assets/bower_components/jvectormap/jquery-jvectormap.css">
  <!-- Date Picker -->
  <link rel="stylesheet" href="../assets/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css">
  <!-- Daterange picker -->
  <link rel="stylesheet" href="../assets/bower_components/bootstrap-daterangepicker/daterangepicker.css">
  <!-- bootstrap wysihtml5 - text editor -->
  <link rel="stylesheet" href="../assets/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css">
  <!-- Select2 -->
  <link rel="stylesheet" href="../assets/bower_components/select2/dist/css/select2.min.css">

  <!-- DataTables -->
  <link rel="stylesheet" href="../assets/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css">

  <!-- Google Font -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">

  <!-- Favicon -->
  <link rel="shortcut icon" href="../assets/image/SuperDesechablesLogo.PNG" type="image/x-icon">
</head>

<?php
include_once '../models/loginModel.php';
include_once '../config/session.php';

// Inicializar sesión con configuración de seguridad
SessionManager::initSession();

// Verificar si la sesión ha expirado
if (SessionManager::checkSessionExpiration()) {
  header('Location: ../index.html?session=expired');
  exit();
}

// Verificar si el usuario está autenticado
if (!isset($_SESSION['email'])) {
  header('Location: ../index.html');
  exit();
}

foreach (LoginModel::existeUsuarioEmailLogin($_SESSION['email']) as $key => $value) {
  $rolDescripcion = $value['rol_descripcion'];
}
?>
<?php
if ($rolDescripcion == 'ADMINISTRADOR') {
?>

  <body class="hold-transition skin-purple sidebar-mini">
  <?php
}
  ?>

  <body class="hold-transition skin-blue sidebar-mini">