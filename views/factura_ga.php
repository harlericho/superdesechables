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
          Facturas Activas
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
            <div class="box box-success">
              <div class="box-header with-border">
                <h3 class="box-title">Listado facturas activadas</h3>
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
      <?php include_once './404.php'; ?>
    </div>
  <?php
  }
  ?>
  <?php require_once '../templates/footer.php'; ?>

  <!-- Modal para reenviar factura por correo -->
  <div class="modal fade" id="modalCorreo" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <form id="formCorreoFactura">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Reenviar Factura</h5>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
          </div>
          <div class="modal-body">
            <input type="hidden" id="facturaIdCorreo" name="factura_id">
            <div class="form-group">
              <label for="correoDestino">Correo destino</label>
              <input type="email" class="form-control" id="correoDestino" name="correo" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Enviar</button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          </div>
        </div>
      </form>
    </div>
  </div>
  <script src="../code/facturaGA.js"></script>