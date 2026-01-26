<?php require_once '../templates/header.php'; ?>
<div class="wrapper">
  <?php require_once '../templates/sidebar.php'; ?>


  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Clientes
        <small>Panel de control</small>
      </h1>
      <?php require_once '../templates/panel.php'; ?>
    </section>

    <!-- Main content -->
    <section class="content">
      <!-- Main row -->
      <div class="row">
        <!-- left column -->
        <div class="col-md-12">
          <!-- general form elements -->
          <div class="box box-danger">
            <div class="box-header with-border">
              <h3 class="box-title">Formulario Cliente</h3>
            </div>
            <!-- form start -->
            <form role="form" id="formCliente" action="javascript:void(0);" method="POST" onsubmit="app.guardar()">
              <div class="box-body">
                <input type="hidden" name="id">
                <div class="form-group col-md-6">
                  <label>Roles</label>
                  <div id="selectorRol"></div>
                </div>
                <div class="form-group col-md-6">
                  <label for="dni">Dni</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
                    <input type="text" minlength="10" maxlength="10" class="form-control" name="dni" placeholder="Dni cliente" autofocus required>
                  </div>
                </div>
                <div class="form-group col-md-6">
                  <label for="nombres">Nombres</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-user"></i></span>
                    <input type="text" class="form-control" name="nombres" placeholder="Nombre cliente" required>
                  </div>
                </div>
                <div class="form-group col-md-6">
                  <label for="apellidos">Apellidos</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-user"></i></span>
                    <input type="text" class="form-control" name="apellidos" placeholder="Apellidos cliente" required>
                  </div>
                </div>
                <div class="form-group col-md-12">
                  <label for="direccion">Direccion</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-info"></i></span>
                    <input type="text" class="form-control" name="direccion" placeholder="Direccion cliente" required>
                  </div>
                </div>
                <div class="form-group col-md-6">
                  <label for="email">Email</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                    <input type="email" class="form-control" name="email" placeholder="Email cliente" required>
                  </div>
                </div>
                <div class="form-group col-md-6">
                  <label for="telefono">Telefono</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-phone-square"></i></span>
                    <input type="text" minlength="10" maxlength="10" class="form-control" name="telefono" placeholder="Telefono cliente" required>
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
        <div class="col-md-12">
          <!-- general form elements -->
          <div class="box box-danger">
            <div class="box-header with-border">
              <h3 class="box-title">Listado Clientes</h3>
              <button type="button" class="btn btn-warning btn-sm pull-right" onclick="app.exportarCSV()">
                <i class="fa fa-file-excel-o"></i> Exportar CSV
              </button>
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

  <?php require_once '../templates/footer.php'; ?>
  <script src="../code/cliente.js"></script>