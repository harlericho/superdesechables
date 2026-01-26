const app = new (function () {
  this.categorias = document.getElementById("tbody");
  this.descripcion = document.getElementsByName("descripcion")[0];

  this.listado = () => {
    fetch("../controllers/categoria/categoriaListadoController.php", {
      method: "GET",
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.length > 0) {
          html = [];
          data.forEach((element) => {
            html += "<tr>";
            // mostra el id de la categoria
            html += "<td>" + element.categoria_id + "</td>";
            html +=
              "<td><strong>" + element.categoria_descripcion + "</strong></td>";
            if (element.categoria_estado == "1") {
              html += "<td><span class='badge bg-green'>Activo</span></td>";
            } else {
              html +=
                "<td><span class='badge bg-red' onClick='app.activar(" +
                element.categoria_id +
                "," +
                element.categoria_estado +
                ")'>Inactivo</span></td>";
            }
            html +=
              "<td><button type='button' class='btn btn-danger btn-sm' title='ELiminar' onClick='app.eliminar(" +
              element.categoria_id +
              "," +
              element.categoria_estado +
              ")'><i class= 'fa fa-trash'></i></button></td>";
            html += "</tr>";
          });
          this.categorias.innerHTML = html;
        } else {
          this.categorias.innerHTML =
            "<tr><td colspan='3'>No hay categorias registradas</td></tr>";
        }
      })
      .catch((err) => console.log(err));
  };
  this.guardar = () => {
    var form = new FormData();
    form.append("descripcion", this.descripcion.value.toUpperCase());
    fetch("../controllers/categoria/categoriaGuardarController.php", {
      method: "POST",
      body: form,
    })
      .then((res) => res.json())
      .then((data) => {
        if (data === 1) {
          swal({
            title: "Registro exitoso",
            text: "Categoria registrada exitosamente",
            icon: "success",
            button: "Aceptar",
          });
          this.limpiar();
          this.listado();
        } else if (data === 2) {
          swal({
            title: "Error",
            text: "La categoria ya existe",
            icon: "error",
            button: "Aceptar",
          });
          this.descripcion.focus();
        }
      })
      .catch((err) => console.log(err));
  };
  this.limpiar = () => {
    this.descripcion.value = "";
    this.descripcion.focus();
  };
  this.eliminar = (id, estado) => {
    var form = new FormData();
    form.append("id", id);
    swal({
      title: "¿Está seguro de modificar el estado de la categoria?",
      text: "Solo cambiará el estado del categoria",
      icon: "warning",
      buttons: true,
      dangerMode: true,
    }).then((willDelete) => {
      if (willDelete) {
        if (estado !== 0) {
          fetch("../controllers/categoria/categoriaInactivarController.php", {
            method: "POST",
            body: form,
          })
            .then((res) => res.json())
            .then((data) => {
              if (data === 1) {
                swal("Categoria ha cambiado su estado: inactivo", {
                  icon: "success",
                });
                this.listado();
              }
            })
            .catch((err) => console.log(err));
        } else {
          swal({
            title: "Eliminación fallida",
            text: "Categoria ya esta con estado inactivo",
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
      fetch("../controllers/categoria/categoriaActivarController.php", {
        method: "POST",
        body: form,
      })
        .then((res) => res.json())
        .then((data) => {
          if (data === 1) {
            swal({
              title: "Activación exitosa",
              text: "Categoria ha cambiado su estado: activo",
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
        text: "Categoria ya esta con estado activo",
        icon: "warning",
        button: "Aceptar",
      });
    }
  };
})();
app.listado();
