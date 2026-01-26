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
          Cliente masivo
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
                <h3 class="box-title">Formulario cliente masivo</h3>
              </div>
              <!-- form start -->
              <form role="form" action="javascript:void(0);" method="POST" onsubmit="app.guardar()">
                <div class="box-body">
                  <div class="form-group col-md-12">
                    <div class="alert alert-danger">
                      <h4><i class="icon fa fa-info"></i> Formato del archivo CSV</h4>
                      <p>El archivo debe contener las siguientes columnas en este orden:</p>
                      <ul>
                        <li><strong>dni:</strong> DNI o documento de identidad del cliente</li>
                        <li><strong>nombres:</strong> Nombres del cliente</li>
                        <li><strong>apellidos:</strong> Apellidos del cliente</li>
                        <li><strong>direccion:</strong> Dirección del cliente</li>
                        <li><strong>telefono:</strong> Número de teléfono</li>
                        <li><strong>email:</strong> Correo electrónico</li>
                        <li><strong>rol:</strong> ID del rol del cliente (número)</li>
                      </ul>
                      <p>
                        <a href="../archives/modelo_clientes.xlsx" class="btn btn-danger btn-sm" download>
                          <i class="fa fa-download"></i> Descargar plantilla de ejemplo
                        </a>
                      </p>
                    </div>
                  </div>
                  <div class="form-group col-md-6">
                    <div id="archivoCargada"></div>
                    <label for="imagen">Archivo CSV</label>
                    <input type="file" name="file" id="file" accept=".csv">
                    <p class="help-block">Seleccione un archivo CSV con los clientes.</p>
                  </div>
                </div>
                <!-- /.box-body -->

                <div class="box-footer">
                  <button type="submit" class="btn btn-success">
                    <i class="fa fa-upload"></i>
                    Subir</button>
                  <button type="reset" class="btn btn-info">
                    <i class="fa fa-refresh"></i>
                    Nuevo</button>
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
      <?php include_once './404.php'; ?>
    </div>
  <?php
  }
  ?>
  <?php require_once '../templates/footer.php'; ?>
  <script src="../code/cliente_masivo.js"></script>