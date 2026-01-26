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
          Usuarios
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
                <h3 class="box-title">Formulario usuarios</h3>
              </div>
              <!-- form start -->
              <form role="form" id="formUsuario" action="javascript:void(0);" method="POST" onsubmit="app.guardar()">
                <div class="box-body">
                  <input type="hidden" name="id">
                  <div class="form-group">
                    <label>Roles</label>
                    <div id="selectorRol"></div>
                  </div>
                  <!-- /.form-group -->
                  <div class="form-group">
                    <label for="nombres">Nombres</label>
                    <div class="input-group">
                      <span class="input-group-addon"><i class="fa fa-user"></i></span>
                      <input type="text" class="form-control" name="nombres" placeholder="Nombres usuario" required>
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="email">Email</label>
                    <div class="input-group">
                      <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                      <input type="email" class="form-control" name="email" placeholder="Email usuario" required>
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="telefono">Password</label>
                    <div class="input-group">
                      <span class="input-group-addon"><i class="fa fa-cc"></i></span>
                      <input type="text" class="form-control" name="password" placeholder="************" required>
                    </div>
                  </div>
                </div>
                <!-- /.box-body -->

                <div class="box-footer">
                  <button type="submit" class="btn btn-success">
                    <i class="fa fa-plus"></i>
                    Agregar</button>
                  <button type="button" class="btn btn-info" onclick="app.limpiar()">
                    <i class="fa fa-refresh"></i>
                    Nuevo</button>
                </div>
              </form>
            </div>
          </div>
          <div class="col-md-9">
            <!-- general form elements -->
            <div class="box box-success">
              <div class="box-header with-border">
                <h3 class="box-title">Listado usuarios</h3>
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
    <script src="../code/usuario.js"></script>
  <?php
  }
  ?>