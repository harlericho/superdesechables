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
          <div class="modal-header" id="correoModalHeader">
            <h5 class="modal-title">Reenviar Factura</h5>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
          </div>

          <!-- Formulario normal -->
          <div class="modal-body" id="correoFormBody">
            <input type="hidden" id="facturaIdCorreo" name="factura_id">
            <div class="form-group">
              <label for="correoDestino">Correo destino</label>
              <input type="email" class="form-control" id="correoDestino" name="correo" required>
            </div>
          </div>
          <div class="modal-footer" id="correoFormFooter">
            <button type="submit" class="btn btn-primary"><i class="fa fa-send"></i> Enviar</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
          </div>

          <!-- Estado de progreso (oculto por defecto) -->
          <div class="modal-body" id="correoProgresoBody" style="display:none;text-align:center;padding:28px 24px 24px;">
            <i class="fa fa-envelope fa-2x fa-spin" style="color:#3498db;margin-bottom:12px;"></i>
            <h4 style="font-size:15px;color:#2c3e50;margin:0 0 5px;" id="correoPasoTitulo">Preparando envío...</h4>
            <p style="font-size:12px;color:#888;margin-bottom:14px;" id="correoPasoDetalle">Por favor espere</p>
            <div class="progress" style="height:10px;border-radius:5px;margin-bottom:6px;">
              <div class="progress-bar progress-bar-striped active" id="correoProgressBar"
                role="progressbar" style="width:5%;border-radius:5px;transition:width .6s ease;"></div>
            </div>
            <small style="color:#aaa;" id="correoPasoNum">Paso 1 de 4</small>
          </div>

        </div>
      </form>
    </div>
  </div>

  <script src="../code/facturaGA.js"></script>