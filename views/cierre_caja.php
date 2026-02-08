<?php require_once '../templates/header.php';
date_default_timezone_set('America/Guayaquil'); ?>
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
          Cierre de Caja
          <small>Sistema de control de caja diaria</small>
        </h1>
        <?php require_once '../templates/panel.php'; ?>
      </section>

      <!-- Main content -->
      <section class="content">
        <!-- Main row -->
        <div class="row">
          <!-- Formulario de cierre -->
          <div class="col-md-6">
            <div class="box box-success">
              <div class="box-header with-border">
                <h3 class="box-title">
                  <i class="fa fa-calculator"></i>
                  Realizar Cierre de Caja
                </h3>
              </div>
              <form role="form" id="formCierreCaja" action="javascript:void(0);" method="POST">
                <div class="box-body">
                  <div class="form-group">
                    <label for="fecha">Fecha del cierre</label>
                    <div class="input-group">
                      <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                      <input type="date" class="form-control" name="fecha" id="fecha" value="<?php echo date('Y-m-d'); ?>" readonly required>
                    </div>
                  </div>

                  <div class="form-group">
                    <label for="observaciones">Observaciones <span class="text-red">*</span></label>
                    <textarea class="form-control" name="observaciones" id="observaciones" rows="3" placeholder="Ingrese observaciones del cierre (requerido). Ej: Cierre normal del día, sin novedades..." required minlength="5" maxlength="255"></textarea>
                    <small class="text-muted">Mínimo 5 caracteres requeridos</small>
                  </div>

                  <div class="row">
                    <div class="col-md-6">
                      <button type="button" class="btn btn-info btn-block" onclick="cierreCaja.obtenerDatos()">
                        <i class="fa fa-search"></i> Obtener Datos del Día
                      </button>
                    </div>
                    <div class="col-md-6">
                      <button type="button" class="btn btn-success btn-block" onclick="cierreCaja.realizarCierre()" disabled id="btnRealizarCierre">
                        <i class="fa fa-save"></i> Realizar Cierre
                      </button>
                    </div>
                  </div>
                </div>
              </form>
            </div>
          </div>

          <!-- Resumen del día -->
          <div class="col-md-6">
            <div class="box box-info">
              <div class="box-header with-border">
                <h3 class="box-title">
                  <i class="fa fa-chart-line"></i>
                  Resumen del Día
                </h3>
              </div>
              <div class="box-body">
                <div id="resumenDia" style="display: none;">
                  <div class="row">
                    <div class="col-md-6">
                      <div class="info-box bg-green">
                        <span class="info-box-icon"><i class="fa fa-money"></i></span>
                        <div class="info-box-content">
                          <span class="info-box-text">Saldo Inicial</span>
                          <span class="info-box-number" id="saldoInicial">$0.00</span>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="info-box bg-blue">
                        <span class="info-box-icon"><i class="fa fa-calculator"></i></span>
                        <div class="info-box-content">
                          <span class="info-box-text">Saldo Final</span>
                          <span class="info-box-number" id="saldoFinal">$0.00</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div id="mensajeSinDatos" class="text-center text-muted">
                  <i class="fa fa-info-circle fa-3x"></i>
                  <p>Seleccione una fecha y obtenga los datos del día</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Detalles del cierre -->
        <div class="row" id="seccionDetalles" style="display: none;">
          <div class="col-md-6">
            <!-- Ventas por tipo de pago -->
            <div class="box box-warning">
              <div class="box-header with-border">
                <h3 class="box-title">
                  <i class="fa fa-credit-card"></i>
                  Ventas por Tipo de Pago
                </h3>
              </div>
              <div class="box-body">
                <table class="table table-striped" id="tablaVentasTipo">
                  <thead>
                    <tr>
                      <th>Tipo de Pago</th>
                      <th>Cantidad</th>
                      <th>Total</th>
                    </tr>
                  </thead>
                  <tbody id="tbodyVentasTipo">
                    <!-- Datos dinámicos -->
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Balance con IVA -->
            <div class="box box-primary">
              <div class="box-header with-border">
                <h3 class="box-title">
                  <i class="fa fa-percent"></i>
                  Balance con IVA
                </h3>
              </div>
              <div class="box-body">
                <div class="row">
                  <div class="col-md-6">
                    <div class="small-box bg-yellow">
                      <div class="inner">
                        <h3 id="ventasSinIva">$0.00</h3>
                        <p>Ventas Sin IVA (0%)</p>
                      </div>
                      <div class="icon">
                        <i class="fa fa-circle-o"></i>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="small-box bg-green">
                      <div class="inner">
                        <h3 id="ventasConIva">$0.00</h3>
                        <p>Ventas Con IVA (15%)</p>
                      </div>
                      <div class="icon">
                        <i class="fa fa-percent"></i>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-12">
                    <div class="info-box bg-red">
                      <span class="info-box-icon"><i class="fa fa-dollar"></i></span>
                      <div class="info-box-content">
                        <span class="info-box-text">Total IVA Cobrado</span>
                        <span class="info-box-number" id="ivaCobrado">$0.00</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <!-- Movimientos de caja -->
            <div class="box box-danger">
              <div class="box-header with-border">
                <h3 class="box-title">
                  <i class="fa fa-exchange"></i>
                  Movimientos de Caja
                </h3>
              </div>
              <div class="box-body">
                <table class="table table-striped" id="tablaMovimientos">
                  <thead>
                    <tr>
                      <th>Tipo</th>
                      <th>Cantidad</th>
                      <th>Monto</th>
                    </tr>
                  </thead>
                  <tbody id="tbodyMovimientos">
                    <!-- Datos dinámicos -->
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Resumen final -->
            <div class="box box-success">
              <div class="box-header with-border">
                <h3 class="box-title">
                  <i class="fa fa-summary"></i>
                  Resumen Final
                </h3>
              </div>
              <div class="box-body">
                <table class="table">
                  <tr>
                    <td><strong>Ventas en Efectivo:</strong></td>
                    <td class="text-right" id="totalIngresosEfectivo">$0.00</td>
                  </tr>
                  <tr>
                    <td><strong>Ventas por Transferencia:</strong></td>
                    <td class="text-right" id="totalTransferencias">$0.00</td>
                  </tr>
                  <tr>
                    <td><strong>Ventas por Cheque:</strong></td>
                    <td class="text-right" id="totalCheques">$0.00</td>
                  </tr>
                  <tr>
                    <td><strong>Otras Ventas:</strong></td>
                    <td class="text-right" id="otrosIngresos">$0.00</td>
                  </tr>
                  <tr class="bg-green">
                    <td><strong>Ingresos de Caja (Mov.):</strong></td>
                    <td class="text-right" id="totalIngresosMovimientos">$0.00</td>
                  </tr>
                  <tr class="bg-red">
                    <td><strong>Egresos de Caja (Mov.):</strong></td>
                    <td class="text-right" id="totalEgresos">$0.00</td>
                  </tr>
                  <tr class="bg-green">
                    <td><strong>Saldo Final del Día:</strong></td>
                    <td class="text-right" id="saldoFinalDetalle">$0.00</td>
                  </tr>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- Historial de cierres -->
        <div class="row">
          <div class="col-md-12">
            <div class="box box-default">
              <div class="box-header with-border">
                <h3 class="box-title">
                  <i class="fa fa-history"></i>
                  Historial de Cierres de Caja
                </h3>
                <div class="box-tools">
                  <button type="button" class="btn btn-sm btn-default" onclick="cierreCaja.obtenerHistorial()">
                    <i class="fa fa-refresh"></i> Actualizar
                  </button>
                </div>
              </div>
              <div class="box-body">
                <div class="table-responsive">
                  <table class="table table-striped" id="tablaHistorial">
                    <thead>
                      <tr>
                        <th class="text-center">Fecha</th>
                        <th class="text-center">Hora</th>
                        <th>Usuario</th>
                        <th class="text-right">Saldo Inicial</th>
                        <th class="text-right">Total Ventas</th>
                        <th class="text-right">Saldo Final</th>
                        <th class="text-center">Estado</th>
                      </tr>
                    </thead>
                    <tbody id="tbodyHistorial">
                      <!-- Datos dinámicos -->
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
  <?php
  if ($rolDescripcion == 'ADMINISTRADOR') {
  ?>
    <style>
      #tablaHistorial th {
        background-color: #f4f4f4;
        font-weight: bold;
      }

      #tablaHistorial tbody tr:hover {
        background-color: #f9f9f9;
      }

      #tablaHistorial .text-right {
        font-family: monospace;
        font-weight: bold;
      }

      .label-success {
        padding: 4px 8px;
      }
    </style>
    <script src="../code/cierre_caja.js"></script>
  <?php
  }
  ?>