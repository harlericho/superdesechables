const app = new (function () {
  this.facturasActivas = document.getElementById("tbody");
  this.listado = () => {
    fetch("../controllers/factura/facturaListadoAGController.php", {
      method: "GET",
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.length > 0) {
          html =
            "<table class='table table-bordered text-center' id='example1'>";
          html += "<thead>";
          html += "<tr>";
          html +=
            "<th>Usuario facturado</th><th>Dni</th><th>Cliente(s)</th><th>Forma pago</th><th>Número factura</th><th>Fecha</th><th>Subtotal</th><th>Impuesto %</th><th>Descuento</th><th>Total</th><th>Estado</th><th>Acción</th>";
          html += "</tr>";
          html += "</thead>";
          html += "<tbody>";
          data.forEach((element) => {
            html += "<tr>";
            html += "<td> <i>" + element.usuario_nombres + "</i></td>";
            html += "<td> <strong>" + element.cliente_dni + "</strong></td>";
            html +=
              "<td> <strong>" +
              element.cliente_apellidos +
              " " +
              element.cliente_nombres +
              "</strong></td>";
            html +=
              "<td> <strong>" +
              element.tipo_comp_descripcion +
              "</strong></td>";
            html += "<td> <b>" + element.factura_num_comprobante + "</b></td>";
            html +=
              "<td class='text-blue'><strong>" +
              element.factura_fecha +
              "</strong></td>";
            html +=
              "<td class='text-yellow'> <b>" +
              element.factura_subtotal +
              "</b></td>";
            html +=
              "<td class='text-red'>" +
              parseInt(element.factura_impuesto) +
              "</td>";
            html +=
              "<td class='text-red'>" +
              (element.factura_descuento_global ?? "0.0") +
              "</td>";
            html +=
              "<td class='text-green'> <b>" +
              element.factura_total +
              "</b></td>";
            html += "<td><span class='badge bg-green'>Activo</span></td>";
            html += "<td>";
            html +=
              "<button type='button' class='btn btn-danger btn-sm' style='margin-right: 5px;' title='Eliminar' onClick='app.eliminar(" +
              element.factura_id +
              ")'><i class= 'fa fa-trash'></i></button>";
            html +=
              "<a href='../controllers/factura/facturaPdfController.php?factura_id=" +
              element.factura_id +
              "' target='_blank' class='btn btn-info btn-sm' style='margin-right: 5px;' title='Ver PDF'><i class='fa fa-file-pdf-o'></i></a>";
            // Botón XML solo si tiene factura electrónica
            if (element.fe_clave_acceso) {
              html +=
                "<a href='../controllers/facturacion_electronica/feDescargarXMLController.php?factura_id=" +
                element.factura_id +
                "' class='btn btn-success btn-sm' style='margin-right: 5px;' title='Descargar XML Firmado'><i class='fa fa-code'></i></a>";
              html +=
                "<a href='../controllers/facturacion_electronica/feVerLogsController.php?factura_id=" +
                element.factura_id +
                "' target='_blank' class='btn btn-primary btn-sm' style='margin-right: 5px;' title='Ver Logs SRI'><i class='fa fa-list'></i></a>";
            }
            html +=
              "<button type='button' class='btn btn-warning btn-sm' title='Reenviar' onClick='app.abrirModalCorreo(" +
              element.factura_id +
              ")'><i class='fa fa-envelope'></i></button>";
            html += "</td>";
            this.abrirModalCorreo = (factura_id) => {
              $("#correoDestino").val(""); // Limpiar el input de correo
              $("#modalCorreo").modal("show");
              $("#facturaIdCorreo").val(factura_id);
            };
          });
          html += "</tr></tbody></table>";
          this.facturasActivas.innerHTML = html;
          // todo : Inicializar variables datatable
          $("#example1").DataTable({
            language: {
              lengthMenu: "Mostrar _MENU_ registros por página",
              zeroRecords: "No se encontraron resultados en su búsqueda",
              searchPlaceholder: "Buscar registros",
              search: "Buscar:",
              info: "Mostrando registros de _START_ al _END_ de un total de  _TOTAL_ registros",
              infoEmpty: "No existen registros",
              infoFiltered: "(filtrado de un total de _MAX_ registros)",
              loadingRecords: "Cargando...",
              processing: "Procesando...",
              paginate: {
                first: "Primero",
                last: "Último",
                next: "Siguiente",
              },
            },
          });
        } else {
          this.facturasActivas.innerHTML =
            "<tr><td colspan='9'>No hay facturas registradas</td></tr>";
        }
      })
      .catch((err) => console.log(err));
  };
  this.eliminar = (id) => {
    var form = new FormData();
    form.append("factura_id", id);
    swal({
      title: "¿Está seguro que desea anular la factura?",
      text: "Solo cambiará el estado de la factura",
      icon: "warning",
      buttons: true,
      dangerMode: true,
    }).then((willDelete) => {
      if (willDelete) {
        fetch("../controllers/factura/facturaAnularController.php", {
          method: "POST",
          body: form,
        })
          .then((res) => res.json())
          .then((data) => {
            if (data === 1) {
              swal(
                "Se ha procedido anular la factura y devuelto el stock vendido",
                {
                  icon: "success",
                },
              );
              this.listado();
            }
          })
          .catch((err) => console.log(err));
      } else {
        swal("Operación cancelada");
        return false;
      }
    });
  };
})();

// Envío de factura por correo desde el modal
$(document).ready(function () {
  $("#formCorreoFactura").on("submit", function (e) {
    e.preventDefault();
    var formData = new FormData(this);
    // ── Pasar a modo progreso ─────────────────────────────────────────────
    const pasos = [
      {
        titulo: "Preparando factura...",
        detalle: "Cargando datos e imágenes",
        pct: 15,
        label: "Paso 1 de 4",
      },
      {
        titulo: "Generando PDF adjunto...",
        detalle: "Construyendo documento para el destinatario",
        pct: 42,
        label: "Paso 2 de 4",
      },
      {
        titulo: "Conectando al servidor de correo...",
        detalle: "Autenticando con el servidor SMTP",
        pct: 70,
        label: "Paso 3 de 4",
      },
      {
        titulo: "Enviando correo...",
        detalle: "Transmitiendo PDF y XML al destinatario",
        pct: 90,
        label: "Paso 4 de 4",
      },
    ];

    const actualizarPaso = (idx) => {
      const p = pasos[idx];
      document.getElementById("correoPasoTitulo").textContent = p.titulo;
      document.getElementById("correoPasoDetalle").textContent = p.detalle;
      document.getElementById("correoProgressBar").style.width = p.pct + "%";
      document.getElementById("correoPasoNum").textContent = p.label;
    };

    // Bloquear cierre mientras se envía
    $("#modalCorreo").on("hide.bs.modal.sending", function (e) {
      e.preventDefault();
    });
    $("#correoModalHeader .close").hide();
    $("#correoFormBody, #correoFormFooter").hide();
    $("#correoProgresoBody").show();
    actualizarPaso(0);

    const delays = [1400, 2800, 4400];
    const timers = delays.map((ms, i) =>
      setTimeout(() => actualizarPaso(i + 1), ms),
    );

    const restaurarModal = () => {
      timers.forEach(clearTimeout);
      document.getElementById("correoProgressBar").style.width = "100%";
      setTimeout(() => {
        $("#modalCorreo").off("hide.bs.modal.sending");
        $("#modalCorreo").modal("hide");
        // Restaurar estado original para la próxima apertura
        $("#correoProgresoBody").hide();
        $("#correoFormBody, #correoFormFooter").show();
        $("#correoModalHeader .close").show();
        document.getElementById("correoProgressBar").style.width = "5%";
      }, 400);
    };
    // ── Fin configuración progreso ─────────────────────────────────────────

    fetch("../controllers/factura/facturaReenviarCorreoController.php", {
      method: "POST",
      body: formData,
    })
      .then((res) => res.json())
      .then((data) => {
        restaurarModal();
        if (data.success) {
          swal("¡Enviado!", "La factura fue enviada correctamente.", "success");
        } else {
          swal(
            "Error",
            data.message || "No se pudo enviar la factura.",
            "error",
          );
        }
        $("#modalCorreo").modal("hide");
      })
      .catch(() => {
        restaurarModal();
        swal("Error", "No se pudo enviar la factura.", "error");
        $("#modalCorreo").modal("hide");
      });
  });
});

app.listado();
