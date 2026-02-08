<?php require_once '../templates/header.php'; ?>

<style>
  #tablaPeriodo {
    font-size: 1.05rem;
  }

  #tablaPeriodo th {
    font-weight: 600;
    padding: 1rem 0.75rem;
    font-size: 1rem;
    background-color: #AACFF2;
    color: white;
    border: none;
  }

  #tablaPeriodo td {
    padding: 0.8rem 0.75rem;
    font-size: 0.95rem;
    border-top: 1px solid #dee2e6;
  }

  .badge {
    font-size: 0.9em;
    padding: 0.5em 0.75em;
  }

  /* Canvas del gráfico */
  #graficoGanancias {
    width: 100% !important;
    height: 300px !important;
  }

  /* Mejorar el card del gráfico */
  .chart-container {
    position: relative;
    height: 330px;
    width: 100%;
    padding: 1rem;
  }

  /* Columnas del análisis por período - Distribución mejorada */
  #tab-periodo .col-md-4:first-child {
    padding-right: 10px;
  }

  #tab-periodo .col-md-8:last-child {
    padding-left: 10px;
  }

  /* Hacer que el gráfico sea más compacto y la tabla más amplia */
  #tab-periodo .col-md-4 .card {
    height: 450px;
  }

  #tab-periodo .col-md-8 .card {
    height: 450px;
  }

  #tab-periodo .col-md-4 .card-body {
    padding: 1rem;
    overflow-y: auto;
  }

  #tab-periodo .col-md-8 .card-body {
    padding: 1.2rem;
    overflow-y: auto;
  }

  /* Tabla de período más grande y legible */
  #tablaPeriodo {
    font-size: 1.2rem;
    width: 100%;
    margin-bottom: 0;
  }

  #tablaPeriodo th {
    font-weight: 600;
    padding: 1.4rem 1rem;
    font-size: 1.1rem;
    background: linear-gradient(135deg, #AACFF2 0%, #AACFF2 100%);
    color: white;
    border: none;
    text-align: center;
    white-space: nowrap;
    box-shadow: 0 2px 4px rgba(0, 123, 255, 0.3);
  }

  #tablaPeriodo td {
    padding: 1.3rem 1rem;
    font-size: 1.1rem;
    border-top: 1px solid #dee2e6;
    vertical-align: middle;
    text-align: center;
    font-weight: 500;
  }

  /* Anchos específicos para cada columna */
  #tablaPeriodo th:nth-child(1),
  #tablaPeriodo td:nth-child(1) {
    width: 20%;
  }

  /* Período */
  #tablaPeriodo th:nth-child(2),
  #tablaPeriodo td:nth-child(2) {
    width: 12%;
  }

  /* Ventas */
  #tablaPeriodo th:nth-child(3),
  #tablaPeriodo td:nth-child(3) {
    width: 10%;
  }

  /* Cant. */
  #tablaPeriodo th:nth-child(4),
  #tablaPeriodo td:nth-child(4) {
    width: 18%;
  }

  /* Ingresos */
  #tablaPeriodo th:nth-child(5),
  #tablaPeriodo td:nth-child(5) {
    width: 18%;
  }

  /* Ganancia */
  #tablaPeriodo th:nth-child(6),
  #tablaPeriodo td:nth-child(6) {
    width: 15%;
  }

  /* Margen */

  .badge {
    font-size: 1em;
    padding: 0.7em 0.9em;
    font-weight: 600;
  }

  /* Efectos hover para las filas */
  #tablaPeriodo tbody tr:hover {
    background: linear-gradient(90deg, #f8f9fa 0%, #e9ecef 100%) !important;
    transform: translateY(-2px);
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    cursor: pointer;
  }

  /* Mejorar headers de la tabla */
  #tablaPeriodo thead th {
    position: sticky;
    top: 0;
    z-index: 10;
    box-shadow: 0 4px 6px rgba(0, 123, 255, 0.3);
  }

  /* Espaciado del card de la tabla */
  #tab-periodo .col-md-8:last-child .card-body {
    padding: 1.5rem 1.2rem;
  }

  /* Altura mínima para asegurar buena visualización */
  #tablaPeriodo tbody tr {
    min-height: 65px;
  }

  /* Mejorar la visualización de números */
  #tablaPeriodo td.numero {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    font-weight: 600;
  }

  /* Colores alternados para las filas */
  #tablaPeriodo tbody tr:nth-child(even) {
    background-color: #f8f9fa;
  }

  #tablaPeriodo tbody tr:nth-child(odd) {
    background-color: #ffffff;
  }

  /* Estilos especiales para valores monetarios */
  #tablaPeriodo .valor-monetario {
    color: #28a745;
    font-weight: 600;
  }

  #tablaPeriodo .porcentaje {
    color: #007bff;
    font-weight: 600;
  }

  /* Mejorar el contraste del texto */
  #tablaPeriodo td {
    color: #2c3e50;
  }

  /* Tabla responsiva con scroll horizontal */
  .table-responsive {
    border-radius: 0.375rem;
    box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.05);
  }

  /* Animación suave para el hover */
  #tablaPeriodo tbody tr {
    transition: all 0.2s ease-in-out;
  }

  /* Alertas - Simplificado y limpio */
  #alertas {
    margin: 15px auto;
    max-width: 1200px;
    padding: 0 15px;
  }

  /* Usar estilos Bootstrap puros */
  #alertas .alert {
    /* Bootstrap se encargará de los estilos */
  }

  /* Botones modernos y profesionales */
  .btn {
    font-weight: 500;
    border-radius: 0.375rem;
    transition: all 0.2s ease-in-out;
    border: none;
    letter-spacing: 0.025em;
  }

  .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  }

  .btn:active {
    transform: translateY(0);
  }

  /* Colores personalizados para botones */
  .btn-primary {
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
    border: none;
  }

  .btn-primary:hover {
    background: linear-gradient(135deg, #0056b3, #004085);
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
  }

  .btn-secondary {
    background: linear-gradient(135deg, #6c757d, #5a6268);
    color: white;
  }

  .btn-secondary:hover {
    background: linear-gradient(135deg, #5a6268, #495057);
    color: white;
  }

  .btn-success {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
  }

  .btn-success:hover {
    background: linear-gradient(135deg, #20c997, #1e7e34);
    color: white;
  }

  .btn-danger {
    background: linear-gradient(135deg, #dc3545, #e74c3c);
    color: white;
  }

  .btn-danger:hover {
    background: linear-gradient(135deg, #e74c3c, #c82333);
    color: white;
  }

  /* Mejorar toolbar de botones - sin fondo */
  .btn-toolbar {
    /* Sin estilos extra, solo espaciado */
  }

  @media (max-width: 768px) {
    #tablaGanancias {
      font-size: 0.8rem;
    }

    #graficoGanancias {
      height: 200px !important;
    }

    .chart-container {
      height: 230px;
      padding: 0.5rem;
    }

    #tab-periodo .col-md-4,
    #tab-periodo .col-md-8 {
      margin-bottom: 1rem;
      padding: 0;
    }

    #tab-periodo .col-md-4 .card,
    #tab-periodo .col-md-8 .card {
      height: auto;
    }

    /* Mejorar la tabla en móviles */
    #tablaPeriodo {
      font-size: 0.95rem;
    }

    #tablaPeriodo th {
      padding: 1rem 0.6rem;
      font-size: 0.9rem;
    }

    #tablaPeriodo td {
      padding: 0.9rem 0.6rem;
      font-size: 0.85rem;
    }

    /* Distribución en móviles: gráfico arriba, tabla abajo */
    #tab-periodo .row .col-md-4:first-child {
      order: 1;
    }

    #tab-periodo .row .col-md-8:last-child {
      order: 2;
    }

    /* Botones responsive en móviles */
    .btn-toolbar {
      flex-direction: column;
      gap: 0.75rem;
    }

    .btn-toolbar .btn-group {
      width: 100%;
    }

    .btn-toolbar .btn {
      width: 100%;
      margin: 0 0 0.5rem 0 !important;
    }

    .btn-toolbar .btn:last-child {
      margin-bottom: 0 !important;
    }

    .btn-group {
      width: 100%;
      margin-bottom: 0.5rem;
      display: flex;
      justify-content: space-between;
    }

    .btn-group .btn {
      flex: 1;
      margin: 0 2px;
      font-size: 0.9rem;
    }

    /* Alertas en móviles */
    #alertas .alert {
      font-size: 0.9rem;
      padding: 0.75rem;
    }
  }

  /* Mejoras adicionales para tablet */
  @media (min-width: 768px) and (max-width: 992px) {
    #tablaPeriodo {
      font-size: 1.05rem;
    }

    #tablaPeriodo th {
      padding: 1.2rem 0.8rem;
      font-size: 1rem;
    }

    #tablaPeriodo td {
      padding: 1.1rem 0.8rem;
      font-size: 0.95rem;
    }
  }
</style>

<div class="wrapper">
  <?php require_once '../templates/sidebar.php'; ?>

  <?php
  foreach (LoginModel::existeUsuarioEmailLogin($_SESSION['email']) as $key => $value) {
    $rolDescripcion = $value['rol_descripcion'];
  }
  ?>

  <?php if ($rolDescripcion == 'ADMINISTRADOR') { ?>
    <div class="content-wrapper">
      <!-- Content Header (Page header) -->
      <div class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1 class="m-0">Módulo de Ganancias</h1>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                <li class="breadcrumb-item active">Ganancias</li>
              </ol>
            </div>
          </div>
        </div>
      </div>

      <!-- Alertas -->
      <div id="alertas"></div>

      <!-- Main content -->
      <section class="content">
        <div class="container-fluid">

          <!-- Filtros -->
          <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title">
                    <i class="fa fa-filter"></i>
                    Filtros de Búsqueda
                  </h3>
                </div>
                <div class="card-body">
                  <div class="row">
                    <div class="col-md-3">
                      <div class="form-group">
                        <label for="fechaDesde">Fecha Desde:</label>
                        <input type="date" class="form-control" id="fechaDesde" name="fechaDesde" placeholder="Opcional: filtrar por fecha">
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="form-group">
                        <label for="fechaHasta">Fecha Hasta:</label>
                        <input type="date" class="form-control" id="fechaHasta" name="fechaHasta" placeholder="Opcional: filtrar por fecha">
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="form-group">
                        <label for="selectProducto">
                          <i class="ion ion-search text-primary"></i> Buscar Producto:
                        </label>
                        <select class="form-control select2" id="selectProducto" name="selectProducto" style="width: 100%;">
                          <option value="">Todos los productos</option>
                        </select>
                        <small class="text-muted">
                          <i class="ion ion-information-circled"></i> Selecciona un producto específico o deja en blanco para ver todos
                        </small>
                      </div>
                    </div>
                    <div class="col-md-2">
                      <div class="form-group">
                        <label>&nbsp;</label>
                        <div>
                          <button type="button" class="btn btn-primary btn-block" id="btnFiltrar" style="font-weight: 500; padding: 0.75rem 1.5rem; background: linear-gradient(135deg, #007bff, #0056b3); border: none; border-radius: 0.375rem;">
                            <i class="fa fa-search" style="margin-right: 0.5rem;"></i>Filtrar
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Botones de Acción alineados con los cards -->
          <div class="row" style="margin-bottom: 1rem;">
            <div class="col-12">
              <div class="btn-toolbar justify-content-between" role="toolbar">
                <div class="btn-group" role="group">
                  <button type="button" class="btn btn-info" id="btnLimpiarFiltros">
                    <i class="fa fa-eraser" style="margin-right: 0.5rem;"></i>Limpiar Filtros
                  </button>
                </div>
                <div class="btn-group" role="group">
                  <button type="button" class="btn btn-success" id="btnExportarExcel" style="margin-right: 0.5rem;">
                    <i class="fas fa-file-excel" style="margin-right: 0.5rem;"></i>Exportar Excel
                  </button>
                  <button type="button" class="btn btn-danger" id="btnExportarPDF">
                    <i class="fas fa-file-pdf" style="margin-right: 0.5rem;"></i>Exportar PDF
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Resumen de Ganancias -->
          <div class="row">
            <div class="col-lg-3 col-6">
              <div class="small-box bg-info">
                <div class="inner">
                  <h3 id="totalProductos">0</h3>
                  <p>Total Productos</p>
                </div>
                <div class="icon">
                  <i class="ion ion-bag"></i>
                </div>
              </div>
            </div>
            <div class="col-lg-3 col-6">
              <div class="small-box bg-success">
                <div class="inner">
                  <h3 id="totalVentas">$ 0.00</h3>
                  <p>Total Ventas</p>
                </div>
                <div class="icon">
                  <i class="ion ion-stats-bars"></i>
                </div>
              </div>
            </div>
            <div class="col-lg-3 col-6">
              <div class="small-box bg-warning">
                <div class="inner">
                  <h3 id="totalCostos">$ 0.00</h3>
                  <p>Total Costos</p>
                </div>
                <div class="icon">
                  <i class="ion ion-cash"></i>
                </div>
              </div>
            </div>
            <div class="col-lg-3 col-6">
              <div class="small-box bg-danger">
                <div class="inner">
                  <h3 id="gananciaNeta">$ 0.00</h3>
                  <p>Ganancia Neta</p>
                </div>
                <div class="icon">
                  <i class="ion ion-pie-graph"></i>
                </div>
              </div>
            </div>
          </div>

          <!-- Pestañas -->
          <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-header p-2">
                  <ul class="nav nav-pills">
                    <li class="nav-item">
                      <a class="nav-link active" href="#tab-productos" data-toggle="tab">
                        <i class="fa fa-list"></i> Ganancias por Producto
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="#tab-periodo" data-toggle="tab">
                        <i class="fas fa-chart-line"></i> Análisis por Periodo
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="#tab-resumen" data-toggle="tab">
                        <i class="fas fa-chart-pie"></i> Resumen Detallado
                      </a>
                    </li>
                  </ul>
                </div>
                <div class="card-body">
                  <div class="tab-content">

                    <!-- Tab Ganancias por Producto -->
                    <div class="active tab-pane" id="tab-productos" style="padding-top: 15px;">
                      <div class="table-responsive">
                        <table id="tablaGanancias" class="table table-bordered table-striped">
                          <thead style="background-color: #f8f9fa;">
                            <tr>
                              <th style="padding: 12px 8px; font-weight: 600;">Código</th>
                              <th style="padding: 12px 8px; font-weight: 600;">Producto</th>
                              <th style="padding: 12px 8px; font-weight: 600;">Categoría</th>
                              <th style="padding: 12px 8px; font-weight: 600;">Cantidad</th>
                              <th style="padding: 12px 8px; font-weight: 600;">P. Compra</th>
                              <th style="padding: 12px 8px; font-weight: 600;">P. Venta</th>
                              <th style="padding: 12px 8px; font-weight: 600;">Total Ventas</th>
                              <th style="padding: 12px 8px; font-weight: 600;">Descuentos</th>
                              <th style="padding: 12px 8px; font-weight: 600;">Costo Total</th>
                              <th style="padding: 12px 8px; font-weight: 600;">Ganancia Bruta</th>
                              <th style="padding: 12px 8px; font-weight: 600;">Ganancia Neta</th>
                              <th style="padding: 12px 8px; font-weight: 600;">Margen %</th>
                            </tr>
                          </thead>
                          <tbody>
                          </tbody>
                        </table>
                      </div>
                    </div>

                    <!-- Tab Análisis por Periodo -->
                    <div class="tab-pane" id="tab-periodo">
                      <div class="row">
                        <div class="col-md-3">
                          <div class="form-group">
                            <label for="selectPeriodo">Agrupar por:</label>
                            <select class="form-control" id="selectPeriodo">
                              <option value="diario">Diario</option>
                              <option value="semanal">Semanal</option>
                              <option value="mensual">Mensual</option>
                            </select>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-12">
                          <div class="row">
                            <div class="col-md-4">
                              <div class="card">
                                <div class="card-header">
                                  <h3 class="card-title"><i class="fas fa-chart-bar"></i> Gráfico de Ganancias</h3>
                                </div>
                                <div class="card-body">
                                  <div class="chart-container">
                                    <canvas id="graficoGanancias"></canvas>
                                  </div>
                                </div>
                              </div>
                            </div>
                            <div class="col-md-8">
                              <div class="card">
                                <div class="card-header">
                                  <h3 class="card-title"><i class="fa fa-table"></i> Datos por Período</h3>
                                </div>
                                <div class="card-body">
                                  <div class="table-responsive">
                                    <table id="tablaPeriodo" class="table table-hover">
                                      <thead>
                                        <tr>
                                          <th>Período</th>
                                          <th>Ventas</th>
                                          <th>Cant.</th>
                                          <th>Ingresos</th>
                                          <th>Ganancia</th>
                                          <th>Margen</th>
                                        </tr>
                                      </thead>
                                      <tbody>
                                      </tbody>
                                    </table>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Tab Resumen Detallado -->
                    <div class="tab-pane" id="tab-resumen">
                      <div class="row">
                        <div class="col-md-6">
                          <div class="box box-primary">
                            <div class="box-header with-border">
                              <h3 class="box-title">
                                <i class="ion ion-stats-bars"></i> Resumen General
                              </h3>
                            </div>
                            <div class="box-body table-responsive no-padding">
                              <table class="table table-hover">
                                <tr>
                                  <td style="padding: 12px 15px;">
                                    <i class="ion ion-cube text-aqua"></i> <strong>Total Productos:</strong>
                                  </td>
                                  <td class="text-right" style="padding: 12px 15px;" id="totalProductos2">-</td>
                                </tr>
                                <tr>
                                  <td style="padding: 12px 15px;">
                                    <i class="ion ion-bag text-red"></i> <strong>Cantidad Total Vendida:</strong>
                                  </td>
                                  <td class="text-right" style="padding: 12px 15px;" id="cantidadTotalVendida">-</td>
                                </tr>
                                <tr>
                                  <td style="padding: 12px 15px;">
                                    <i class="ion ion-cash text-yellow"></i> <strong>Total Ventas:</strong>
                                  </td>
                                  <td class="text-right" style="padding: 12px 15px;" id="totalVentas2">-</td>
                                </tr>
                                <tr>
                                  <td style="padding: 12px 15px;">
                                    <i class="ion ion-minus-circled text-orange"></i> <strong>Total Descuentos:</strong>
                                  </td>
                                  <td class="text-right" style="padding: 12px 15px;" id="totalDescuentos">-</td>
                                </tr>
                                <tr>
                                  <td style="padding: 12px 15px;">
                                    <i class="ion ion-pricetag text-gray"></i> <strong>Total Costos:</strong>
                                  </td>
                                  <td class="text-right" style="padding: 12px 15px;" id="totalCostos2">-</td>
                                </tr>
                                <tr class="success">
                                  <td style="padding: 15px;">
                                    <i class="ion ion-arrow-up-c text-green"></i> <strong>Ganancia Bruta:</strong>
                                  </td>
                                  <td class="text-right" style="padding: 15px; font-weight: bold; color: #00a65a;" id="gananciaBruta">-</td>
                                </tr>
                                <tr class="info">
                                  <td style="padding: 15px;">
                                    <i class="ion ion-trending-up text-light-blue"></i> <strong>Ganancia Neta:</strong>
                                  </td>
                                  <td class="text-right" style="padding: 15px; font-weight: bold; color: #3c8dbc;" id="gananciaNeta2">-</td>
                                </tr>
                                <tr class="warning">
                                  <td style="padding: 15px;">
                                    <i class="ion ion-pie-graph text-yellow"></i> <strong>Margen de Ganancia:</strong>
                                  </td>
                                  <td class="text-right" style="padding: 15px; font-weight: bold; color: #f39c12;" id="margenGanancia">-</td>
                                </tr>
                              </table>
                            </div>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="box box-info">
                            <div class="box-header with-border">
                              <h3 class="box-title">
                                <i class="ion ion-information-circled"></i> Información
                              </h3>
                            </div>
                            <div class="box-body">
                              <div class="callout callout-info">
                                <h4><i class="icon ion ion-calculator"></i> Cálculo de Ganancias</h4>
                                <p><strong>Ganancia Bruta:</strong> Precio de Venta - Precio de Compra</p>
                                <p><strong>Ganancia Neta:</strong> Ganancia Bruta - Descuentos</p>
                                <p><strong>Margen:</strong> (Ganancia Neta / Total Ventas) * 100</p>
                              </div>
                              <div class="callout callout-warning">
                                <h4><i class="icon ion ion-alert-circled"></i> Nota Importante</h4>
                                <p>Los cálculos se basan en las ventas realizadas y registradas en el sistema.
                                  Los productos sin ventas no aparecen en el reporte.</p>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>
      </section>
    </div>
  <?php } else { ?>
    <div class="content-wrapper">
      <?php include_once './403.php'; ?>
    </div>
  <?php } ?>
  <?php require_once '../templates/footer.php'; ?>
</div>

<!-- Chart.js desde CDN (versión estable) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<!-- DataTables -->
<script src="../assets/bower_components/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="../assets/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>

<!-- Ganancias JS -->
<script src="../code/ganancias.js"></script>