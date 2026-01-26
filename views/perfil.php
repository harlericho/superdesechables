<?php require_once '../templates/header.php'; ?>
<div class="wrapper">
  <?php require_once '../templates/sidebar.php'; ?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Perfil
        <small>Panel de control</small>
      </h1>
      <?php require_once '../templates/panel.php'; ?>
    </section>

    <!-- Main content -->
    <section class="content">
      <!-- Small boxes (Stat box) -->
      <div class="row">
        <!-- small box -->
        <div class="col-md-9">
          <div class="nav-tabs-custom box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title">Perfil</h3>
            </div>
            <div class="tab-content">
              <div class="active tab-pane" id="settings">
                <form class="form-horizontal" onsubmit="app.actualizarDatos()" action="javascript:void(0);">
                  <div class="form-group">
                    <label for="nombres" class="col-sm-2 control-label">Nombres</label>
                    <div class="col-sm-10">
                      <input type="text" class="form-control" id="usuario_nombres" placeholder="Nombres usuario" autofocus required>
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="email" class="col-sm-2 control-label">Email</label>
                    <div class="col-sm-10">
                      <input type="email" class="form-control" id="usuario_email" placeholder="Email" readonly>
                    </div>
                  </div>
                  <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                      <button type="submit" class="btn btn-info">Actualizar</button>
                    </div>
                  </div>
                </form>
              </div>
              <!-- /.tab-pane -->
            </div>
            <!-- /.tab-content -->
          </div>
          <!-- /.nav-tabs-custom -->
        </div>
        <div class="col-md-3">
          <div class="box box-primary">
            <div class="box-body box-profile">
              <img class="profile-user-img img-responsive img-circle" src="../assets/image/user.png" alt="User profile picture">

              <h3 class="profile-username text-center">
                <div id="nombres"></div>
              </h3>
              <div class="text-muted text-center" id="email">
              </div>
              <div class="text-muted text-center" id="rol">
              </div>
              <!-- /.box-body -->
            </div>
          </div>
          <!-- ./col -->
        </div>
        <!-- /.row -->
        <!-- Main row -->


    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  <?php require_once '../templates/footer.php'; ?>
  <script src="../code/perfil.js"></script>