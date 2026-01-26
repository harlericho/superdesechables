const app = new (function () {
  this.detalleInactivas = document.getElementById("tbody");
  this.listado = () => {
    fetch("../controllers/detalle/detalleFacturaIGController.php", {
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
            "<th>Número factura</th><th>Fecha</th><th>Código</th><th>Producto(s)</th><th>Cantidad vendida</th><th>Precio unitario</th><th>Descuento %</th><th>Total</th><th>Estado</th><th>Acción</th>";
          html += "</tr>";
          html += "</thead>";
          html += "<tbody>";
          data.forEach((element) => {
            html += "<tr>";
            html += "<td> <b>" + element.factura_num_comprobante + "</b></td>";
            html +=
              "<td class='text-blue'><strong>" +
              element.factura_fecha +
              "</strong></td>";
            html +=
              "<td> <strong>" + element.producto_codigo + "</strong></td>";
            html +=
              "<td> <strong>" +
              element.producto_nombre +
              "</strong><br/><i>" +
              element.producto_descripcion +
              "</i> </td>";
            html +=
              "<td> <strong>" + element.detalle_cantidad + "</strong></td>";
            html +=
              "<td class='text-yellow'> <b>" +
              element.detalle_precio_unit +
              "</b></td>";
            html +=
              "<td class='text-red'>" +
              parseInt(element.detalle_descuento) +
              "</td>";
            html +=
              "<td class='text-green'> <b>" +
              element.detalle_total +
              "</b></td>";
            html += "<td><span class='badge bg-red'>Inactivo</span></td>";
            html +=
              "<td><button type='button' class='btn btn-danger btn-sm' style='margin-right: 5%;' title='Eliminar' onClick='app.eliminar(" +
              element.detalle_id +
              ")'><i class= 'fa fa-trash'></i></button>";
          });
          html += "</tr></tbody></table>";
          this.detalleInactivas.innerHTML = html;
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
          this.detalleInactivas.innerHTML =
            "<tr><td colspan='10'>No hay detalles facturas registradas</td></tr>";
        }
      })
      .catch((err) => console.log(err));
  };

  this.eliminar = (id) => {
    var form = new FormData();
    form.append("detalle_id", id);
    swal({
      title: "¿Está seguro que desea eliminar este detalle?",
      text: "Se eliminará por completo el detalle",
      icon: "warning",
      buttons: true,
      dangerMode: true,
    }).then((willDelete) => {
      if (willDelete) {
        fetch("../controllers/detalle/detalleEliminarController.php", {
          method: "POST",
          body: form,
        })
          .then((res) => res.json())
          .then((data) => {
            if (data === 1) {
              swal("Seha eliminado el detalle", {
                icon: "success",
              });
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
app.listado();
