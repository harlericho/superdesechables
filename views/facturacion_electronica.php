<?php require_once '../templates/header.php'; ?>
<div class="wrapper">
  <?php require_once '../templates/sidebar.php'; ?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Facturación Electrónica
        <small>Configuración SRI Ecuador</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="index.php"><i class="fa fa-dashboard"></i> Inicio</a></li>
        <li class="active">Facturación Electrónica</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-md-12">
          <!-- Nav tabs -->
          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
              <li class="active"><a href="#datos-emisor" data-toggle="tab">
                  <i class="fa fa-building"></i> Datos del Emisor</a>
              </li>
              <li><a href="#certificado" data-toggle="tab">
                  <i class="fa fa-certificate"></i> Certificado Digital</a>
              </li>
              <li><a href="#configuracion" data-toggle="tab">
                  <i class="fa fa-cog"></i> Configuración</a>
              </li>
              <li><a href="#pruebas" data-toggle="tab">
                  <i class="fa fa-flask"></i> Pruebas</a>
              </li>
              <li><a href="#pendientes" data-toggle="tab">
                  <i class="fa fa-exclamation-triangle"></i> Comprobantes Pendientes</a>
              </li>
            </ul>

            <div class="tab-content">
              <!-- TAB 1: DATOS DEL EMISOR -->
              <div class="tab-pane active" id="datos-emisor">
                <form id="formDatosEmisor" method="POST">
                  <input type="hidden" id="config_fe_id" name="config_fe_id" value="">

                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label>RUC *</label>
                        <input type="text" class="form-control" id="config_fe_ruc" name="config_fe_ruc"
                          placeholder="1234567890001" maxlength="13" required>
                        <small class="text-muted">RUC de 13 dígitos</small>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Razón Social *</label>
                        <input type="text" class="form-control" id="config_fe_razon_social"
                          name="config_fe_razon_social" maxlength="300" required>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-12">
                      <div class="form-group">
                        <label>Nombre Comercial</label>
                        <input type="text" class="form-control" id="config_fe_nombre_comercial"
                          name="config_fe_nombre_comercial" maxlength="300">
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-12">
                      <div class="form-group">
                        <label>Dirección Matriz *</label>
                        <textarea class="form-control" id="config_fe_direccion_matriz"
                          name="config_fe_direccion_matriz" rows="2" required></textarea>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-12">
                      <div class="form-group">
                        <label>Dirección Sucursal</label>
                        <textarea class="form-control" id="config_fe_direccion_sucursal"
                          name="config_fe_direccion_sucursal" rows="2"></textarea>
                        <small class="text-muted">Si es igual a la matriz, dejar en blanco</small>
                      </div>
                    </div>
                  </div>

                  <!-- Información sobre Serie y Secuencial -->
                  <div class="row">
                    <div class="col-md-12">
                      <div class="callout callout-success">
                        <h4><i class="fa fa-info-circle"></i> Establecimiento, Punto de Emisión y Secuencial</h4>
                        <p>Estos datos se obtienen automáticamente del <strong>Módulo de Serie de Facturas</strong> (menú: Configurar Serie).</p>
                        <p>Si necesitas cambiar el establecimiento (001), punto de emisión (001) o el secuencial actual, modifícalo desde ese módulo.</p>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-3">
                      <div class="form-group">
                        <label>Obligado Contabilidad *</label>
                        <select class="form-control" id="config_fe_obligado_contabilidad"
                          name="config_fe_obligado_contabilidad" required>
                          <option value="NO">NO</option>
                          <option value="SI">SI</option>
                        </select>
                      </div>
                    </div>

                    <div class="col-md-3">
                      <div class="form-group">
                        <label>Régimen RIMPE</label>
                        <select class="form-control" id="config_fe_contribuyente_rimpe"
                          name="config_fe_contribuyente_rimpe">
                          <option value="NO">No aplica</option>
                          <option value="NEGOCIO_POPULAR">RIMPE - Negocio Popular</option>
                          <option value="EMPRENDEDOR">RIMPE - Emprendedor</option>
                        </select>
                        <small class="text-muted">Verifícalo en la Consulta RUC del portal del SRI (campo "Régimen"). Define el texto legal que se imprime en factura y XML.</small>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-12">
                      <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Guardar Datos del Emisor
                      </button>
                    </div>
                  </div>
                </form>
              </div>

              <!-- TAB 2: CERTIFICADO DIGITAL -->
              <div class="tab-pane" id="certificado">
                <div class="callout callout-info">
                  <h4><i class="fa fa-info-circle"></i> Certificado Digital (.p12)</h4>
                  <p>Para emitir facturas electrónicas necesitas un certificado digital (.p12) emitido por una entidad certificadora autorizada por el SRI.</p>
                  <p>El certificado debe estar vigente y corresponder al RUC del emisor.</p>
                </div>

                <form id="formCertificado" method="POST" enctype="multipart/form-data">
                  <div id="certificado-actual" style="display:none;">
                    <div class="alert alert-success">
                      <h4><i class="fa fa-check"></i> Certificado cargado</h4>
                      <p>
                        <strong>Archivo:</strong> <span id="cert-nombre"></span><br>
                        <strong>Fecha de caducidad:</strong> <span id="cert-caducidad"></span>
                      </p>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-8">
                      <div class="form-group">
                        <label>Subir Certificado (.p12) *</label>
                        <input type="file" id="certificado_archivo" name="certificado_archivo"
                          accept=".p12,.pfx" required>
                        <small class="text-muted">Archivo .p12 o .pfx obtenido de la entidad certificadora</small>
                      </div>
                    </div>

                    <div class="col-md-4">
                      <div class="form-group">
                        <label>Contraseña del Certificado *</label>
                        <input type="password" class="form-control" id="config_fe_certificado_password"
                          name="config_fe_certificado_password" required>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-4">
                      <div class="form-group">
                        <label>Fecha de Caducidad</label>
                        <input type="date" class="form-control" id="config_fe_certificado_fecha_caducidad"
                          name="config_fe_certificado_fecha_caducidad">
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-12">
                      <button type="submit" class="btn btn-success">
                        <i class="fa fa-upload"></i> Cargar Certificado
                      </button>
                      <button type="button" class="btn btn-warning" id="btnValidarCertificado">
                        <i class="fa fa-check-circle"></i> Validar Certificado
                      </button>
                    </div>
                  </div>
                </form>
              </div>

              <!-- TAB 3: CONFIGURACIÓN -->
              <div class="tab-pane" id="configuracion">
                <form id="formConfiguracion" method="POST">
                  <div class="row">
                    <div class="col-md-6">
                      <div class="box box-primary">
                        <div class="box-header with-border">
                          <h3 class="box-title">Ambiente SRI</h3>
                        </div>
                        <div class="box-body">
                          <div class="form-group">
                            <label>
                              <input type="radio" name="config_fe_ambiente" value="PRUEBAS" checked>
                              <strong>PRUEBAS</strong>
                            </label>
                            <p class="text-muted">Usar para realizar pruebas sin compromiso legal</p>
                          </div>
                          <div class="form-group">
                            <label>
                              <input type="radio" name="config_fe_ambiente" value="PRODUCCION">
                              <strong>PRODUCCIÓN</strong>
                            </label>
                            <p class="text-muted">Facturas reales con validez legal</p>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="box box-info">
                        <div class="box-header with-border">
                          <h3 class="box-title">Tipo de Emisión</h3>
                        </div>
                        <div class="box-body">
                          <div class="form-group">
                            <label>
                              <input type="radio" name="config_fe_tipo_emision" value="NORMAL" checked>
                              <strong>NORMAL</strong>
                            </label>
                            <p class="text-muted">Emisión en línea (requiere conexión)</p>
                          </div>
                          <div class="form-group">
                            <label>
                              <input type="radio" name="config_fe_tipo_emision" value="CONTINGENCIA">
                              <strong>CONTINGENCIA</strong>
                            </label>
                            <p class="text-muted">Emisión offline (envío posterior)</p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Email para Envío</label>
                        <input type="email" class="form-control" id="config_fe_email_envio"
                          name="config_fe_email_envio">
                        <small class="text-muted">Email que aparecerá como remitente</small>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Email Copia (opcional)</label>
                        <input type="email" class="form-control" id="config_fe_email_copia"
                          name="config_fe_email_copia">
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-12">
                      <div class="form-group">
                        <label>
                          <input type="checkbox" id="config_fe_enviar_email_automatico"
                            name="config_fe_enviar_email_automatico" value="1" checked>
                          Enviar email automáticamente al cliente después de autorizar
                        </label>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-12">
                      <div class="form-group">
                        <label>
                          <input type="checkbox" id="config_fe_activo" name="config_fe_activo" value="1">
                          <strong class="text-success">Activar Facturación Electrónica</strong>
                        </label>
                        <p class="text-muted">Al activar, todas las ventas generarán facturas electrónicas por defecto</p>
                      </div>
                      <div class="form-group">
                        <label>
                          <input type="checkbox" id="config_fe_permitir_ventas_simples" name="config_fe_permitir_ventas_simples" value="1">
                          <strong class="text-info">Permitir Emitir Ventas Internas (Sin SRI)</strong>
                        </label>
                        <p class="text-muted">Si se activa, el cajero podrá decidir no enviar la venta al SRI (generando solo un Ticket interno)</p>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-12">
                      <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fa fa-save"></i> Guardar Configuración
                      </button>
                    </div>
                  </div>
                </form>
              </div>

              <!-- TAB 4: PRUEBAS -->
              <div class="tab-pane" id="pruebas">
                <div class="callout callout-warning">
                  <h4><i class="fa fa-flask"></i> Pruebas de Facturación Electrónica</h4>
                  <p>Antes de activar en producción, realiza pruebas para verificar que todo funcione correctamente.</p>
                </div>

                <div class="row">
                  <div class="col-md-12">
                    <button type="button" class="btn btn-info btn-lg" id="btnProbarConexionSRI">
                      <i class="fa fa-plug"></i> Probar Conexión con SRI
                    </button>

                    <button type="button" class="btn btn-warning btn-lg" id="btnGenerarXMLPrueba">
                      <i class="fa fa-file-code-o"></i> Generar XML de Prueba
                    </button>

                    <button type="button" class="btn btn-success btn-lg" id="btnEnviarFacturaPrueba">
                      <i class="fa fa-paper-plane"></i> Enviar Factura de Prueba
                    </button>
                  </div>
                </div>

                <div class="row" style="margin-top: 20px;">
                  <div class="col-md-12">
                    <div class="box">
                      <div class="box-header with-border">
                        <h3 class="box-title">Resultado de Pruebas</h3>
                      </div>
                      <div class="box-body">
                        <pre id="resultado-pruebas" style="max-height: 400px; overflow-y: auto;">
Aquí aparecerán los resultados...
                          </pre>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- TAB 5: COMPROBANTES PENDIENTES -->
              <div class="tab-pane" id="pendientes">
                <div class="callout callout-warning">
                  <h4><i class="fa fa-warning"></i> Comprobantes No Autorizados</h4>
                  <p>Aquí se listan las facturas que no pudieron ser enviadas o autorizadas por el SRI debido a caídas del sistema, intermitencias o errores en los datos (como RUC incorrecto).</p>
                </div>

                <div class="row">
                  <div class="col-md-12 text-right" style="margin-bottom: 15px;">
                    <button type="button" class="btn btn-warning" id="btnReintentarTodas" onclick="reintentarTodas()">
                      <i class="fa fa-refresh"></i> Reintentar Todas las Pendientes
                    </button>
                  </div>
                </div>

                <div class="table-responsive">
                  <table id="tablaPendientes" class="table table-bordered table-striped" width="100%">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Factura</th>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Total</th>
                        <th>Estado SRI</th>
                        <th>Mensaje SRI</th>
                        <th>Acciones</th>
                      </tr>
                    </thead>
                    <tbody>
                      <!-- Cargado por AJAX -->
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <!-- Modal Progreso Reintento Masivo -->
  <div class="modal fade" id="modalReintentoMasivo" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Reintentando Facturas Pendientes</h4>
        </div>
        <div class="modal-body">
          <p id="reintentoTexto">Preparando...</p>
          <div class="progress progress-sm active">
            <div class="progress-bar progress-bar-warning progress-bar-striped" id="reintentoBarra" role="progressbar" style="width: 0%">
            </div>
          </div>
          <p id="reintentoDetalle" class="text-muted" style="margin-top: 10px; font-size: 12px;"></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal" id="btnCerrarReintento" style="display:none;">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <?php include_once '../templates/footer.php'; ?>
</div>

<script src="../code/facturacion_electronica.js"></script>