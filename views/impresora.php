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
          Impresora de Tickets POS
          <small>Panel de control</small>
        </h1>
        <?php require_once '../templates/panel.php'; ?>
      </section>

      <!-- Main content -->
      <section class="content">
        <!-- Main row -->
        <div class="row">
          <!-- Formulario -->
          <div class="col-md-5">
            <div class="box box-warning">
              <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-print"></i> Configurar impresora de tickets</h3>
              </div>
              <!-- form start -->
              <form role="form" id="formImpresora" action="javascript:void(0);" method="POST" onsubmit="app.guardar()">
                <div class="box-body">
                  <div class="form-group">
                    <label for="impresora_ticket">Nombre de la impresora</label>
                    <div class="input-group">
                      <span class="input-group-addon"><i class="fa fa-print"></i></span>
                      <input type="text" class="form-control" id="impresora_ticket" name="impresora_ticket"
                        placeholder="Ej: XP-80C" required autofocus>
                    </div>
                    <span class="help-block">
                      Ingrese el nombre exacto de la impresora instalada en Windows.<br>
                      <small class="text-muted">Ver: <strong>Configuración &gt; Bluetooth y dispositivos &gt; Impresoras y escáneres</strong></small>
                    </span>
                  </div>
                </div>
                <!-- /.box-body -->
                <div class="box-footer">
                  <button type="submit" class="btn btn-warning">
                    <i class="fa fa-save"></i> Guardar
                  </button>
                  <span id="impresoraMsg" style="margin-left:10px;"></span>
                </div>
              </form>
            </div>
          </div>

          <!-- Info impresora actual -->
          <div class="col-md-7">
            <div class="box box-info">
              <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-info-circle"></i> Impresora actualmente configurada</h3>
              </div>
              <div class="box-body">
                <p>
                  La impresora configurada para tickets POS es:
                  <strong id="impresoraActual"><i class="fa fa-spinner fa-spin"></i> Cargando...</strong>
                </p>
                <hr>
                <p class="text-muted">
                  <i class="fa fa-lightbulb-o"></i>
                  Esta impresora se utiliza al marcar <strong>"Imprimir ticket"</strong> en el módulo de ventas.
                  Asegúrate de que el nombre coincida exactamente con el nombre de la impresora en Windows.
                </p>
              </div>
            </div>

            <div class="box box-default">
              <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-list"></i> Impresoras disponibles en este equipo</h3>
              </div>
              <div class="box-body" id="listaImpresoras">
                <p class="text-muted"><i class="fa fa-spinner fa-spin"></i> Cargando lista vía QZ Tray...</p>
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
    <script src="../code/qz-tray.js"></script>
    <script src="../code/impresora.js"></script>
  <?php
  }
  ?>
</div>