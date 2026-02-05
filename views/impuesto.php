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
          Impuestos / IVA
          <small>Configuración de impuestos</small>
        </h1>
        <?php require_once '../templates/panel.php'; ?>
      </section>

      <!-- Main content -->
      <section class="content">
        <!-- Main row -->
        <div class="row">
          <!-- left column -->
          <div class="col-md-4">
            <!-- general form elements -->
            <div class="box box-primary">
              <div class="box-header with-border">
                <h3 class="box-title">Formulario impuestos</h3>
              </div>
              <!-- form start -->
              <form role="form" id="formImpuesto" action="javascript:void(0);" method="POST" onsubmit="app.guardar()">
                <div class="box-body">
                  <input type="hidden" name="id">
                  <div class="form-group">
                    <label for="nombre">Nombre del impuesto <span class="text-red">*</span></label>
                    <div class="input-group">
                      <span class="input-group-addon"><i class="fa fa-info"></i></span>
                      <input type="text" class="form-control" name="nombre" placeholder="Ej: IVA General, Sin IVA, etc." autofocus required>
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="porcentaje">Porcentaje (%) <span class="text-red">*</span></label>
                    <div class="input-group">
                      <span class="input-group-addon"><i class="fa fa-percent"></i></span>
                      <input type="number" min="0" max="100" step="0.01" class="form-control" name="porcentaje" placeholder="0.00" required>
                    </div>
                    <small class="text-muted">Ingresa 0 para impuestos exentos, o el porcentaje correspondiente (ej: 12, 15, 18, etc.)</small>
                  </div>
                  <div class="form-group">
                    <div class="checkbox">
                      <label>
                        <input type="checkbox" name="activo" id="activoCheckbox">
                        <strong>Marcar como impuesto activo</strong>
                      </label>
                    </div>
                    <small class="text-info"><i class="fa fa-info-circle"></i> Solo un impuesto puede estar activo a la vez. Al activar este, se desactivarán los demás automáticamente.</small>
                  </div>
                </div>
                <!-- /.box-body -->

                <div class="box-footer">
                  <button type="submit" class="btn btn-primary" id="btnGuardar">
                    <i class="fa fa-save"></i>
                    <span id="btnTexto">Guardar</span>
                  </button>
                  <button type="button" class="btn btn-warning" onclick="app.limpiar()" id="btnNuevo">
                    <i class="fa fa-refresh"></i>
                    Nuevo
                  </button>
                </div>
              </form>
            </div>

            <!-- Info Box -->
            <div class="box box-info">
              <div class="box-header with-border">
                <h3 class="box-title">💡 Información importante</h3>
              </div>
              <div class="box-body">
                <ul class="list-unstyled">
                  <li><i class="fa fa-check text-green"></i> Solo un impuesto puede estar <strong>activo</strong> a la vez</li>
                  <li><i class="fa fa-shopping-cart text-blue"></i> El impuesto activo se usa automáticamente en ventas</li>
                  <li><i class="fa fa-edit text-orange"></i> Puedes editar impuestos haciendo clic en el botón de edición</li>
                  <li><i class="fa fa-toggle-on text-green"></i> Activa impuestos haciendo clic en el estado "Inactivo"</li>
                </ul>
              </div>
            </div>
          </div>

          <div class="col-md-8">
            <!-- general form elements -->
            <div class="box box-primary">
              <div class="box-header with-border">
                <h3 class="box-title">Listado de impuestos configurados</h3>
              </div>
              <div class="box-body">
                <div class="table table-responsive">
                  <table class="table table-bordered text-center">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Nombre Impuesto</th>
                        <th>Porcentaje</th>
                        <th>Estado</th>
                        <th>Activo</th>
                        <th>Fecha Creación</th>
                        <th style="width: 120px">Acciones</th>
                      </tr>
                    </thead>
                    <tbody id="tbody">
                      <tr>
                        <td colspan="7">
                          <i class="fa fa-spinner fa-spin"></i> Cargando impuestos...
                        </td>
                      </tr>
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
      <?php include_once './403.php'; ?>
    </div>
  <?php
  }
  ?>
  <?php require_once '../templates/footer.php'; ?>
  <script src="../code/impuesto.js"></script>
</div>