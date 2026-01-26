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
          Productos
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
                <h3 class="box-title">Formulario registro</h3>
              </div>
              <!-- form start -->
              <form role="form" id="formProducto" action="javascript:void(0);" method="POST" onsubmit="app.guardar()">
                <div class="box-body">
                  <input type="hidden" name="id">
                  <div class="form-group col-md-4">
                    <label>Proveedor</label>
                    <div id="selectorProveedor"></div>
                  </div>
                  <div class="form-group col-md-4">
                    <label>Categoria</label>
                    <div id="selectorCategoria"></div>
                  </div>
                  <div class="form-group col-md-4">
                    <label for="codigo">Código</label>
                    <div class="input-group has-error">
                      <span class="input-group-addon"><i class="fa fa-barcode"></i></span>
                      <input type="text" class="form-control" maxlength="50" name="codigo" placeholder="Código producto" required>
                    </div>
                  </div>
                  <div class="form-group col-md-4">
                    <label for="nombre">Nombre</label>
                    <div class="input-group">
                      <span class="input-group-addon"><i class="fa fa-user"></i></span>
                      <input type="text" class="form-control" name="nombre" placeholder="Nombre producto" required>
                    </div>
                  </div>
                  <div class="form-group col-md-4">
                    <label for="nombre">Descripción</label>
                    <div class="input-group">
                      <span class="input-group-addon"><i class="fa fa-info"></i></span>
                      <input type="text" class="form-control" name="descripcion" placeholder="Descripción producto">
                    </div>
                  </div>
                  <div class="form-group col-md-4">
                    <label for="stock">Stock</label>
                    <div class="input-group">
                      <span class="input-group-addon"><i class="fa fa-sort-numeric-desc"></i></span>
                      <input type="number" min="1" max="999" class="form-control" name="stock" placeholder="1" required>
                    </div>
                  </div>
                  <div class="form-group col-md-6">
                    <label for="precioventa">Precio compra</label>
                    <div class="input-group has-warning">
                      <span class="input-group-addon"><i class="fa fa-dollar"></i></span>
                      <input type="text" class="form-control" name="precio_c" placeholder="0.00" required>
                    </div>
                  </div>
                  <div class="form-group col-md-6">
                    <label for="precioventa">Precio venta</label>
                    <div class="input-group has-success">
                      <span class="input-group-addon"><i class="fa fa-dollar"></i></span>
                      <input type="text" class="form-control" name="precio_v" placeholder="0.00" required>
                    </div>
                  </div>
                  <div class="form-group col-md-6">
                    <div id="imagenCargada"></div>
                    <label for="imagen">Imagen producto</label>
                    <input type="file" name="file">
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
            <div class="box box-success">
              <div class="box-header with-border">
                <h3 class="box-title">Listado productos</h3>
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
    <script src="../code/producto.js"></script>
  <?php
  }
  ?>