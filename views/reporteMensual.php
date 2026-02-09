<?php require_once '../templates/header.php'; ?>
<?php require_once '../config/empresa.php'; ?>
<div class="wrapper">
  <?php require_once '../templates/sidebar.php'; ?>
  <div class="content-wrapper">
    <section class="content-header">
      <h1>Reporte Mensual <small>Dinero por mes</small></h1>
      <div class="row">
        <div class="col-md-12 text-right">
          <a id="btnDescargarPDF" href="../controllers/ganancias/reportePDFMensualController.php" class="btn btn-danger" style="margin-top:10px;margin-right:0;" target="_blank"><i class="fa fa-file-pdf-o"></i> Descargar PDF</a>
        </div>
      </div>
    </section>
    <section class="content">
      <div class="row">
        <div class="col-md-12">
          <!-- GRÁFICO PRINCIPAL DE BARRAS -->
          <div class="box box-success">
            <div class="box-header with-border">
              <h3 class="box-title">Dinero Mensual</h3>
              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
              </div>
            </div>
            <div class="box-body">
              <div class="chart">
                <canvas id="dineroMensualChart" style="height:230px"></canvas>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- Filtros por año o rango de fechas -->
      <div class="row">
        <div class="col-md-4">
          <div class="form-group">
            <label for="anio">Año:</label>
            <select id="anio" name="anio" class="form-control"></select>
          </div>
        </div>
      </div>
    </section>
    <!-- Tabla de detalle mensual -->
    <div class="row">
      <div class="col-md-12">
        <div class="box box-info" style="margin-left:16px;max-width:calc(100% - 32px);">
          <div class="box-header with-border">
            <h3 class="box-title">Detalle Mensual</h3>
          </div>
          <div class="box-body">
            <div class="table-responsive">
              <table class="table table-bordered" id="tablaDetalleMensual">
                <thead>
                  <tr>
                    <th><i class="fa fa-calendar text-primary"></i> Mes</th>
                    <th><i class="fa fa-shopping-cart text-success"></i> Ventas</th>
                    <th><i class="fa fa-cubes text-info"></i> Cantidad Vendida</th>
                    <th><i class="fa fa-money text-warning"></i> Ingresos</th>
                    <th><i class="fa fa-percent text-danger"></i> Descuentos</th>
                    <th><i class="fa fa-credit-card text-muted"></i> Costos</th>
                    <th><i class="fa fa-line-chart text-primary"></i> Ganancia Neta</th>
                  </tr>
                </thead>
                <tbody>
                  <!-- Se llenará por JS -->
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
    </section>
  </div>
  <?php require_once '../templates/footer.php'; ?>
</div>
<link rel="stylesheet" href="../assets/bower_components/select2/dist/css/select2.min.css">
<script src="../assets/bower_components/chart.js/Chart.min.js"></script>
<script src="../assets/bower_components/select2/dist/js/select2.full.min.js"></script>
<script src="../code/reporteMensual.js"></script>
<script>
  $(document).ready(function() {
    $('#anio').select2({
      placeholder: 'Buscar año',
      allowClear: true,
      width: '100%'
    });
  });
</script>