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
            "<th>Usuario facturado</th><th>Dni</th><th>Cliente(s)</th><th>Forma pago</th><th>Número factura</th><th>Fecha</th><th>Subtotal</th><th>Impuesto %</th><th>Total</th><th>Estado</th><th>Acción</th>";
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
              "<td class='text-green'> <b>" +
              element.factura_total +
              "</b></td>";
            html += "<td><span class='badge bg-green'>Activo</span></td>";
            html +=
              "<td>" +
              "<button type='button' class='btn btn-danger btn-sm' style='margin-right: 5%;' title='Eliminar' onClick='app.eliminar(" +
              element.factura_id +
              ")'><i class= 'fa fa-trash'></i></button> " +
              "<a href='../controllers/factura/facturaPdfController.php?factura_id=" +
              element.factura_id +
              "' target='_black' class='btn btn-info btn-sm' title='Pdf'><i class='fa fa-file-pdf-o'></i></a> " +
              "<button type='button' class='btn btn-warning btn-sm' title='Reenviar' onClick='app.abrirModalCorreo(" +
              element.factura_id +
              ")'><i class='fa fa-envelope'></i></button>" +
              "</td>";
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
    fetch("../controllers/factura/facturaReenviarCorreoController.php", {
      method: "POST",
      body: formData,
    })
      .then((res) => res.json())
      .then((data) => {
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
        swal("Error", "No se pudo enviar la factura.", "error");
        $("#modalCorreo").modal("hide");
      });
  });
});

app.listado();
