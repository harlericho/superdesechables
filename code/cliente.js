const app = new (function () {
  this.clientes = document.getElementById("tbody");
  this.id = document.getElementsByName("id")[0];
  this.dni = document.getElementsByName("dni")[0];
  this.nombres = document.getElementsByName("nombres")[0];
  this.apellidos = document.getElementsByName("apellidos")[0];
  this.direccion = document.getElementsByName("direccion")[0];
  this.email = document.getElementsByName("email")[0];
  this.telefono = document.getElementsByName("telefono")[0];
  this.selectorRol = document.getElementById("selectorRol");

  this.listado = () => {
    fetch("../controllers/cliente/clienteListadoController.php", {
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
            "<th>Rol</th><th>Dni</th><th>Nombre(s)</th><th>Apellido(s)</th><th>Dirección</th><th>Email</th><th>Telefono</th><th>Estado</th><th style='width: 40px' colpsan='2'>Accciones</th>";
          html += "</tr>";
          html += "</thead>";
          html += "<tbody>";
          data.forEach((element) => {
            html += "<tr>";
            html +=
              "<td> <strong>" + element.rol_descripcion + "</strong></td>";
            html += "<td>" + element.cliente_dni + "</td>";
            html += "<td>" + element.cliente_nombres + "</td>";
            html += "<td>" + element.cliente_apellidos + "</td>";
            html += "<td>" + element.cliente_direccion + "</td>";
            html += "<td>" + element.cliente_email + "</td>";
            html += "<td> <b>" + element.cliente_telefono + "</b></td>";
            if (element.cliente_estado == "1") {
              html += "<td><span class='badge bg-green'>Activo</span></td>";
            } else {
              html +=
                "<td><span class='badge bg-red' onClick='app.activar(" +
                element.cliente_id +
                "," +
                element.cliente_estado +
                ")'>Inactivo</span></td>";
            }
            html +=
              "<td><button type='button' class='btn btn-danger btn-sm' style='margin-right: 5%;' title='Eliminar' onClick='app.eliminar(" +
              element.cliente_id +
              "," +
              element.cliente_estado +
              ")'><i class= 'fa fa-trash'></i></button><button type='button' class='btn btn-info btn-sm' title='Editar' onClick='app.editar(" +
              element.cliente_id +
              ")'><i class= 'fa fa-pencil'></i></button></td>";
          });
          html += "</tr></tbody></table>";
          this.clientes.innerHTML = html;
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
          this.clientes.innerHTML =
            "<tr><td colspan='9'>No hay clientes registrados</td></tr>";
        }
      })
      .catch((err) => console.log(err));
  };
  this.guardar = () => {
    var form = new FormData(document.getElementById("formCliente"));
    if (this.id.value === "") {
      if (form.get("rol") !== null) {
        fetch("../controllers/cliente/clienteGuardarController.php", {
          method: "POST",
          body: form,
        })
          .then((res) => res.json())
          .then((data) => {
            const code = data.code !== undefined ? data.code : data;
            if (code === 1 || (data.success && data.code === 1)) {
              swal({
                title: "Registro exitoso",
                text: "Cliente registrado exitosamente",
                icon: "success",
                button: "Aceptar",
              });
              this.limpiar();
              this.listado();
            } else if (code === 2) {
              swal({
                title: "Error",
                text: "El dni ya existe",
                icon: "error",
                button: "Aceptar",
              });
              this.dni.focus();
            } else if (code === 3) {
              swal({
                title: "Error",
                text: "El email ya existe",
                icon: "error",
                button: "Aceptar",
              });
              this.email.focus();
            } else if (code === 4) {
              swal({
                title: "Error",
                text: "El telefono ya existe",
                icon: "error",
                button: "Aceptar",
              });
              this.telefono.focus();
            }
          })
          .catch((err) => console.log(err));
      } else {
        swal({
          title: "Atención",
          text: "Elegir un rol",
          icon: "warning",
          button: "Aceptar",
        });
        return;
      }
    } else {
      fetch("../controllers/cliente/clienteActualizarController.php", {
        method: "POST",
        body: form,
      })
        .then((res) => res.json())
        .then((data) => {
          if (data === 1) {
            swal({
              title: "Actualización exitosa",
              text: "Cliente actualizado exitosamente",
              icon: "success",
              button: "Aceptar",
            });
            this.limpiar();
            this.listado();
          } else if (data === 2) {
            swal({
              title: "Error",
              text: "El dni ya existe",
              icon: "error",
              button: "Aceptar",
            });
            this.dni.focus();
          } else if (data === 3) {
            swal({
              title: "Error",
              text: "El email ya existe",
              icon: "error",
              button: "Aceptar",
            });
            this.email.focus();
          } else if (data === 4) {
            swal({
              title: "Error",
              text: "El telefono ya existe",
              icon: "error",
              button: "Aceptar",
            });
            this.telefono.focus();
          }
        })
        .catch((err) => console.log(err));
    }
  };
  this.rol = () => {
    fetch("../controllers/rol/rolListadoController.php", {
      method: "GET",
    })
      .then((res) => res.json())
      .then((data) => {
        html =
          "<select class='form-control select2' style='width: 100%;' name='rol' id='rol' autofocus required>";
        html +=
          "<option disabled='selected' selected='selected'>Seleccione</option>";
        data.forEach((element) => {
          if (element.rol_estado != "0") {
            if (
              element.rol_descripcion === "CLIENTE" ||
              element.rol_descripcion === "CLIENTES"
            ) {
              html +=
                "<option value='" +
                element.rol_id +
                "'>" +
                element.rol_descripcion +
                "</option>";
            } else {
              html +=
                "<option value='" +
                element.rol_id +
                "' disabled >" +
                element.rol_descripcion +
                "</option>";
            }
          }
        });
        html += "</select>";
        this.selectorRol.innerHTML = html;
        // todo : Inicializar variables select roles
        $(".select2").select2();
      })
      .catch((err) => console.log(err));
  };
  this.limpiar = () => {
    $("#formCliente")[0].reset();
    this.id.value = "";
    $("#rol").val("Seleccione").trigger("change");
  };
  this.eliminar = (id, estado) => {
    var form = new FormData();
    form.append("id", id);
    swal({
      title: "¿Está seguro de modificar el estado del cliente?",
      text: "Solo cambiará el estado del cliente",
      icon: "warning",
      buttons: true,
      dangerMode: true,
    }).then((willDelete) => {
      if (willDelete) {
        if (estado !== 0) {
          fetch("../controllers/cliente/clienteInactivarController.php", {
            method: "POST",
            body: form,
          })
            .then((res) => res.json())
            .then((data) => {
              if (data === 1) {
                swal("Cliente ha cambiado su estado: inactivo", {
                  icon: "success",
                });
                this.listado();
              }
            })
            .catch((err) => console.log(err));
        } else {
          swal({
            title: "Eliminación fallida",
            text: "CLiente ya esta con estado inactivo",
            icon: "error",
            button: "Aceptar",
          });
        }
      } else {
        swal("Operación cancelada");
        return false;
      }
    });
  };
  this.activar = (id, estado) => {
    var form = new FormData();
    form.append("id", id);
    if (estado !== 1) {
      fetch("../controllers/cliente/clienteActivarController.php", {
        method: "POST",
        body: form,
      })
        .then((res) => res.json())
        .then((data) => {
          if (data === 1) {
            swal({
              title: "Activación exitosa",
              text: "Cliente ha cambiado su estado: activo",
              icon: "success",
              button: "Aceptar",
            });
            this.listado();
          }
        })
        .catch((err) => console.log(err));
    } else {
      swal({
        title: "Activación fallida",
        text: "Cliente ya esta con estado activo",
        icon: "warning",
        button: "Aceptar",
      });
    }
  };
  this.editar = (id) => {
    var form = new FormData();
    form.append("id", id);
    fetch("../controllers/cliente/clienteEditarController.php", {
      method: "POST",
      body: form,
    })
      .then((res) => res.json())
      .then((data) => {
        this.id.value = data.cliente_id;
        this.dni.value = data.cliente_dni;
        this.nombres.value = data.cliente_nombres;
        this.apellidos.value = data.cliente_apellidos;
        this.direccion.value = data.cliente_direccion;
        this.email.value = data.cliente_email;
        this.telefono.value = data.cliente_telefono;
        $("#rol").val(data["rol_id"]).trigger("change");
        swal("Atención!", "Esta en el modo actualizar datos", "warning");
      })
      .catch((err) => console.log(err));
  };
  this.exportarCSV = function () {
    // Busca la tabla de productos
    let tabla = document.getElementById("example1");
    if (!tabla) {
      swal({
        title: "Error",
        text: "No hay datos para exportar.",
        icon: "error",
        button: "Aceptar",
      });
      return;
    }
    let csv = [];
    for (let i = 0; i < tabla.rows.length; i++) {
      let row = [],
        cols = tabla.rows[i].cells;
      let numCols = cols.length > 1 ? cols.length - 1 : cols.length;
      for (let j = 0; j < numCols; j++) {
        let text = cols[j].innerText.replace(/\n/g, " ").replace(/,/g, "");
        // Si es la columna de código (índice 2), conserva ceros a la izquierda
        if (i === 0) {
          // encabezado
          row.push('"' + text + '"');
        } else if (j === 2) {
          // Obtiene el valor original del código desde la celda HTML
          let codigo = cols[j].querySelector("strong")
            ? cols[j].querySelector("strong").textContent
            : text;
          row.push('"' + codigo + '"');
        } else {
          row.push('"' + text + '"');
        }
      }
      csv.push(row.join(","));
    }
    // Agrega BOM para conservar acentos y caracteres especiales
    let bom = "\uFEFF";
    let csvString = bom + csv.join("\n");
    let blob = new Blob([csvString], { type: "text/csv;charset=utf-8;" });
    let link = document.createElement("a");
    link.href = window.URL.createObjectURL(blob);
    // Fecha y hora actual en formato YYYYMMDD_HHMMSS
    let fecha = new Date();
    let yyyy = fecha.getFullYear();
    let mm = String(fecha.getMonth() + 1).padStart(2, "0");
    let dd = String(fecha.getDate()).padStart(2, "0");
    let hh = String(fecha.getHours()).padStart(2, "0");
    let min = String(fecha.getMinutes()).padStart(2, "0");
    let ss = String(fecha.getSeconds()).padStart(2, "0");
    let fechaActual = yyyy + mm + dd + "_" + hh + min + ss;
    link.download = "clientes" + fechaActual + ".csv";
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  };
})();
app.listado();
app.rol();
