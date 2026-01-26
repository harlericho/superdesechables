const app = new (function () {
  this.series = document.getElementById("tbody");
  this.id = document.getElementsByName("id")[0];
  this.primera_serie = document.getElementsByName("primera_serie")[0];
  this.segunda_serie = document.getElementsByName("segunda_serie")[0];
  this.secuencial = document.getElementsByName("secuencial")[0];
  this.listado = () => {
    fetch("../controllers/configserie/configserieListadoController.php", {
      method: "GET",
    })
      .then((res) => res.json())
      .then((data) => {
        if (data !== null) {
          let html =
            "<table class='table table-bordered text-center' id='example1'>";
          html += "<thead>";
          html += "<tr>";
          html +=
            "<th>Primera serie</th><th>Segunda serie</th><th>Secuencial</th><th style='width: 40px' colpsan='2'>Accciones</th>";
          html += "</tr>";
          html += "</thead>";
          html += "<tbody>";
          data.forEach((element) => {
            html += "<tr>";
            html +=
              "<td> <strong>" + element.config_primera_serie + "</strong></td>";
            html += "<td> <strong>" + element.config_segunda_serie + "</td>";
            html += "<td> <strong>" + element.config_secuencial + "</td>";
            html +=
              "<td><button type='button' class='btn btn-info btn-sm' title='Editar' onClick='app.editar(" +
              element.config_id +
              ")'><i class= 'fa fa-pencil'></i></button></td>";
            // html += "</tbody>"
          });
          html += "</tr></tbody></table>";
          this.series.innerHTML = html;
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
    const form = new FormData(document.getElementById("formConfigSerie"));
    if (this.id.value === "") {
      this.contar()
        .then((data) => {
          if (data < 0) {
            fetch(
              "../controllers/configserie/configserieGuardarController.php",
              {
                method: "POST",
                body: form,
              }
            )
              .then((res) => res.json())
              .then((data) => {
                if (data === 1) {
                  swal({
                    title: "Registro exitoso",
                    text: "Serie registrada exitosamente",
                    icon: "success",
                    button: "Aceptar",
                  });
                }
                this.limpiar();
                this.listado();
              })
              .catch((err) => console.log(err));
          } else {
            swal({
              title: "Atención!",
              text: "Ya existe una serie registrada",
              icon: "warning",
              button: "Aceptar",
            });
          }
        })
        .catch((err) => {
          console.error(err);
        });
    } else {
      fetch("../controllers/configserie/configserieActualizarController.php", {
        method: "POST",
        body: form,
      })
        .then((res) => res.json())
        .then((data) => {
          if (data === 1) {
            swal({
              title: "Actualización exitosa",
              text: "Serie actualizada exitosamente",
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
    $("#formConfigSerie")[0].reset();
    this.id.value = "";
  };
  this.editar = (id) => {
    const form = new FormData();
    form.append("id", id);
    fetch("../controllers/configserie/configserieEditarController.php", {
      method: "POST",
      body: form,
    })
      .then((res) => res.json())
      .then((data) => {
        this.primera_serie.value = data[0]["primera_serie"];
        this.segunda_serie.value = data[0]["segunda_serie"];
        this.secuencial.value = data[0]["secuencial"];
        this.id.value = data[0]["id"];
        swal("Atención!", "Esta en el modo actualizar datos", "warning");
      })
      .catch((err) => console.log(err));
  };
  this.contar = () => {
    return new Promise((resolve, reject) => {
      fetch("../controllers/configserie/configserieContarController.php", {
        method: "GET",
      })
        .then((res) => res.json())
        .then((data) => {
          resolve(data); // Resolvemos la promesa con el valor de data
        })
        .catch((err) => reject(err)); // Rechazamos la promesa en caso de error
    });
  };
})();
app.listado();
