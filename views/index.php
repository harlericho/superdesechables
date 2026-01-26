<?php require_once '../templates/header.php'; ?>
<?php require_once '../config/empresa.php'; ?>
<div class="wrapper">
  <?php require_once '../templates/sidebar.php'; ?>
  <?php
  foreach (LoginModel::existeUsuarioEmailLogin($_SESSION['email']) as $key => $value) {
    $rolDescripcion = $value['rol_descripcion'];
  }
  ?>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        <?= Empresa::getComercio() ?>
        <small>Panel de control</small>
      </h1>
      <?php require_once '../templates/panel.php'; ?>
    </section>

    <!-- Main content -->
    <section class="content">
      <!-- Small boxes (Stat box) -->
      <div class="row">
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-aqua">
            <div class="inner">
              <h3 id="numVentasA"></h3>
              <p>Ventas Activas</p>
            </div>
            <div class="icon">
              <i class="ion ion-card"></i>
            </div>
            <?php
            if ($rolDescripcion == 'ADMINISTRADOR') {
            ?>
              <a href="factura_ga.php" class="small-box-footer">Más info <i class="fa fa-arrow-circle-right"></i></a>
            <?php
            } else {
            ?>
              <a href="factura_a.php" class="small-box-footer">Más info <i class="fa fa-arrow-circle-right"></i></a>
            <?php
            }
            ?>
          </div>
        </div>
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-purple">
            <div class="inner">
              <h3 id="numVentasI"></h3>
              <p>Ventas Anuladas</p>
            </div>
            <div class="icon">
              <i class="ion ion-card"></i>
            </div>
            <?php
            if ($rolDescripcion == 'ADMINISTRADOR') {
            ?>
              <a href="factura_gi.php" class="small-box-footer">Más info <i class="fa fa-arrow-circle-right"></i></a>
            <?php
            } else {
            ?>
              <a href="factura_i.php" class="small-box-footer">Más info <i class="fa fa-arrow-circle-right"></i></a>
            <?php
            }
            ?>
          </div>
        </div>
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-maroon">
            <div class="inner">
              <h3 id="numDinero"></h3>
              <p>Dinero Mensual</p>
            </div>
            <div class="icon">
              <i class="ion ion-cash"></i>
            </div>
            <a href="reporteMensual.php" class="small-box-footer">Más info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-light-blue">
            <div class="inner">
              <h3 id="numGanacias">0</h3>
              <p>Ganancias</p>
            </div>
            <div class="icon">
              <i class="ion ion-social-usd"></i>
            </div>
            <a href="ganancias.php" class="small-box-footer">Más info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-green">
            <div class="inner">
              <h3 id="numUsuarios"></h3>
              <p>Usuarios</p>
            </div>
            <div class="icon">
              <i class="ion ion-unlocked"></i>
            </div>
            <a href="usuario.php" class="small-box-footer">Más info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-yellow">
            <div class="inner">
              <h3 id="numClientes"></h3>
              <p>Clientes</p>
            </div>
            <div class="icon">
              <i class="ion ion-person-stalker"></i>
            </div>
            <a href="cliente.php" class="small-box-footer">Más info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-red">
            <div class="inner">
              <h3 id="numProductos"></h3>
              <p>Productos</p>
            </div>
            <div class="icon">
              <i class="ion ion-filing"></i>
            </div>
            <a href="producto.php" class="small-box-footer">Más info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-primary">
            <div class="inner">
              <h3 id="numProveedores"></h3>
              <p>Proveedores</p>
            </div>
            <div class="icon">
              <i class="ion ion-person"></i>
            </div>
            <a href="proveedor.php" class="small-box-footer">Más info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-teal">
            <div class="inner">
              <h3 id="numRoles"></h3>
              <p>Roles</p>
            </div>
            <div class="icon">
              <i class="ion ion-gear-a"></i>
            </div>
            <a href="rol.php" class="small-box-footer">Más info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-orange">
            <div class="inner">
              <h3 id="numCategorias"></h3>
              <p>Categorías</p>
            </div>
            <div class="icon">
              <i class="ion ion-pricetags"></i>
            </div>
            <a href="categoria.php" class="small-box-footer">Más info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->
      </div>
      <!-- /.row -->
      <!-- Main row -->


      <div class="row">
        <!-- Grafico para mostrar el detalle del dinero o ventas en general. -->
        <section class="col-lg-6 connectedSortable">

          <!-- Custom tabs (Charts with tabs)-->
          <div class="nav-tabs-custom">
            <!-- Tabs within a box -->
            <ul class="nav nav-tabs pull-right">
              <li class="active"><a href="#revenue-chart" data-toggle="tab">Area</a></li>
              <li class="pull-left header"><i class="fa fa-inbox"></i> Ventas</li>
            </ul>
            <div class="tab-content no-padding">
              <!-- Morris chart - Sales -->
              <div class="chart tab-pane active" id="revenue-chart" style="position: relative; height: 300px;"></div>
              <div class="chart tab-pane" id="sales-chart" style="position: relative; height: 300px;"></div>
            </div>
          </div>
          <!-- /.nav-tabs-custom -->
        </section>
        <!-- Mostrar calendario -->
        <section class="col-lg-6 connectedSortable">
          <!-- Calendar -->
          <div class="box box-solid bg-blue-gradient">
            <div class="box-header">
              <i class="fa fa-calendar"></i>

              <h3 class="box-title">Calendario</h3>
              <!-- tools box -->
              <div class="pull-right box-tools">
                <button type="button" class="btn btn-primary btn-sm" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
                <button type="button" class="btn btn-primary btn-sm" data-widget="remove"><i class="fa fa-times"></i>
                </button>
              </div>
              <!-- /. tools -->
            </div>
            <!-- /.box-header -->
            <div class="box-body no-padding">
              <!--The calendar -->
              <div id="calendar" style="width: 100%"></div>
            </div>
          </div>
          <!-- /.box -->

        </section>
        <!-- right col -->
      </div>
      <!-- /.row (main row) -->

    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  <?php require_once '../templates/footer.php'; ?>
  <script src="../code/index.js"></script>