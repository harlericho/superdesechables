<?php require_once '../templates/header.php'; ?>
<div class="wrapper">
  <?php require_once '../templates/sidebar.php'; ?>

  <?php

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
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
      <!-- Content Header (Page header) -->
      <section class="content-header">
        <h1>
          Tipo de comprobantes
          <small>Panel de control</small>
        </h1>
        <?php require_once '../templates/panel.php'; ?>
      </section>

      <!-- Main content -->
      <section class="content">
        <!-- Main row -->
        <div class="row">
          <!-- left column -->
          <div class="col-md-5">
            <!-- general form elements -->
            <div class="box box-info">
              <div class="box-header with-border">
                <h3 class="box-title">Formulario comprobantes</h3>
              </div>
              <!-- form start -->
              <form role="form" action="javascript:void(0);" method="POST" onsubmit="app.guardar()">
                <div class="box-body">
                  <div class="form-group">
                    <label for="descripcion">Descripción</label>
                    <div class="input-group">
                      <span class="input-group-addon"><i class="fa fa-info"></i></span>
                      <input type="text" class="form-control" name="descripcion" placeholder="Descripción comprobante" autofocus required>
                    </div>
                  </div>
                </div>
                <!-- /.box-body -->

                <div class="box-footer">
                  <button type="submit" class="btn btn-success">
                    <i class="fa fa-plus"></i>
                    Agregar</button>
                  <button type="reset" class="btn btn-info">
                    <i class="fa fa-refresh"></i>
                    Nuevo</button>
                </div>
              </form>
            </div>
          </div>
          <div class="col-md-7">
            <!-- general form elements -->
            <div class="box box-info">
              <div class="box-header with-border">
                <h3 class="box-title">Listado comprobantes</h3>
              </div>
              <div class="box-body">
                <div class="table table-responsive">
                  <table class="table table-bordered text-center">
                    <thead>
                      <tr>
                        <th>Comprobantes</th>
                        <th>Estado</th>
                      </tr>
                    </thead>
                    <tbody id="tbody">
                    </tbody>
                  </table>
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
      <?php include_once './404.php'; ?>
    </div>
  <?php
  }
  ?>
  <?php require_once '../templates/footer.php'; ?>
  <script src="../code/comprobante.js"></script>