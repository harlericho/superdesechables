<?php
include_once '../models/loginModel.php';
require_once '../config/empresa.php';
foreach (LoginModel::existeUsuarioEmailLogin($_SESSION['email']) as $key => $value) {
  $nombres = $value['usuario_nombres'];
  $email = $value['usuario_email'];
  $rolDescripcion = $value['rol_descripcion'];
}
?>
<header class="main-header">
  <!-- Logo -->
  <a href="index.php" class="logo">
    <!-- mini logo for sidebar mini 50x50 pixels -->
    <span class="logo-mini"><b><?= Empresa::getBanner1() ?></b><?= Empresa::getBanner2() ?></span>
    <!-- logo for regular state and mobile devices -->
    <span class="logo-lg"><b><?= Empresa::getNavbar() ?></b><?= Empresa::getBanner2() ?></span>
  </a>
  <!-- Header Navbar: style can be found in header.less -->
  <nav class="navbar navbar-static-top">
    <!-- Sidebar toggle button-->
    <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
      <span class="sr-only">Toggle navigation</span>
    </a>
    <div class="navbar-custom-menu">
      <ul class="nav navbar-nav">
        <li class="dropdown messages-menu">
          <a href="" class="dropdown-toggle" data-toggle="dropdown">
            <span class="hidden-xs">
              <strong>
                <?php
                $diassemana = array("Domingo", "Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sábado");
                $meses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
                echo $diassemana[date('w')] . " " . date('d') . " de " . $meses[date('n') - 1] . " del " . date('Y');
                ?>
              </strong>
            </span>
          </a>
        </li>
        <!-- Detalle del usuario -->
        <li class="dropdown user user-menu">
          <a href="" class="dropdown-toggle" data-toggle="dropdown">
            <img src="../assets/image/user.png" class="user-image" alt="User Image">
            <span class="hidden-xs"></span>
            <?php echo $nombres; ?>
          </a>

          <ul class="dropdown-menu">
            <!-- User image -->
            <li class="user-header">
              <img src="../assets/image/user.png" class="img-circle" alt="User Image">
              <p>
                <?php echo $nombres; ?>
                <small>
                  <?php echo $email; ?>
                  <br />
                  <?php echo $rolDescripcion; ?>
                </small>
              </p>
            </li>
            <!-- Menu Footer-->
            <li class="user-footer">
              <div class="pull-left">
                <a href="perfil.php" class="btn btn-default btn-flat">Perfil</a>
              </div>
              <div class="pull-right">
                <a href="out.php" class="btn btn-default btn-flat">Salir</a>
              </div>
            </li>
          </ul>
        </li>
      </ul>
    </div>
  </nav>

</header>
<!-- Menu de los modulos -->
<!-- Left side column. contains the logo and sidebar -->
<aside class="main-sidebar">
  <!-- sidebar: style can be found in sidebar.less -->
  <section class="sidebar">
    <!-- Sidebar user panel -->
    <div class="user-panel">
      <div class="pull-left image">
        <img src="../assets/image/<?= Empresa::getLogoLogin() ?>" class="img-circle" alt="User Image">
      </div>
      <div class="pull-left info">
        <p><?= Empresa::getNombre() ?></p>
        <a href="#"><i class="fa fa-circle text-success"></i> En linea</a>
      </div>
    </div>

    <!-- sidebar menu: : style can be found in sidebar.less -->
    <ul class="sidebar-menu" data-widget="tree">
      <li class="header"><?php echo $_SESSION["email"] ?></li>
      <li class="active treeview">
        <a href="index.php">
          <i class="fa fa-dashboard"></i>
          <?php
          if ($rolDescripcion == 'ADMINISTRADOR') {
          ?>
            <span class="btn btn-success">Panel</span>
          <?php
          } else {
          ?>
            <span class="btn btn-primary">Panel</span>
          <?php
          }
          ?>
          <span class="pull-right-container label label-success">
            <i class="fa fa-angle-left pull-right"></i>
          </span>
        </a>
      </li>
      <?php
      if ($rolDescripcion == 'ADMINISTRADOR') {
      ?>
        <li class="treeview">
          <a href="#">
            <i class="fa fa-suitcase "></i>
            <span>Productos</span>
            <span class="pull-right-container label label-success">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="producto.php"><i class="fa fa-circle-o"></i> Agregar / Mostrar</a></li>
            <li><a href="producto_masivo.php"><i class="fa fa-circle-o"></i> Producto masivo</a></li>
            <li><a href="producto_codigo_barra.php"><i class="fa fa-circle-o"></i> Código barras</a></li>
          </ul>
        </li>
      <?php
      }
      ?>
      <li class="treeview">
        <a href="#">
          <i class="fa fa-money"></i>
          <span>Ventas</span>
          <span class="pull-right-container label label-success">
            <i class="fa fa-angle-left pull-right"></i>
          </span>
        </a>
        <ul class="treeview-menu">
          <li><a href="venta.php"><i class="fa fa-circle-o"></i> Realizar</a></li>
        </ul>
      </li>
      <?php
      if ($rolDescripcion == 'ADMINISTRADOR') {
      ?>
        <li class="treeview">
          <a href="#">
            <i class="fa fa-cart-plus"></i>
            <span>Facturas</span>
            <span class="pull-right-container label label-success">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="factura_ga.php"><i class="fa fa-circle-o"></i> Activas</a></li>
            <li><a href="factura_gi.php"><i class="fa fa-circle-o"></i> Anuladas</a></li>
          </ul>
        </li>
      <?php
      } else {
      ?>
        <li class="treeview">
          <a href="#">
            <i class="fa fa-cart-plus"></i>
            <span>Facturas</span>
            <span class="pull-right-container label label-success">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="factura_a.php"><i class="fa fa-circle-o"></i> Activas</a></li>
            <li><a href="factura_i.php"><i class="fa fa-circle-o"></i> Anuladas</a></li>
          </ul>
        </li>
      <?php
      }
      ?>
      <?php
      if ($rolDescripcion == 'ADMINISTRADOR') {
      ?>
        <li class="treeview">
          <a href="#">
            <i class="fa fa-line-chart"></i> <span>Detalle facturas</span>
            <span class="pull-right-container label label-success">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="detalle_ga.php"><i class="fa fa-circle-o"></i> Activas</a></li>
            <li><a href="detalle_gi.php"><i class="fa fa-circle-o"></i> Inactivas</a></li>
          </ul>
        </li>
      <?php
      } else {
      ?>
        <li class="treeview">
          <a href="#">
            <i class="fa fa-line-chart"></i> <span>Detalle facturas</span>
            <span class="pull-right-container label label-success">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="detalle_a.php"><i class="fa fa-circle-o"></i> Activas</a></li>
            <li><a href="detalle_i.php"><i class="fa fa-circle-o"></i> Inactivas</a></li>
          </ul>
        </li>
      <?php
      }
      ?>
      <?php
      if ($rolDescripcion == 'ADMINISTRADOR') {
      ?>
        <li class="treeview">
          <a href="#">
            <i class="fa fa-table "></i>
            <span>Reportes</span>
            <span class="pull-right-container label label-success">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="../controllers/cliente/clienteReporteController.php" target="_blank"><i class="fa fa-circle-o"></i> Clientes</a></li>
            <li><a href="../controllers/proveedor/proveedorReporteController.php" target="_blank"><i class="fa fa-circle-o"></i> Proveedores</a></li>
            <li><a href="../controllers/usuario/usuarioReporteController.php" target="_blank"><i class="fa fa-circle-o"></i> Usuarios</a></li>
          </ul>
        </li>
      <?php
      }
      ?>
      <?php
      if ($rolDescripcion == 'ADMINISTRADOR') {
      ?>
        <li class="treeview">
          <a href="#">
            <i class="fa fa-shopping-basket"></i> <span>Caja Chica</span>
            <span class="pull-right-container label label-success">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="mov_cajachica.php"><i class="fa fa-circle-o"></i> Mov. Caja Chica</a></li>
            <li><a href="cierre_caja.php"><i class="fa fa-circle-o"></i> Cierre Caja Chica</a></li>
          </ul>
        </li>
      <?php
      }
      ?>
      <li class="treeview">
        <a href="#">
          <i class="fa fa-users"></i>
          <span>Clientes</span>
          <span class="pull-right-container label label-success">
            <i class="fa fa-angle-left pull-right"></i>
          </span>
        </a>
        <ul class="treeview-menu">
          <li><a href="cliente.php"><i class="fa fa-circle-o"></i> Agregar / Mostrar</a></li>
          <?php
          if ($rolDescripcion == 'ADMINISTRADOR') {
          ?>
            <li><a href="cliente_masivo.php"><i class="fa fa-circle-o"></i> Cliente masivo</a></li>
          <?php
          }
          ?>
        </ul>
      </li>
      <?php
      if ($rolDescripcion == 'ADMINISTRADOR') {
      ?>
        <li class="treeview">
          <a href="#">
            <i class="fa fa-user-secret"></i>
            <span>Administrador</span>
            <span class="pull-right-container label label-success">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="configserie.php"><i class="fa fa-circle-o"></i> Serie factura</a></li>
            <li><a href="rol.php"><i class="fa fa-circle-o"></i> Roles</a></li>
            <li><a href="usuario.php"><i class="fa fa-circle-o"></i> Usuarios</a></li>
            <li><a href="categoria.php"><i class="fa fa-circle-o"></i> Categoria productos</a></li>
            <li><a href="categoria_masivo.php"><i class="fa fa-circle-o"></i> Categoria productos masivo</a></li>
            <li><a href="proveedor.php"><i class="fa fa-circle-o"></i> Proveedores</a></li>
            <li><a href="comprobante.php"><i class="fa fa-circle-o"></i> Forma pago</a></li>
          </ul>
        </li>
      <?php
      }
      ?>
      <li class="treeview">
        <a href="#">
          <i class="fa fa-user-plus"></i>
          <span>Perfil</span>
          <span class="pull-right-container label label-success">
            <i class="fa fa-angle-left pull-right"></i>
          </span>
        </a>
        <ul class="treeview-menu">
          <li><a href="perfil.php"><i class="fa fa-circle-o"></i> Agregar / Mostrar</a></li>
        </ul>
      </li>
    </ul>

  </section>
  <!-- /.sidebar -->
</aside>