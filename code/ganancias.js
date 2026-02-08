$(document).ready(function () {
  // Inicializar componentes
  inicializarGanancias();
  cargarProductosParaFiltro();
  cargarGanancias();
  cargarResumenGanancias();

  // Forzar conversión de S/ a $ después de cargar todo
  setTimeout(function () {
    forzarConversionASolesADolares();
  }, 2000);

  // Inicializar gráfico si estamos en la pestaña de período al cargar
  setTimeout(function () {
    if ($("#tab-periodo").hasClass("active")) {
      if (typeof Chart !== "undefined") {
        cargarGananciasPorPeriodo();
      } else {
        setTimeout(function () {
          if (typeof Chart !== "undefined") {
            cargarGananciasPorPeriodo();
          }
        }, 2000);
      }
    }
  }, 1500);

  // Event handlers para filtros
  $("#btnFiltrar").on("click", function () {
    mostrarAlerta("Aplicando filtros...", "success");
    setTimeout(function () {
      cargarGanancias();
      cargarResumenGanancias();
    }, 300);
  });

  $("#btnLimpiarFiltros").on("click", function (e) {
    e.preventDefault();
    limpiarFiltros();
  });

  $("#btnExportarPDF").on("click", function () {
    mostrarAlerta("Generando reporte PDF...", "info");
    exportarGananciasPDF();
  });

  $("#btnExportarExcel").on("click", function () {
    mostrarAlerta("Generando reporte Excel...", "warning");
    exportarGananciasExcel();
  });

  // Event handler para filtro automático por producto
  $(document).on("change", "#selectProducto", function () {
    var valorSeleccionado = $(this).val();
    if (valorSeleccionado !== "") {
      mostrarAlerta(
        "Filtrando por producto: " + $(this).find("option:selected").text(),
        "info",
      );
      setTimeout(function () {
        cargarGanancias();
        cargarResumenGanancias();
      }, 300);
    }
  });

  // Event handler para cambio de pestañas
  $('a[data-toggle="tab"]').on("shown.bs.tab", function (e) {
    var target = $(e.target).attr("href");

    if (target === "#tab-periodo") {
      // Esperar un poco más para asegurar que todo esté cargado
      setTimeout(function () {
        // Verificar que el canvas esté visible y Chart.js esté cargado
        var canvas = $("#graficoGanancias");
        if (
          canvas.is(":visible") &&
          canvas.length > 0 &&
          typeof Chart !== "undefined"
        ) {
          cargarGananciasPorPeriodo();
        } else {
          setTimeout(function () {
            if (typeof Chart !== "undefined") {
              cargarGananciasPorPeriodo();
            } else {
              console.error("Chart.js no se pudo cargar");
            }
          }, 1000);
        }
      }, 300);
    } else if (target === "#tab-productos") {
      cargarGanancias();
    } else if (target === "#tab-resumen") {
      cargarResumenGanancias();
    }
  });

  $("#selectPeriodo").on("change", function () {
    setTimeout(function () {
      cargarGananciasPorPeriodo();
    }, 100);
  });
});

function inicializarGanancias() {
  // NO configurar fechas por defecto para mostrar todos los datos inicialmente
  // El usuario puede filtrar por fechas específicas si lo desea
  $("#fechaDesde").val("");
  $("#fechaHasta").val("");

  // Limpiar DataTable existente si existe
  if ($.fn.DataTable.isDataTable("#tablaGanancias")) {
    $("#tablaGanancias").DataTable().destroy();
  }

  // Inicializar DataTable
  $("#tablaGanancias").DataTable({
    language: {
      search: "Buscar:",
      lengthMenu: "Mostrar _MENU_ entradas",
      info: "Mostrando _START_ a _END_ de _TOTAL_ entradas",
      infoEmpty: "Mostrando 0 a 0 de 0 entradas",
      infoFiltered: "(filtrado de _MAX_ entradas totales)",
      loadingRecords: "Cargando...",
      processing: "Procesando...",
      emptyTable: "No hay datos disponibles en la tabla",
      paginate: {
        first: "Primero",
        last: "Último",
        next: "Siguiente",
        previous: "Anterior",
      },
    },
    responsive: true,
    autoWidth: false,
    order: [[10, "desc"]], // Ordenar por ganancia neta descendente (columna 10)
    columnDefs: [
      {
        targets: [3, 4, 5, 6, 7, 8, 9, 10, 11],
        className: "text-right",
      },
      {
        targets: [4, 5, 6, 7, 8, 9, 10],
        render: function (data, type, row) {
          if (type === "display") {
            return formatearMoneda(data);
          }
          return data;
        },
      },
    ],
  });
}

function cargarProductosParaFiltro() {
  $.ajax({
    url: "../controllers/ganancias/gananciasProductosController.php",
    type: "POST",
    dataType: "json",
    success: function (data) {
      var select = $("#selectProducto");
      select.empty();
      select.append('<option value="">Todos los productos</option>');

      $.each(data, function (index, producto) {
        select.append(
          '<option value="' +
            producto.producto_codigo +
            '">' +
            producto.producto_codigo +
            " - " +
            producto.producto_nombre +
            "</option>",
        );
      });

      // Inicializar Select2 después de cargar los productos
      $("#selectProducto").select2({
        placeholder: "Busca un producto específico...",
        allowClear: true,
        language: {
          noResults: function () {
            return "No se encontraron productos";
          },
          searching: function () {
            return "Buscando...";
          },
        },
        width: "100%",
      });
    },
    error: function (xhr, status, error) {
      mostrarAlerta("Error al cargar productos para filtro", "error");
    },
  });
}

function cargarGanancias() {
  var fechaDesde = $("#fechaDesde").val();
  var fechaHasta = $("#fechaHasta").val();
  var codigoProducto = $("#selectProducto").val();

  // Solo aplicar filtros de fecha si ambos campos están llenos
  if (fechaDesde && !fechaHasta) {
    mostrarAlerta("Por favor seleccione también la fecha hasta", "error");
    return;
  }
  if (!fechaDesde && fechaHasta) {
    mostrarAlerta("Por favor seleccione también la fecha desde", "error");
    return;
  }

  $.ajax({
    url: "../controllers/ganancias/gananciasListadoController.php",
    type: "POST",
    data: {
      fecha_desde: fechaDesde,
      fecha_hasta: fechaHasta,
      codigo_producto: codigoProducto,
    },
    dataType: "json",
    success: function (data) {
      // Actualizar la tabla con los datos recibidos
      actualizarTablaGanancias(data);
      // Mostrar indicador de filtros
      mostrarEstadoFiltros();
    },
    error: function (xhr, status, error) {
      mostrarAlerta("Error al cargar datos de ganancias: " + error, "error");
    },
  });
}

function actualizarTablaGanancias(data) {
  // Verificar si DataTables está inicializada
  if (!$.fn.DataTable.isDataTable("#tablaGanancias")) {
    return;
  }

  var table = $("#tablaGanancias").DataTable();
  table.clear();

  if (!data || data.length === 0) {
    // Verificar si hay filtros aplicados
    var fechaDesde = $("#fechaDesde").val();
    var fechaHasta = $("#fechaHasta").val();
    var codigoProducto = $("#selectProducto").val();

    if (fechaDesde || fechaHasta || codigoProducto) {
      // Hay filtros aplicados, mostrar mensaje específico
      var mensaje = "No se encontraron datos para los filtros aplicados. ";
      if (fechaDesde && fechaHasta) {
        mensaje += "Período: " + fechaDesde + " al " + fechaHasta + ". ";
      }
      if (codigoProducto) {
        mensaje += "Producto: " + codigoProducto + ". ";
      }
      mensaje += "Intente ampliar el rango de fechas o quitar filtros.";
      mostrarAlerta(mensaje, "error");
    }
    table.draw();
    return;
  }

  $.each(data, function (index, item) {
    var margenGanancia = 0;
    if (parseFloat(item.total_ventas) > 0) {
      margenGanancia =
        (parseFloat(item.ganancia_neta) / parseFloat(item.total_ventas)) * 100;
    }

    table.row.add([
      item.producto_codigo || "",
      item.producto_nombre || "",
      item.categoria_descripcion || "",
      parseInt(item.cantidad_vendida) || 0,
      parseFloat(item.producto_precio_compra) || 0,
      parseFloat(item.producto_precio_venta) || 0,
      parseFloat(item.total_ventas) || 0,
      parseFloat(item.total_descuentos || 0),
      parseFloat(item.costo_total) || 0,
      parseFloat(item.ganancia_bruta) || 0,
      parseFloat(item.ganancia_neta) || 0,
      margenGanancia.toFixed(2) + "%",
    ]);
  });

  table.draw();

  // Forzar conversión después de actualizar la tabla
  setTimeout(function () {
    forzarConversionASolesADolares();
  }, 100);
}

function cargarResumenGanancias() {
  var fechaDesde = $("#fechaDesde").val();
  var fechaHasta = $("#fechaHasta").val();
  var codigoProducto = $("#selectProducto").val();

  $.ajax({
    url: "../controllers/ganancias/gananciasResumenController.php",
    type: "POST",
    data: {
      fecha_desde: fechaDesde,
      fecha_hasta: fechaHasta,
      codigo_producto: codigoProducto,
    },
    dataType: "json",
    success: function (data) {
      actualizarResumenGanancias(data);

      // Mostrar estado de filtros también al cargar resumen
      mostrarEstadoFiltros();
    },
    error: function (xhr, status, error) {},
  });
}

function actualizarResumenGanancias(data) {
  $("#totalProductos").text(data.total_productos || 0);
  $("#cantidadTotalVendida").text(data.cantidad_total_vendida || 0);
  $("#totalVentas").text(formatearMoneda(data.total_ventas_general || 0));
  $("#totalDescuentos").text(
    formatearMoneda(data.total_descuentos_general || 0),
  );
  $("#totalCostos").text(formatearMoneda(data.costo_total_general || 0));
  $("#gananciaBruta").text(formatearMoneda(data.ganancia_bruta_general || 0));
  $("#gananciaNeta").text(formatearMoneda(data.ganancia_neta_general || 0));

  // También actualizar el tab de resumen detallado
  $("#totalProductos2").text(data.total_productos || 0);
  $("#totalVentas2").text(formatearMoneda(data.total_ventas_general || 0));
  $("#totalCostos2").text(formatearMoneda(data.costo_total_general || 0));
  $("#gananciaNeta2").text(formatearMoneda(data.ganancia_neta_general || 0));

  // Calcular margen de ganancia
  var margen = 0;
  if (data.total_ventas_general > 0) {
    margen = (data.ganancia_neta_general / data.total_ventas_general) * 100;
  }
  $("#margenGanancia").text(margen.toFixed(2) + "%");

  // Forzar conversión de cualquier valor que aún tenga S/
  forzarConversionASolesADolares();
}

function cargarGananciasPorPeriodo() {
  var periodo = $("#selectPeriodo").val();
  var fechaDesde = $("#fechaDesde").val();
  var fechaHasta = $("#fechaHasta").val();

  $.ajax({
    url: "../controllers/ganancias/gananciasPeriodoController.php",
    type: "POST",
    data: {
      periodo: periodo,
      fecha_desde: fechaDesde,
      fecha_hasta: fechaHasta,
    },
    dataType: "json",
    success: function (data) {
      actualizarGraficoPeriodo(data);
      actualizarTablaPeriodo(data);
    },
    error: function (xhr, status, error) {
      // Mostrar error al usuario
      $("#tablaPeriodo tbody").html(
        '<tr><td colspan="6" class="text-center text-danger">Error al cargar datos: ' +
          error +
          "</td></tr>",
      );
    },
  });
}

function actualizarGraficoPeriodo(data) {
  var canvas = document.getElementById("graficoGanancias");
  if (!canvas) {
    return;
  }

  // Verificar que Chart.js esté disponible
  if (typeof Chart === "undefined") {
    setTimeout(function () {
      actualizarGraficoPeriodo(data);
    }, 1000);
    return;
  }

  // Destruir gráfico existente si existe
  if (
    window.graficoGanancias &&
    typeof window.graficoGanancias.destroy === "function"
  ) {
    window.graficoGanancias.destroy();
    window.graficoGanancias = null;
  }

  // Verificar que hay datos
  if (!data || data.length === 0) {
    var ctx = canvas.getContext("2d");
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.font = "16px Arial";
    ctx.fillStyle = "#666";
    ctx.textAlign = "center";
    ctx.fillText(
      "No hay datos para mostrar",
      canvas.width / 2,
      canvas.height / 2,
    );
    return;
  }

  var labels = data.map((item) => item.periodo);
  var ganancias = data.map((item) => parseFloat(item.ganancia_neta || 0));
  var ventas = data.map((item) => parseFloat(item.total_ingresos || 0));

  try {
    var config = {
      type: "bar",
      data: {
        labels: labels,
        datasets: [
          {
            label: "Ganancia Neta",
            data: ganancias,
            backgroundColor: "rgba(40, 167, 69, 0.7)",
            borderColor: "rgba(40, 167, 69, 1)",
            borderWidth: 2,
            borderRadius: 4,
          },
          {
            label: "Total Ingresos",
            data: ventas,
            backgroundColor: "rgba(54, 162, 235, 0.7)",
            borderColor: "rgba(54, 162, 235, 1)",
            borderWidth: 2,
            borderRadius: 4,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
          mode: "index",
          intersect: false,
        },
        plugins: {
          title: {
            display: true,
            text: "Análisis de Ganancias por Período",
            font: {
              size: 16,
              weight: "bold",
            },
          },
          legend: {
            display: true,
            position: "top",
          },
        },
        scales: {
          x: {
            display: true,
            title: {
              display: true,
              text: "Período",
            },
          },
          y: {
            display: true,
            title: {
              display: true,
              text: "Monto ($)",
            },
            beginAtZero: true,
            ticks: {
              callback: function (value) {
                return "$ " + value.toFixed(2);
              },
            },
          },
        },
      },
    };

    window.graficoGanancias = new Chart(canvas, config);
  } catch (error) {
    // Mostrar mensaje de error en el canvas
    var ctx = canvas.getContext("2d");
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.font = "14px Arial";
    ctx.fillStyle = "#dc3545";
    ctx.textAlign = "center";
    ctx.fillText(
      "Error al cargar gráfico",
      canvas.width / 2,
      canvas.height / 2 - 10,
    );
    ctx.fillText(error.message, canvas.width / 2, canvas.height / 2 + 10);
  }
}

function actualizarTablaPeriodo(data) {
  var tbody = $("#tablaPeriodo tbody");
  tbody.empty();

  if (!data || data.length === 0) {
    tbody.append(
      '<tr><td colspan="6" class="text-center text-muted" style="padding: 2rem;"><i class="fas fa-info-circle"></i> No hay datos para el período seleccionado</td></tr>',
    );
    return;
  }

  $.each(data, function (index, item) {
    var margen = 0;
    if (item.total_ingresos > 0) {
      margen = (item.ganancia_neta / item.total_ingresos) * 100;
    }

    // Determinar color del badge según el margen
    var margenColor = "badge-danger";
    var margenIcon = "fa fa-arrow-down";
    if (margen >= 40) {
      margenColor = "badge-success";
      margenIcon = "fa fa-arrow-up";
    } else if (margen >= 20) {
      margenColor = "badge-warning";
      margenIcon = "fa fa-minus";
    }

    tbody.append(`
            <tr class="table-row-hover">
                <td class="text-center">
                  <strong style="color: #495057;">${item.periodo}</strong>
                </td>
                <td class="text-center">
                  <span class="badge badge-primary" style="font-size: 1em; padding: 0.5em 0.7em;">
                    <i class="fa fa-shopping-cart"></i> ${item.total_ventas}
                  </span>
                </td>
                <td class="text-center">
                  <span class="badge badge-info" style="font-size: 1em; padding: 0.5em 0.7em;">
                    <i class="fa fa-boxes"></i> ${item.cantidad_vendida}
                  </span>
                </td>
                <td class="text-center valor-monetario">
                  <strong>${formatearMoneda(item.total_ingresos)}</strong>
                </td>
                <td class="text-center valor-monetario">
                  <strong>${formatearMoneda(item.ganancia_neta)}</strong>
                </td>
                <td class="text-center porcentaje">
                  <span class="badge ${margenColor}" style="font-size: 1em; padding: 0.5em 0.7em;">
                    <i class="${margenIcon}"></i> ${margen.toFixed(1)}%
                  </span>
                </td>
            </tr>
        `);
  });

  // Forzar conversión después de actualizar la tabla
  setTimeout(function () {
    forzarConversionASolesADolares();
  }, 100);
}

function limpiarFiltros() {
  // Verificar que el contenedor de alertas existe
  if ($("#alertas").length === 0) {
    return;
  }

  // Limpiar campos inmediatamente
  $("#fechaDesde").val("");
  $("#fechaHasta").val("");
  $("#selectProducto").val("").trigger("change"); // Limpiar Select2 correctamente

  // Mostrar mensaje inmediatamente
  mostrarAlerta("Limpiando filtros y cargando todos los datos...", "info");
  // Ejecutar recarga de datos
  setTimeout(function () {
    cargarGanancias();
    cargarResumenGanancias();
    mostrarEstadoFiltros();

    // Confirmar que se completó
    setTimeout(function () {
      mostrarAlerta(
        "Filtros limpiados correctamente. Mostrando todos los datos.",
        "success",
      );
    }, 800);
  }, 400);
}

function formatearFecha(fecha) {
  var d = new Date(fecha);
  var mes = "" + (d.getMonth() + 1);
  var dia = "" + d.getDate();
  var año = d.getFullYear();

  if (mes.length < 2) mes = "0" + mes;
  if (dia.length < 2) dia = "0" + dia;

  return [año, mes, dia].join("-");
}

function formatearMoneda(cantidad) {
  return new Intl.NumberFormat("en-US", {
    style: "currency",
    currency: "USD",
  }).format(cantidad);
}

// Función para convertir cualquier texto que contenga S/ a formato dólar
function convertirSolesADolares(texto) {
  if (typeof texto === "string" && texto.includes("S/")) {
    // Extraer el número del valor en soles
    var numero = texto.replace("S/", "").replace(/,/g, "").trim();
    if (!isNaN(numero)) {
      return formatearMoneda(parseFloat(numero));
    }
  }
  return texto;
}

// Función para forzar conversión de S/ a $ en toda la página
function forzarConversionASolesADolares() {
  // Buscar todos los elementos que contengan "S/" y convertirlos
  $("*").each(function () {
    var elemento = $(this);
    var texto = elemento.text();

    if (texto && texto.includes("S/")) {
      var numeroMatch = texto.match(/S\/\s*([0-9,]+\.?[0-9]*)/);
      if (numeroMatch && numeroMatch[1]) {
        var numero = parseFloat(numeroMatch[1].replace(/,/g, ""));
        if (!isNaN(numero)) {
          var nuevoTexto = texto.replace(
            /S\/\s*[0-9,]+\.?[0-9]*/,
            formatearMoneda(numero),
          );
          elemento.text(nuevoTexto);
        }
      }
    }
  });

  // También revisar valores en inputs y otros elementos
  $("input").each(function () {
    var elemento = $(this);
    var valor = elemento.val();
    if (valor && valor.includes("S/")) {
      var nuevoValor = convertirSolesADolares(valor);
      elemento.val(nuevoValor);
    }
  });

  // Forzar actualización específica de los cards de resumen
  ["#totalVentas", "#totalCostos", "#gananciaNeta"].forEach(
    function (selector) {
      var elemento = $(selector);
      var texto = elemento.text();
      if (texto && texto.includes("S/")) {
        var numero = texto.replace("S/", "").replace(/,/g, "").trim();
        if (!isNaN(numero)) {
          elemento.text(formatearMoneda(parseFloat(numero)));
        }
      }
    },
  );
}

// Función para limpiar caché manualmente
function limpiarCacheYActualizar() {
  // Limpiar localStorage si existe
  if (typeof Storage !== "undefined") {
    localStorage.removeItem("ganancias_cache");
    sessionStorage.removeItem("ganancias_cache");
  }

  // Forzar recarga de datos
  cargarGanancias();
  cargarResumenGanancias();

  // Aplicar conversión múltiples veces para asegurar
  setTimeout(function () {
    forzarConversionASolesADolares();
  }, 500);

  setTimeout(function () {
    forzarConversionASolesADolares();
  }, 1500);
}

function mostrarAlerta(mensaje, tipo) {
  var contenedor = $("#alertas");
  if (contenedor.length === 0) {
    return;
  }

  // Determinar clase de alerta
  var clase = "alert-success";
  if (tipo === "error" || tipo === "danger") {
    clase = "alert-danger";
  } else if (tipo === "warning") {
    clase = "alert-warning";
  } else if (tipo === "info") {
    clase = "alert-info";
  }

  // HTML simple y limpio
  var alerta =
    '<div class="alert ' +
    clase +
    ' alert-dismissible" role="alert">' +
    '<button type="button" class="close" data-dismiss="alert">' +
    "<span>&times;</span>" +
    "</button>" +
    mensaje +
    "</div>";

  contenedor.html(alerta);

  // Auto ocultar después de 4 segundos
  setTimeout(function () {
    contenedor.find(".alert").fadeOut(400, function () {
      contenedor.empty();
    });
  }, 4000);
}

function exportarGananciasPDF() {
  var fechaDesde = $("#fechaDesde").val();
  var fechaHasta = $("#fechaHasta").val();
  var codigoProducto = $("#selectProducto").val();

  // Crear formulario temporal para enviar datos via POST
  var form = $("<form>");
  form.attr("method", "post");
  form.attr(
    "action",
    "../controllers/ganancias/gananciasExportPDFController.php",
  );
  form.attr("target", "_blank");

  // Usar nombres que espera el PHP (con guiones bajos)
  if (fechaDesde) {
    form.append(
      $("<input>")
        .attr("type", "hidden")
        .attr("name", "fecha_desde")
        .val(fechaDesde),
    );
  }
  if (fechaHasta) {
    form.append(
      $("<input>")
        .attr("type", "hidden")
        .attr("name", "fecha_hasta")
        .val(fechaHasta),
    );
  }
  if (codigoProducto) {
    form.append(
      $("<input>")
        .attr("type", "hidden")
        .attr("name", "codigo_producto")
        .val(codigoProducto),
    );
  }

  $("body").append(form);
  form.submit();
  form.remove();

  mostrarAlerta("Generando reporte PDF...", "success");
}

function exportarGananciasExcel() {
  var fechaDesde = $("#fechaDesde").val();
  var fechaHasta = $("#fechaHasta").val();
  var codigoProducto = $("#selectProducto").val();

  // Crear formulario temporal para enviar datos via POST
  var form = $("<form>");
  form.attr("method", "post");
  form.attr(
    "action",
    "../controllers/ganancias/gananciasExportExcelController.php",
  );

  if (fechaDesde) {
    form.append(
      $("<input>")
        .attr("type", "hidden")
        .attr("name", "fecha_desde")
        .val(fechaDesde),
    );
  }
  if (fechaHasta) {
    form.append(
      $("<input>")
        .attr("type", "hidden")
        .attr("name", "fecha_hasta")
        .val(fechaHasta),
    );
  }
  if (codigoProducto) {
    form.append(
      $("<input>")
        .attr("type", "hidden")
        .attr("name", "codigo_producto")
        .val(codigoProducto),
    );
  }

  $("body").append(form);
  form.submit();
  form.remove();

  mostrarAlerta("Generando reporte Excel...", "success");
}

// Función para mostrar el estado actual de los filtros
function mostrarEstadoFiltros() {
  var fechaDesde = $("#fechaDesde").val();
  var fechaHasta = $("#fechaHasta").val();
  var codigoProducto = $("#selectProducto").val();

  var filtrosAplicados = [];

  if (fechaDesde && fechaHasta) {
    filtrosAplicados.push("Período: " + fechaDesde + " al " + fechaHasta);
  }

  if (codigoProducto) {
    var productoTexto = $("#selectProducto option:selected").text();
    filtrosAplicados.push("Producto: " + productoTexto);
  }

  var estadoBadge = $("#estadoFiltros");
  if (estadoBadge.length && filtrosAplicados.length > 0) {
    estadoBadge.removeClass("badge-success").addClass("badge-warning");
    estadoBadge.html(
      '<i class="fas fa-filter"></i> Filtros: ' + filtrosAplicados.join(" | "),
    );
  } else if (estadoBadge.length) {
    estadoBadge.removeClass("badge-warning").addClass("badge-success");
    estadoBadge.html('<i class="fas fa-check"></i> Mostrando todos los datos');
  }
}
// Función para probar todos los tipos de alertas (solo para testing)
function probarAlertas() {
  mostrarAlerta("🟢 Esta es una alerta de éxito (success)", "success");

  setTimeout(function () {
    mostrarAlerta("🔵 Esta es una alerta informativa (info)", "info");
  }, 1500);

  setTimeout(function () {
    mostrarAlerta("🟡 Esta es una alerta de advertencia (warning)", "warning");
  }, 3000);

  setTimeout(function () {
    mostrarAlerta("🔴 Esta es una alerta de error/peligro (danger)", "danger");
  }, 4500);
}
