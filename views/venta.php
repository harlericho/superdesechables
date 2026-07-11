<?php require_once '../templates/header.php'; ?>
<div class="wrapper">
  <?php require_once '../templates/sidebar.php'; ?>

  <?php
  include_once '../models/loginModel.php';
  foreach (LoginModel::existeUsuarioEmailLogin($_SESSION['email']) as $key => $value) {
    $id = $value['usuario_id'];
  }
  ?>
  <style>
    .custom-input {
      color: blue;
      font-size: 20px;
      font-weight: bold;
      padding: 10px;
      border-radius: 5px;
    }
  </style>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Ventas
        <small>Panel de control</small>
      </h1>
      <?php require_once '../templates/panel.php'; ?>
    </section>

    <!-- Main content -->
    <section class="content">
      <!-- Main row -->
      <div class="row">
        <!-- left column -->
        <div class="col-md-6">
          <!-- general form elements -->
          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title">Producto</h3>
            </div>
            <!-- form start -->
            <form role="form" id="formProductoDetalle" action="javascript:void(0);" method="POST" onsubmit="app.guardar()">
              <div class="box-body">
                <input type="hidden" name="id">
                <div class="form-group col-md-6">
                  <label for="codigo">Código</label>
                  <!-- <div id="codigo"></div> -->
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-barcode"></i></span>
                    <input type="text" class="form-control" maxlength="50" name="codigo" id="inputCodigo" placeholder="Código producto" autofocus>
                  </div>
                  <div id="codigoMensaje"></div>
                </div>
                <div class="form-group col-md-6">
                  <label for="nombre">Nombre</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-user"></i></span>
                    <input type="text" class="form-control" name="nombre" placeholder="Nombre producto" disabled>
                  </div>
                </div>
                <div class="form-group col-md-12">
                  <label for="nombre">Descripción</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
                    <input type="text" class="form-control" name="descripcion" placeholder="Descripción producto" disabled>
                  </div>
                </div>
                <div class="form-group col-md-6">
                  <label for="precioventa">Precio venta</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-dollar"></i></span>
                    <input type="text" class="form-control" name="precio_v" placeholder="0.00" disabled>
                  </div>
                </div>
                <div class="form-group col-md-6">
                  <label for="stock">Stock producto</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-sort-numeric-desc"></i></span>
                    <input type="number" min="1" max="999" class="form-control" name="stock" placeholder="1" disabled>
                  </div>
                </div>
                <div class="form-group col-md-6">
                  <label for="stock">Cantidad a vender</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-sort-numeric-desc"></i></span>
                    <input type="number" min="1" max="999" class="form-control" name="cantidad" placeholder="1" required>
                  </div>
                </div>
                <div class="form-group col-md-6">
                  <label for="stock">Descuento %</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-plus"></i></span>
                    <input type="number" min="0" max="100" class="form-control" name="descuento" placeholder="0" value="0" required>
                  </div>
                </div>
              </div>
              <!-- /.box-body -->

              <div class="box-footer">
                <button type="submit" class="btn btn-primary pull-right">
                  <i class="fa fa-plus"></i>
                  Agregar</button>
                <button type="button" class="btn btn-info" onclick="app.limpiar()">
                  <i class="fa fa-refresh"></i>
                  Nuevo</button>
              </div>
            </form>
          </div>
        </div>

        <div class="col-md-6">
          <!-- general form elements -->
          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title">Detalle factura</h3>
            </div>
            <!-- form start -->
            <form role="form" id="formFactura" action="javascript:void(0);" method="POST" onsubmit="app.guardarFactura()">
              <div class="box-body">
                <input type="hidden" name="id">
                <input type="hidden" name="idUsuario" value="<?php echo $id; ?>">
                <div class="form-group col-md-6">
                  <label for="codigo">Cliente</label>
                  <div id="selectorCliente"></div>
                  <div style="margin-top: 5px;">
                    <button type="button" class="btn btn-primary btn-sm btn-block" data-toggle="modal" data-target="#modalNuevoCliente" title="Agregar nuevo cliente">
                      <i class="fa fa-user-plus"></i> Agregar Cliente
                    </button>
                  </div>
                </div>
                <div class="form-group col-md-6">
                  <label for="codigo">Forma de pago</label>
                  <div id="selectorFormapago"></div>
                </div>
                <div class="form-group col-md-12">
                  <label for="nombre">Número factura</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-area-chart"></i></span>
                    <!-- <div id="numero_factura"></div> -->
                    <input type="text" class="form-control custom-input" maxlength="20" id="numero_factura" name="numero_factura" placeholder="Numero factura" readonly>
                  </div>
                </div>
                <div class="form-group col-md-6">
                  <label for="nombre">Fecha factura</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                    <input type="date" class="form-control" name="fecha_factura" placeholder="Fecha" value="<?php echo date('Y-m-d'); ?>" readonly>
                  </div>
                </div>
                <div class="form-group col-md-6">
                  <label for="stock">Impuesto (IVA) %</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-percent"></i></span>
                    <input type="number" class="form-control" name="impuesto_factura" id="impuesto_factura" placeholder="Cargando..." readonly>
                    <span class="input-group-btn">
                      <button type="button" class="btn btn-default btn-sm" onclick="app.cargarImpuestoActivo()" title="Refrescar impuesto">
                        <i class="fa fa-refresh"></i>
                      </button>
                    </span>
                    <input type="hidden" name="impuesto_id" id="impuesto_id">
                  </div>
                  <small class="text-muted">El impuesto se carga automáticamente desde configuración</small>
                </div>
                <div class="form-group col-md-6">
                  <label for="stock">Sub Total factura</label>
                  <div class="input-group has-warning">
                    <span class="input-group-addon"><i class="fa fa-dollar"></i></span>
                    <input type="text" class="form-control input-lg text-yellow" name="subtotal_factura" placeholder="0.00" readonly>
                  </div>
                </div>
                <div class="form-group col-md-6">
                  <label for="descuento_global">Descuento global</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-percent"></i></span>
                    <input type="number" min="0" max="100" step="0.01" class="form-control input-lg text-blue" name="descuento_global" id="descuento_global" placeholder="0" value="0">
                  </div>
                  <small class="text-muted">Porcentaje de descuento aplicado al subtotal</small>
                </div>
                <div class="form-group col-md-6">
                  <label for="stock">Total factura</label>
                  <div class="input-group has-success">
                    <span class="input-group-addon"><i class="fa fa-dollar"></i></span>
                    <input type="text" class="form-control input-lg text-green" name="total_factura" placeholder="0.00" readonly>
                  </div>
                </div>
                <div class="form-group col-md-6">
                  <label style="display:block;">&nbsp;</label>
                  <div class="checkbox" style="margin-top:6px; border:1px solid #d9a100; border-radius:4px; padding:8px 12px; background:#fffbe6;">
                    <label>
                      <input type="checkbox" id="toggleIncluyeIva" checked>
                      <strong>Total incluye IVA</strong>
                    </label>
                    <small class="text-muted" style="display:block; margin-top:3px;">
                      Al activar, los precios ya incluyen IVA y se calcula el subtotal neto automáticamente.
                    </small>
                  </div>
                </div>
                <div class="form-group col-md-6" id="containerToggleSri" style="display:none;">
                  <label style="display:block;">&nbsp;</label>
                  <div class="checkbox" style="margin-top:6px; border:1px solid #3c8dbc; border-radius:4px; padding:8px 12px; background:#e8f4f8;">
                    <label>
                      <input type="checkbox" id="toggleEnviarSri" checked>
                      <strong>Enviar Factura al SRI</strong>
                    </label>
                    <small class="text-muted" style="display:block; margin-top:3px;">
                      Si se apaga, generará una Venta Interna (Ticket) que NO irá al SRI.
                    </small>
                  </div>
                </div>
                <!-- agregar un checkbox que deiga imprimir ticket -->
                <div class="form-group col-md-12">
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" name="imprimir_ticket" id="imprimirTicketCheckbox" checked>
                      <strong>Imprimir ticket de venta</strong>
                    </label>
                  </div>
                </div>
                <!-- /.box-body -->

                <div class="box-footer">
                  <button type="submit" class="btn btn-primary pull-right">
                    <i class="fa fa-cart-plus"></i>
                    Generar</button>
                </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Main row -->
      <div class="row">
        <!-- left column -->
        <div class="col-md-12">
          <!-- general form elements -->
          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title">Detalle productos</h3>
            </div>
            <div class="box-body">
              <div class="table table-responsive">
                <table class="table table-bordered text-center">
                  <thead>
                    <tr>
                      <th>Código producto</th>
                      <th>Nombre producto</th>
                      <th>Cantidad a vender</th>
                      <th>Precio unitario</th>
                      <th>Descuento %</th>
                      <th>Total</th>
                      <th style="width: 40px">Accciones</th>
                    </tr>
                  </thead>
                  <tbody id="tbody">
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

  <!-- Modal Nuevo Cliente -->
  <div class="modal fade" id="modalNuevoCliente" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
          <h4 class="modal-title">Nuevo Cliente</h4>
        </div>
        <form id="formNuevoCliente" onsubmit="app.guardarNuevoCliente(event)">
          <div class="modal-body">
            <div class="form-group">
              <label for="modal_dni">DNI/RUC <span class="text-red">*</span></label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-id-card"></i></span>
                <input type="text" class="form-control" id="modal_dni" name="dni" placeholder="DNI o RUC" maxlength="20" required>
              </div>
            </div>
            <div class="form-group">
              <label for="modal_nombres">Nombres <span class="text-red">*</span></label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-user"></i></span>
                <input type="text" class="form-control" id="modal_nombres" name="nombres" placeholder="Nombres" maxlength="100" required>
              </div>
            </div>
            <div class="form-group">
              <label for="modal_apellidos">Apellidos <span class="text-red">*</span></label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-user"></i></span>
                <input type="text" class="form-control" id="modal_apellidos" name="apellidos" placeholder="Apellidos" maxlength="100" required>
              </div>
            </div>
            <div class="form-group">
              <label for="modal_direccion">Dirección</label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
                <input type="text" class="form-control" id="modal_direccion" name="direccion" placeholder="Dirección" maxlength="200">
              </div>
            </div>
            <div class="form-group">
              <label for="modal_telefono">Teléfono</label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-phone"></i></span>
                <input type="text" class="form-control" id="modal_telefono" name="telefono" placeholder="Teléfono" maxlength="20">
              </div>
            </div>
            <div class="form-group">
              <label for="modal_email">Email <span class="text-red">*</span></label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                <input type="email" class="form-control" id="modal_email" name="email" placeholder="correo@ejemplo.com" maxlength="100" required>
              </div>
            </div>
            <input type="hidden" name="rol" value="3">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">
              <i class="fa fa-times"></i> Cancelar
            </button>
            <button type="submit" class="btn btn-primary">
              <i class="fa fa-save"></i> Guardar Cliente
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal progreso SRI -->
  <div class="modal fade" id="modalSriProceso" tabindex="-1" role="dialog"
    data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-sm" role="document" style="margin-top:15%;">
      <div class="modal-content" style="border-radius:8px;border:none;box-shadow:0 6px 24px rgba(0,0,0,.25);">
        <div class="modal-body" style="padding:28px 24px 20px;text-align:center;">
          <i class="fa fa-cog fa-spin fa-3x" style="color:#3498db;margin-bottom:14px;"></i>
          <h4 style="margin:0 0 6px;font-size:15px;color:#2c3e50;" id="sriPasoTitulo">Registrando venta...</h4>
          <p style="font-size:12px;color:#888;margin-bottom:16px;" id="sriPasoDetalle">Por favor espere</p>
          <div class="progress" style="margin-bottom:6px;height:10px;border-radius:5px;">
            <div class="progress-bar progress-bar-striped active" id="sriProgressBar"
              role="progressbar" style="width:5%;border-radius:5px;transition:width .6s ease;">
            </div>
          </div>
          <small style="color:#aaa;" id="sriPasoNum">Paso 1 de 5</small>
        </div>
      </div>
    </div>
  </div>

  <?php require_once '../templates/footer.php'; ?>
  <script src="../code/qz-tray.js"></script>
  <script src="../code/venta.js"></script>