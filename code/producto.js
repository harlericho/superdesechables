const app = new (function () {
  this.productos = document.getElementById("tbody");
  this.selectorProveedor = document.getElementById("selectorProveedor");
  this.selectorCategoria = document.getElementById("selectorCategoria");
  this.id = document.getElementsByName("id")[0];
  this.codigo = document.getElementsByName("codigo")[0];
  this.nombre = document.getElementsByName("nombre")[0];
  this.descripcion = document.getElementsByName("descripcion")[0];
  this.precio_c = document.getElementsByName("precio_c")[0];
  this.precio_v = document.getElementsByName("precio_v")[0];
  this.stock = document.getElementsByName("stock")[0];
  this.imagen = document.getElementById("imagenCargada");

  this.listado = () => {
    fetch("../controllers/producto/productoListadoController.php", {
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
            "<th>Proveedor</th><th>Categoria</th><th>Codigo</th><th>Imagen</th><th>Nombre</th><th>Descripcion</th><th>Precio Venta</th><th>Stock</th><th>Estado</th><th style='width: 40px' colpsan='2'>Accciones</th>";
          html += "</tr>";
          html += "</thead>";
          html += "<tbody>";
          data.forEach((element) => {
            html += "<tr>";
            html +=
              "<td> <strong>" + element.proveedor_nombres + "</strong></td>";
            html +=
              "<td> <strong>" +
              element.categoria_descripcion +
              "</strong></td>";
            html +=
              "<td> <strong>" + element.producto_codigo + "</strong></td>";
            html +=
              "<td><img src='../assets/uploads/" +
              element.producto_imagen +
              "' width='50' height='50'></td>";
            html += "<td>" + element.producto_nombre + "</td>";
            html += "<td>" + element.producto_descripcion + "</td>";
            html +=
              "<td> <b style='color:blue'>" +
              element.producto_precio_venta +
              "</b></td>";
            if (element.producto_stock <= 5) {
              html +=
                "<td><span class='badge bg-red'>" +
                element.producto_stock +
                "</span></td>";
            } else if (element.producto_stock <= 15) {
              html +=
                "<td><span class='badge bg-yellow'>" +
                element.producto_stock +
                "</span></td>";
            } else {
              html +=
                "<td><span class='badge bg-green'>" +
                element.producto_stock +
                "</span></td>";
            }
            if (element.producto_estado == "1") {
              html += "<td><span class='badge bg-green'>Activo</span></td>";
            } else {
              html +=
                "<td><span class='badge bg-red' onClick='app.activar(" +
                element.producto_id +
                "," +
                element.producto_estado +
                ")'>Inactivo</span></td>";
            }
            html +=
              "<td><button type='button' class='btn btn-danger btn-sm' style='margin-right: 5%;' title='Eliminar' onClick='app.eliminar(" +
              element.producto_id +
              "," +
              element.producto_estado +
              ")'><i class= 'fa fa-trash'></i></button><button type='button' class='btn btn-info btn-sm' title='Editar' onClick='app.editar(" +
              element.producto_id +
              ")'><i class= 'fa fa-pencil'></i></button></td>";
            // html += "</tbody>"
          });
          html += "</tr></tbody></table>";
          this.productos.innerHTML = html;
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
          app.productos.innerHTML =
            "<tr><td colspan='7'>No hay productos registrados</td></tr>";
        }
      })
      .catch((err) => console.log(err));
  };
  this.categorias = () => {
    fetch("../controllers/categoria/categoriaListadoController.php", {
      method: "GET",
    })
      .then((res) => res.json())
      .then((data) => {
        html =
          "<select class='form-control select2' style='width: 100%;' name='categoria' id='categoria' autofocus required>";
        html +=
          "<option disabled='selected' selected='selected'>Seleccione</option>";
        data.forEach((element) => {
          if (element.categoria_estado != "0") {
            html +=
              "<option value='" +
              element.categoria_id +
              "'>" +
              element.categoria_descripcion +
              "</option>";
          }
        });
        html += "</select>";
        this.selectorCategoria.innerHTML = html;
        // todo : Inicializar variables select roles
        $(".select2").select2();
      })
      .catch((err) => console.log(err));
  };
  this.proveedores = () => {
    fetch("../controllers/proveedor/proveedorListadoController.php", {
      method: "GET",
    })
      .then((res) => res.json())
      .then((data) => {
        html =
          "<select class='form-control select2' style='width: 100%;' name='proveedor' id='proveedor' autofocus required>";
        html +=
          "<option disabled='selected' selected='selected'>Seleccione</option>";
        data.forEach((element) => {
          if (element.proveedor_estado != "0") {
            html +=
              "<option value='" +
              element.proveedor_id +
              "'>" +
              element.proveedor_dni +
              " - " +
              element.proveedor_nombres +
              "</option>";
          }
        });
        html += "</select>";
        this.selectorProveedor.innerHTML = html;
        // todo : Inicializar variables select roles
        $(".select2").select2();
      })
      .catch((err) => console.log(err));
  };

  this.guardar = () => {
    var form = new FormData(document.getElementById("formProducto"));
    if (this.id.value === "") {
      if (form.get("proveedor") !== null) {
        if (form.get("categoria") !== null) {
          fetch("../controllers/producto/productoGuardarController.php", {
            method: "POST",
            body: form,
          })
            .then((res) => res.json())
            .then((data) => {
              if (data === 1) {
                swal({
                  title: "Registro exitoso",
                  text: "Producto registrado exitosamente",
                  icon: "success",
                  button: "Aceptar",
                });
                this.limpiar();
                this.listado();
              } else if (data === 2) {
                swal({
                  title: "Error",
                  text: "El código ya existe",
                  icon: "error",
                  button: "Aceptar",
                });
                this.codigo.focus();
              }
            })
            .catch((err) => console.log(err));
        } else {
          swal({
            title: "Atención",
            text: "Elegir una categoría",
            icon: "warning",
            button: "Aceptar",
          });
          return;
        }
      } else {
        swal({
          title: "Atención",
          text: "Elegir un proveedor",
          icon: "warning",
          button: "Aceptar",
        });
        return;
      }
    } else {
      fetch("../controllers/producto/productoActualizarController.php", {
        method: "POST",
        body: form,
      })
        .then((res) => res.json())
        .then((data) => {
          if (data === 1) {
            swal({
              title: "Actualizacíon exitosa",
              text: "Producto actualizado exitosamente",
              icon: "success",
              button: "Aceptar",
            });
            this.limpiar();
            this.listado();
          } else if (data === 2) {
            swal({
              title: "Error",
              text: "El código ya existe",
              icon: "error",
              button: "Aceptar",
            });
            this.codigo.focus();
          }
        })
        .catch((err) => console.log(err));
    }
  };
  this.editar = (id) => {
    var form = new FormData();
    form.append("id", id);
    fetch("../controllers/producto/productoEditarController.php", {
      method: "POST",
      body: form,
    })
      .then((res) => res.json())
      .then((data) => {
        this.id.value = data.producto_id;
        this.codigo.value = data.producto_codigo;
        this.nombre.value = data.producto_nombre;
        this.descripcion.value = data.producto_descripcion;
        this.precio_c.value = data.producto_precio_compra;
        this.precio_v.value = data.producto_precio_venta;
        this.stock.value = data.producto_stock;
        this.imagen.innerHTML =
          "<img src='../assets/uploads/" +
          data.producto_imagen +
          "' class='img-thumbnail' width='50'>";
        $("#categoria").val(data["categoria_id"]).trigger("change");
        $("#proveedor").val(data["proveedor_id"]).trigger("change");
        swal("Atención!", "Esta en el modo actualizar datos", "warning");
      })
      .catch((err) => console.log(err));
  };
  this.eliminar = (id, estado) => {
    var form = new FormData();
    form.append("id", id);
    swal({
      title: "¿Está seguro de modificar el estado del producto?",
      text: "Solo cambiará el estado del producto",
      icon: "warning",
      buttons: true,
      dangerMode: true,
    }).then((willDelete) => {
      if (willDelete) {
        if (estado !== 0) {
          fetch("../controllers/producto/productoInactivarController.php", {
            method: "POST",
            body: form,
          })
            .then((res) => res.json())
            .then((data) => {
              if (data === 1) {
                swal("Producto ha cambiado su estado: inactivo", {
                  icon: "success",
                });
                this.listado();
              }
            })
            .catch((err) => console.log(err));
        } else {
          swal({
            title: "Eliminación fallida",
            text: "Producto ya esta con estado inactivo",
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
      fetch("../controllers/producto/productoActivarController.php", {
        method: "POST",
        body: form,
      })
        .then((res) => res.json())
        .then((data) => {
          if (data === 1) {
            swal({
              title: "Activación exitosa",
              text: "Producto ha cambiado su estado: activo",
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
        text: "Producto ya esta con estado activo",
        icon: "warning",
        button: "Aceptar",
      });
    }
  };
  this.limpiar = () => {
    $("#formProducto")[0].reset();
    $("#proveedor").val("Seleccione").trigger("change");
    $("#categoria").val("Seleccione").trigger("change");
    this.id.value = "";
    this.imagen.innerHTML = "";
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
    let csvString = csv.join("\n");
    let blob = new Blob([csvString], { type: "text/csv" });
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
    link.download = "productos_" + fechaActual + ".csv";
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  };
})();
app.listado();
app.categorias();
app.proveedores();
