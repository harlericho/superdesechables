const app = new (function () {
  this.proveedor = document.getElementById("tbody");
  this.dni = document.getElementsByName("dni")[0];
  this.nombres = document.getElementsByName("nombres")[0];
  this.email = document.getElementsByName("email")[0];
  this.telefono = document.getElementsByName("telefono")[0];

  this.listado = () => {
    fetch("../controllers/proveedor/proveedorListadoController.php", {
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
            "<th>#</th><th>Dni</th><th>Nombre(s)</th><th>Email</th><th>Telefono</th><th>Estado</th>";
          html += "</tr>";
          html += "</thead>";
          html += "<tbody>";
          data.forEach((element) => {
            html += "<tr>";
            html += "<td>" + element.proveedor_id + "</td>";
            html += "<td>" + element.proveedor_dni + "</td>";
            html += "<td>" + element.proveedor_nombres + "</td>";
            html += "<td>" + element.proveedor_email + "</td>";
            html += "<td>" + element.proveedor_telefono + "</td>";
            if (element.proveedor_estado == "1") {
              html += "<td><span class='badge bg-green'>Activo</span></td>";
            } else {
              html +=
                "<td><span class='badge bg-red' onClick='app.activar(" +
                element.tipo_comp_id +
                "," +
                element.tipo_comp_estado +
                ")'>Inactivo</span></td>";
            }
          });
          html += "</tr></tbody></table>";
          this.proveedor.innerHTML = html;
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
          this.proveedor.innerHTML =
            "<tr><td colspan='5'>No hay proveedores registrados</td></tr>";
        }
      })
      .catch((err) => console.log(err));
  };
  this.guardar = () => {
    var form = new FormData();
    form.append("dni", this.dni.value.toUpperCase());
    form.append("nombres", this.nombres.value.toUpperCase());
    form.append("email", this.email.value.toLowerCase());
    form.append("telefono", this.telefono.value);
    fetch("../controllers/proveedor/proveedorGuardarController.php", {
      method: "POST",
      body: form,
    })
      .then((res) => res.json())
      .then((data) => {
        if (data === 1) {
          swal({
            title: "Registro exitoso",
            text: "Proveedor registrado exitosamente",
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
  };
  this.limpiar = () => {
    this.dni.value = "";
    this.nombres.value = "";
    this.email.value = "";
    this.telefono.value = "";
    this.dni.focus();
  };
})();
app.listado();
