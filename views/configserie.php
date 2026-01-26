<?php require_once '../templates/header.php'; ?>
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
          Configuraciones
          <small>Panel de control</small>
        </h1>
        <?php require_once '../templates/panel.php'; ?>
      </section>

      <!-- Main content -->
      <section class="content">
        <!-- Main row -->
        <div class="row">
          <!-- left column -->
          <div class="col-md-3">
            <!-- general form elements -->
            <div class="box box-success">
              <div class="box-header with-border">
                <h3 class="box-title">Formulario configuraciones</h3>
              </div>
              <!-- form start -->
              <form role="form" id="formConfigSerie" action="javascript:void(0);" method="POST" onsubmit="app.guardar()">
                <div class="box-body">
                  <input type="hidden" name="id">
                  <!-- /.form-group -->
                  <div class="form-group">
                    <label for="nombres">Primera Serie</label>
                    <div class="input-group">
                      <span class="input-group-addon"><i class="fa fa-area-chart"></i></span>
                      <input type="text" class="form-control" name="primera_serie" placeholder="001" required autofocus>
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="nombres">Segunda Serie</label>
                    <div class="input-group">
                      <span class="input-group-addon"><i class="fa fa-area-chart"></i></span>
                      <input type="text" class="form-control" name="segunda_serie" placeholder="001" required>
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="nombres">Secuencial</label>
                    <div class="input-group">
                      <span class="input-group-addon"><i class="fa fa-sort-numeric-asc"></i></span>
                      <input type="text" class="form-control" name="secuencial" placeholder="1" required>
                    </div>
                  </div>
                </div>
                <!-- /.box-body -->

                <div class="box-footer">
                  <button type="submit" class="btn btn-success">
                    <i class="fa fa-plus"></i>
                    Agregar</button>
                </div>
              </form>
            </div>
          </div>
          <div class="col-md-9">
            <!-- general form elements -->
            <div class="box box-success">
              <div class="box-header with-border">
                <h3 class="box-title">Listado de serie factura</h3>
              </div>
              <div class="box-body">
                <div id="tbody" class="table table-responsive">
                </div>
              </div>
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
    <script src="../code/configserie.js"></script>
  <?php
  }
  ?>