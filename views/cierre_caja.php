<?php require_once '../templates/header.php';
date_default_timezone_set('America/Guayaquil'); ?>
<div class="wrapper">
  <?php require_once '../templates/sidebar.php'; ?>

  <?php
  foreach (LoginModel::existeUsuarioEmailLogin($_SESSION['email']) as $key => $value) {
    $rolDescripcion = $value['rol_descripcion'];
  }
  ?>
  <?php
  if ($rolDescripcion == 'ADMINISTRADOR') {
  ?>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
      <!-- Content Header (Page header) -->
      <section class="content-header">
        <h1>
          Cierre Caja Chica
          <small>Panel de control</small>
        </h1>
        <?php require_once '../templates/panel.php'; ?>
      </section>

      <!-- Main content -->
      <section class="content">
        <!-- Main row -->
        <div class="row">
          <!-- left column -->
          <div class="col-md-8">
            <!-- general form elements -->
            <div class="box box-success">
              <div class="box-header with-border">
                <h3 class="box-title">Cierre Caja Chica</h3>
              </div>
              <!-- form start -->
              <form role="form" id="formCierreCajaChica" action="javascript:void(0);" method="POST" onsubmit="app.guardar()">
                <div class="box-body">
                  <input type="hidden" name="id">
                  <!-- /.form-group -->
                  <div class="form-group">
                    <label for="fecha">Fecha de registro</label>
                    <div class="input-group">
                      <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                      <input type="date" class="form-control" name="fecha" value="<?php echo date('Y-m-d'); ?>" readonly required>
                    </div>
                    <div class="box-header with-border">
                      <h3 class="box-title" style="display: inline-block;">Detalle del cierre de Caja Chica</h3>
                    </div>
                    <div class="box-body">
                      <div id="tbody" class="table table-responsive">
                      </div>
                    </div>
                  </div>
                </div>
                <!-- /.box-body -->
                <div class="box-footer">
                  <button type="submit" class="btn btn-success">
                    <i class="fa fa-shopping-bag"></i>
                    Cerrar caja</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </section>
      <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->
  <?php
  } else {
  ?>
    <div class="content-wrapper">
      <?php include_once './403.php'; ?>
    </div>
  <?php
  }
  ?>
  <?php require_once '../templates/footer.php'; ?>
  <?php
  if ($rolDescripcion == 'ADMINISTRADOR') {
  ?>
    <script src="../code/cierre_cajachica.js"></script>
  <?php
  }
  ?>