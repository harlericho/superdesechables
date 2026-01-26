$(document).ready(function () {
  // Inicializar Select2
  $("#productoSelect").select2({
    placeholder: "Buscar producto por código o nombre",
    allowClear: true,
  });

  // Cargar productos
  cargarProductos();

  // Variable para almacenar productos seleccionados
  let productosSeleccionados = [];

  function cargarProductos() {
    $.ajax({
      url: "../controllers/producto/productoListadoController.php",
      type: "POST",
      dataType: "json",
      success: function (response) {
        $("#productoSelect").empty();
        $("#productoSelect").append(
          '<option value="">Seleccione un producto</option>',
        );

        if (response && response.length > 0) {
          response.forEach(function (producto) {
            if (producto.producto_estado == "1") {
              $("#productoSelect").append(
                `<option value="${producto.producto_id}" 
                                    data-codigo="${producto.producto_codigo}" 
                                    data-nombre="${producto.producto_nombre}" 
                                    data-precio="${producto.producto_precio_venta}">
                                    ${producto.producto_codigo} - ${producto.producto_nombre} - $. ${producto.producto_precio_venta}
                                </option>`,
              );
            }
          });
        }
      },
      error: function () {
        swal("Error", "No se pudieron cargar los productos", "error");
      },
    });
  }

  // Agregar producto a la tabla
  $("#productoSelect").on("change", function () {
    let productoId = $(this).val();

    if (productoId) {
      let option = $(this).find("option:selected");
      let codigo = option.data("codigo");
      let nombre = option.data("nombre");
      let precio = option.data("precio");

      // Verificar si el producto ya está agregado
      let existe = productosSeleccionados.find((p) => p.id == productoId);

      if (existe) {
        swal(
          "Producto ya agregado",
          "Este producto ya está en la lista",
          "info",
        );
        return;
      }

      // Agregar producto al array
      productosSeleccionados.push({
        id: productoId,
        codigo: codigo,
        nombre: nombre,
        precio: precio,
      });

      // Actualizar tabla
      actualizarTabla();

      // Limpiar select
      $(this).val("").trigger("change");
    }
  });

  function actualizarTabla() {
    let html = "";

    productosSeleccionados.forEach(function (producto, index) {
      html += `
                <tr>
                    <td>${producto.codigo}</td>
                    <td>${producto.nombre}</td>
                    <td>$. ${producto.precio}</td>
                    <td>
                        <button type="button" 
                            class="btn btn-danger btn-sm btn-eliminar" 
                            data-index="${index}">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
    });

    $("#productosSeleccionados").html(html);
  }

  // Eliminar producto
  $(document).on("click", ".btn-eliminar", function () {
    let index = $(this).data("index");
    productosSeleccionados.splice(index, 1);
    actualizarTabla();
  });

  // Generar PDF
  $("#btnGenerarPDF").on("click", function () {
    if (productosSeleccionados.length === 0) {
      swal(
        "Sin productos",
        "Debe agregar al menos un producto para generar el PDF",
        "warning",
      );
      return;
    }

    // Mostrar loading
    swal({
      title: "Generando PDF...",
      text: "Por favor espere",
      icon: "info",
      button: false,
      closeOnClickOutside: false,
    });

    // Enviar datos al servidor
    $.ajax({
      url: "../controllers/producto/productoCodigoBarraPDFController.php",
      type: "POST",
      data: {
        productos: JSON.stringify(productosSeleccionados),
      },
      xhrFields: {
        responseType: "blob",
      },
      success: function (blob) {
        swal.close();

        // Crear URL del blob
        let url = window.URL.createObjectURL(blob);

        // Crear link de descarga
        let a = document.createElement("a");
        a.href = url;
        a.download = "codigos_barras_" + new Date().getTime() + ".pdf";
        document.body.appendChild(a);
        a.click();

        // Limpiar
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);

        swal(
          "Éxito",
          "El PDF se ha generado y descargado correctamente",
          "success",
        );
      },
      error: function () {
        swal.close();
        swal("Error", "No se pudo generar el PDF", "error");
      },
    });
  });
});
