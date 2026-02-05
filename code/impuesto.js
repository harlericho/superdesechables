const app = new (function () {
  this.impuestos = document.getElementById("tbody");
  this.nombre = document.getElementsByName("nombre")[0];
  this.porcentaje = document.getElementsByName("porcentaje")[0];
  this.activo = document.getElementsByName("activo")[0];
  this.id = document.getElementsByName("id")[0];
  this.btnGuardar = document.getElementById("btnGuardar");
  this.btnTexto = document.getElementById("btnTexto");
  this.btnNuevo = document.getElementById("btnNuevo");
  this.form = document.getElementById("formImpuesto");

  this.listado = () => {
    fetch("../controllers/impuesto/impuestoListadoController.php", {
      method: "GET",
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.length > 0) {
          let html = "";
          data.forEach((element) => {
            html += "<tr>";
            html += "<td>" + element.impuesto_id + "</td>";
            html += "<td><strong>" + element.impuesto_nombre + "</strong></td>";
            html +=
              "<td><span class='label label-info'>" +
              element.impuesto_porcentaje +
              "%</span></td>";

            // Estado (activo/eliminado)
            if (element.impuesto_estado == "1") {
              html +=
                "<td><span class='label label-success'>Activo</span></td>";
            } else {
              html +=
                "<td><span class='label label-danger'>Eliminado</span></td>";
            }

            // Activo (Si es el impuesto en uso)
            if (element.impuesto_activo == "1") {
              html +=
                "<td><span class='label label-primary'><i class='fa fa-check'></i> EN USO</span></td>";
            } else {
              html +=
                "<td><button type='button' class='btn btn-xs btn-default' onClick='app.activar(" +
                element.impuesto_id +
                ")' title='Activar este impuesto'><i class='fa fa-toggle-off'></i> Activar</button></td>";
            }

            // Fecha
            const fecha = new Date(
              element.impuesto_created_date,
            ).toLocaleDateString("es-ES");
            html += "<td><small>" + fecha + "</small></td>";

            // Acciones
            html += "<td>";
            if (element.impuesto_estado == "1") {
              html +=
                "<button type='button' class='btn btn-warning btn-xs' title='Editar' onClick='app.editar(" +
                element.impuesto_id +
                ")'><i class='fa fa-edit'></i></button> ";
              html +=
                "<button type='button' class='btn btn-danger btn-xs' title='Eliminar' onClick='app.eliminar(" +
                element.impuesto_id +
                ")'><i class='fa fa-trash'></i></button>";
            } else {
              html +=
                "<button type='button' class='btn btn-success btn-xs' title='Restaurar impuesto' onClick='app.restaurar(" +
                element.impuesto_id +
                ")'><i class='fa fa-undo'></i> Restaurar</button>";
            }
            html += "</td>";
            html += "</tr>";
          });
          this.impuestos.innerHTML = html;
        } else {
          this.impuestos.innerHTML =
            "<tr><td colspan='7'>No hay impuestos registrados</td></tr>";
        }
      })
      .catch((err) => {
        console.error("Error al cargar impuestos:", err);
        this.impuestos.innerHTML =
          "<tr><td colspan='7' class='text-red'>Error al cargar datos</td></tr>";
      });
  };

  this.guardar = () => {
    // Validaciones
    if (this.nombre.value.trim() === "") {
      swal("Advertencia", "El nombre del impuesto es obligatorio", "warning");
      this.nombre.focus();
      return;
    }

    if (this.porcentaje.value === "") {
      swal(
        "Advertencia",
        "El porcentaje del impuesto es obligatorio",
        "warning",
      );
      this.porcentaje.focus();
      return;
    }

    const form = new FormData();
    form.append("nombre", this.nombre.value.toUpperCase());
    form.append("porcentaje", this.porcentaje.value);
    form.append("activo", this.activo.checked ? 1 : 0);

    let url = "../controllers/impuesto/impuestoGuardarController.php";
    let mensaje = "Impuesto guardado correctamente";

    // Si estamos editando
    if (this.id.value !== "") {
      form.append("id", this.id.value);
      url = "../controllers/impuesto/impuestoEditarController.php";
      mensaje = "Impuesto actualizado correctamente";
    }

    fetch(url, {
      method: "POST",
      body: form,
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.status === "success") {
          swal("¡Éxito!", mensaje, "success");
          this.limpiar();
          this.listado();

          // Si el impuesto fue marcado como activo, recargar impuesto en ventas
          if (this.activo.checked && typeof window.parent !== "undefined") {
            window.parent.postMessage("recargarImpuesto", "*");
          }
        } else {
          swal(
            "Error",
            data.message || "Error al procesar la solicitud",
            "error",
          );
        }
      })
      .catch((err) => {
        console.error("Error:", err);
        swal("Error", "Error de conexión al servidor", "error");
      });
  };

  this.editar = (id) => {
    const form = new FormData();
    form.append("id", id);

    fetch("../controllers/impuesto/impuestoObtenerController.php", {
      method: "POST",
      body: form,
    })
      .then((res) => res.json())
      .then((data) => {
        if (data) {
          this.id.value = data.impuesto_id;
          this.nombre.value = data.impuesto_nombre;
          this.porcentaje.value = data.impuesto_porcentaje;
          this.activo.checked = data.impuesto_activo == 1;

          // Cambiar UI para modo edición
          this.btnTexto.textContent = "Actualizar";
          this.btnGuardar.className = "btn btn-warning";
          this.btnNuevo.style.display = "inline-block";

          this.nombre.focus();
        } else {
          swal(
            "Error",
            "No se pudo cargar la información del impuesto",
            "error",
          );
        }
      })
      .catch((err) => {
        console.error("Error:", err);
        swal("Error", "Error al cargar datos", "error");
      });
  };

  this.activar = (id) => {
    swal({
      title: "¿Activar este impuesto?",
      text: "Este será el impuesto utilizado en todas las ventas. Los demás se desactivarán automáticamente.",
      icon: "warning",
      buttons: {
        cancel: "No, cancelar",
        confirm: "Sí, activar",
      },
    }).then(function (result) {
      if (result) {
        const form = new FormData();
        form.append("impuesto_id", id);

        fetch("../controllers/impuesto/impuestoActivarController.php", {
          method: "POST",
          body: form,
        })
          .then((res) => res.json())
          .then((data) => {
            if (data.status === "success") {
              swal("¡Éxito!", "Impuesto activado correctamente", "success");
              app.listado();
            } else {
              swal(
                "Error",
                data.message || "Error al activar impuesto",
                "error",
              );
            }
          })
          .catch((err) => {
            console.error("Error:", err);
            swal("Error", "Error de conexión al servidor", "error");
          });
      }
    });
  };

  this.eliminar = (id) => {
    swal({
      title: "¿Estás seguro?",
      text: "Esta acción eliminará el impuesto permanentemente",
      icon: "warning",
      buttons: {
        cancel: "No, cancelar",
        confirm: "Sí, eliminar",
      },
      dangerMode: true,
    }).then(function (result) {
      if (result) {
        const form = new FormData();
        form.append("id", id);

        fetch("../controllers/impuesto/impuestoEliminarController.php", {
          method: "POST",
          body: form,
        })
          .then((res) => res.json())
          .then((data) => {
            if (data.status === "success") {
              swal(
                "¡Eliminado!",
                "Impuesto eliminado correctamente",
                "success",
              );
              app.listado();
            } else {
              swal(
                "Error",
                data.message || "Error al eliminar impuesto",
                "error",
              );
            }
          })
          .catch((err) => {
            console.error("Error:", err);
            swal("Error", "Error de conexión al servidor", "error");
          });
      }
    });
  };

  this.restaurar = (id) => {
    swal({
      title: "¿Restaurar este impuesto?",
      text: "El impuesto volverá a estar disponible para su uso",
      icon: "info",
      buttons: {
        cancel: "No, cancelar",
        confirm: "Sí, restaurar",
      },
    }).then(function (result) {
      if (result) {
        const form = new FormData();
        form.append("impuesto_id", id);

        fetch("../controllers/impuesto/impuestoRestaurarController.php", {
          method: "POST",
          body: form,
        })
          .then((res) => res.json())
          .then((data) => {
            if (data.status === "success") {
              swal(
                "¡Restaurado!",
                "Impuesto restaurado correctamente",
                "success",
              );
              app.listado();
            } else {
              swal(
                "Error",
                data.message || "Error al restaurar impuesto",
                "error",
              );
            }
          })
          .catch((err) => {
            console.error("Error:", err);
            swal("Error", "Error de conexión al servidor", "error");
          });
      }
    });
  };

  this.limpiar = () => {
    this.form.reset();
    this.id.value = "";

    // Restaurar UI para modo agregar
    this.btnTexto.textContent = "Guardar";
    this.btnGuardar.className = "btn btn-primary";
    this.btnNuevo.style.display = "inline-block";

    this.nombre.focus();
  };
})();

// Inicializar la aplicación
app.listado();

// Escuchar mensajes de otras ventanas para recargar datos
window.addEventListener("message", function (event) {
  if (event.data === "recargarImpuestos") {
    app.listado();
  }
});
