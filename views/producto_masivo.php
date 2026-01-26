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
          Producto masivo
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
            <div class="box box-primary">
              <div class="box-header with-border">
                <h3 class="box-title">Formulario producto masivo</h3>
              </div>
              <!-- form start -->
              <form role="form" action="javascript:void(0);" method="POST" onsubmit="app.guardar()">
                <div class="box-body">
                  <div class="form-group col-md-12">
                    <div class="alert alert-info">
                      <h4><i class="icon fa fa-info"></i> Formato del archivo CSV</h4>
                      <p>El archivo debe contener las siguientes columnas en este orden:</p>
                      <ul>
                        <li><strong>codigo:</strong> Código único del producto</li>
                        <li><strong>nombre:</strong> Nombre del producto</li>
                        <li><strong>descripcion:</strong> Descripción del producto</li>
                        <li><strong>precio_compra:</strong> Precio de compra</li>
                        <li><strong>precio_venta:</strong> Precio de venta</li>
                        <li><strong>imagen:</strong> Nombre del archivo de imagen (sin-imagen.png si se omite)</li>
                        <li><strong>stock:</strong> Cantidad en stock</li>
                        <li><strong>categoria:</strong> ID de la categoría (número)</li>
                        <li><strong>proveedor:</strong> ID del proveedor (número)</li>
                      </ul>
                      <p>
                        <a href="../archives/modelo_productos.xlsx" class="btn btn-info btn-sm" download>
                          <i class="fa fa-download"></i> Descargar plantilla de ejemplo
                        </a>
                      </p>
                    </div>
                  </div>
                  <div class="form-group col-md-6">
                    <div id="archivoCargada"></div>
                    <label for="file">Archivo CSV</label>
                    <input type="file" name="file" id="file" accept=".csv">
                    <p class="help-block">Seleccione un archivo CSV con los productos.</p>
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
  <script src="../code/producto_masivo.js"></script>