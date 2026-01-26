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
    <div class="content-wrapper">
      <section class="content-header">
        <h1>
          Códigos de Barras
          <small>Generar códigos de barras de productos</small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="./"><i class="fa fa-dashboard"></i> Inicio</a></li>
          <li class="active">Códigos de Barras</li>
        </ol>
      </section>

      <section class="content">
        <div class="row">
          <div class="col-md-12">
            <div class="box box-primary">
              <div class="box-header with-border">
                <h3 class="box-title">Seleccionar Productos</h3>
              </div>
              <div class="box-body">
                <div class="form-group">
                  <label>Buscar Producto:</label>
                  <select class="form-control select2" id="productoSelect" style="width: 100%;">
                    <option value="">Seleccione un producto</option>
                  </select>
                </div>

                <div class="table-responsive" style="margin-top: 20px;">
                  <table class="table table-bordered" id="tablaProductosSeleccionados">
                    <thead>
                      <tr>
                        <th>Código</th>
                        <th>Producto</th>
                        <th>Precio</th>
                        <th>Acciones</th>
                      </tr>
                    </thead>
                    <tbody id="productosSeleccionados">
                      <!-- Los productos se agregarán aquí dinámicamente -->
                    </tbody>
                  </table>
                </div>

                <div class="form-group" style="margin-top: 20px;">
                  <button type="button" class="btn btn-success btn-lg" id="btnGenerarPDF">
                    <i class="fa fa-barcode"></i> Generar PDF con Códigos de Barras
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
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
    <script src="../code/producto_codigo_barra.js?v=<?php echo time(); ?>"></script>
  <?php
  }
  ?>