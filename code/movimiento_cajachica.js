const app = new (function () {
  this.movimientos = document.getElementById("tbody");
  this.id = document.getElementsByName("id")[0];
  let infoTbody = 0;
  this.listado = () => {
    fetch("../controllers/cajachica/mov_cajachicaListadoController.php", {
      method: "GET",
    })
      .then((res) => res.json())
      .then((data) => {
        infoTbody = data.length;
        if (data !== null) {
          let html =
            "<table class='table table-bordered text-center' id='example1'>";
          html += "<thead>";
          html += "<tr>";
          html +=
            "<th>Fecha Registro</th><th>Descripcion</th><th>Tipo</th><th>Monto</th>";
          html += "</tr>";
          html += "</thead>";
          html += "<tbody>";
          data.forEach((element) => {
            html += "<tr>";
            html +=
              "<td> <strong>" + element.mov_fecharegistro + "</strong></td>";
            html += "<td>" + element.mov_descripcion + "</td>";
            html +=
              "<td style='color: " +
              (element.mov_tipo === "INGRESO" ? "green" : "red") +
              ";'>" +
              element.mov_tipo +
              "</td>";
            html +=
              "<td style='text-align: right; color: " +
              (element.mov_tipo === "INGRESO" ? "green" : "red") +
              ";'> <strong>" +
              element.mov_monto +
              "</strong></td>";
          });
          html += "</tr></tbody></table>";
          this.movimientos.innerHTML = html;
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
          this.series.innerHTML =
            "<tr><td colspan='4'>No hay usuarios registrados</td></tr>";
        }
      })
      .catch((err) => console.log(err));
  };
  this.guardar = () => {
    const form = new FormData(
      document.getElementById("formMovimientoCajaChica")
    );
    if (this.id.value === "") {
      fetch("../controllers/cajachica/mov_cajachicaGuardarController.php", {
        method: "POST",
        body: form,
      })
        .then((res) => res.json())
        .then((data) => {
          if (data === 1) {
            swal({
              title: "Registro exitoso",
              text: "Movimiento caja chica registrado exitosamente",
              icon: "success",
              button: "Aceptar",
            });
          }
          this.limpiar();
          this.listado();
        })
        .catch((err) => console.log(err));
    }
  };
  this.limpiar = () => {
    $("#formMovimientoCajaChica")[0].reset();
    this.id.value = "";
  };
  this.generarPDF = () => {
    if (infoTbody > 0) {
      window.open(
        "../controllers/cajachica/mov_cajachicaPdfController.php",
        "_blank"
      );
    } else {
      swal({
        title: "Sin registros",
        text: "No hay movimientos de caja chica para mostrar en el PDF.",
        icon: "warning",
        button: "Aceptar",
      });
    }
  };
})();
app.listado();
