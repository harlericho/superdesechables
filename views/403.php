<?php require_once '../templates/header.php'; ?>
<?php require_once '../templates/sidebar.php'; ?>

<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>
    Acceso Denegado
  </h1>
  <ol class="breadcrumb">
    <li><a href="./index.php"><i class="fa fa-dashboard"></i> Inicio</a></li>
    <li class="active">Acceso Denegado</li>
  </ol>
</section>

<!-- Main content -->
<section class="content">
  <div class="error-page">
    <h2 class="headline text-red"> <i class="fa fa-ban"></i></h2>

    <div class="error-content">
      <h3><i class="fa fa-warning text-red"></i> ¡Acceso restringido!</h3>
      <p>
        No tienes permisos para acceder a esta sección. Por favor, contacta con el administrador si crees que esto es un error.
        <br>
        Puedes volver al <a href="./index.php">panel de inicio</a>.
      </p>
    </div>
    <!-- /.error-content -->
  </div>
  <!-- /.error-page -->
</section>
<!-- /.content -->