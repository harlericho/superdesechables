// reporteMensual.js
// Lógica para cargar el gráfico de dinero mensual

document.addEventListener("DOMContentLoaded", function () {
  // Obtener años disponibles (puedes cargar desde backend)
  const anioSelect = document.getElementById("anio");
  const currentYear = new Date().getFullYear();
  for (let y = currentYear; y >= currentYear - 5; y--) {
    const opt = document.createElement("option");
    opt.value = y;
    opt.textContent = y;
    anioSelect.appendChild(opt);
  }
  // Actualizar href PDF al llenar el selector
  actualizarHrefPDF();
  // Actualiza el href del botón PDF según el año seleccionado
  function actualizarHrefPDF() {
    var anio = document.getElementById("anio").value;
    var btn = document.getElementById("btnDescargarPDF");
    if (btn) {
      if (anio) {
        btn.href =
          "../controllers/ganancias/reportePDFMensualController.php?anio=" +
          anio;
      } else {
        btn.href = "#";
      }
    }
  }

  // Evento para cambiar año y actualizar gráfico automáticamente
  $("#anio").on("select2:select", function (e) {
    var anio = $(this).val();
    actualizarHrefPDF();
    if (anio) {
      document.querySelector(".box-success").style.display = "";
      document.querySelector(".box-info").style.display = "";
      cargarGraficoYTabla(anio);
    }
  });
  $("#anio").on("select2:clear", function (e) {
    actualizarHrefPDF();
    document.querySelector(".box-success").style.display = "none";
    document.querySelector(".box-info").style.display = "none";
  });

  // Eliminar el evento change anterior para evitar duplicidad
  anioSelect.removeEventListener("change", function () {});
  anioSelect.addEventListener("change", actualizarHrefPDF);

  // Mostrar gráfico y tabla inicial solo si hay año seleccionado
  if (anioSelect.value) {
    document.querySelector(".box-success").style.display = "";
    document.querySelector(".box-info").style.display = "";
    cargarGraficoYTabla(anioSelect.value);
  } else {
    document.querySelector(".box-success").style.display = "none";
    document.querySelector(".box-info").style.display = "none";
  }
});

function cargarGraficoYTabla(anio) {
  fetch("../controllers/ganancias/gananciasPeriodoController.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body:
      "periodo=mensual&fecha_desde=" +
      anio +
      "-01-01&fecha_hasta=" +
      anio +
      "-12-31",
  })
    .then((res) => res.json())
    .then((data) => {
      var meses = [
        "Enero",
        "Feb",
        "Mar",
        "Abr",
        "May",
        "Jun",
        "Jul",
        "Ago",
        "Sep",
        "Oct",
        "Nov",
        "Dic",
      ];
      var dinero = Array(12).fill(0);
      // Llenar tabla detalle mensual
      var tbody = document.querySelector("#tablaDetalleMensual tbody");
      tbody.innerHTML = "";
      if (data.length === 0) {
        var tr = document.createElement("tr");
        tr.innerHTML = `<td colspan="7" style="text-align:center;color:#888;">No hay registros para este año</td>`;
        tbody.appendChild(tr);
      } else {
        data.forEach(function (item) {
          var mes = parseInt(item.periodo.split("-")[1], 10) - 1;
          dinero[mes] = parseFloat(item.total_ingresos);
          var tr = document.createElement("tr");
          tr.innerHTML = `
            <td>${meses[mes]}</td>
            <td>${item.total_ventas}</td>
            <td>${item.cantidad_vendida}</td>
            <td>${item.total_ingresos}</td>
            <td>${item.total_descuentos}</td>
            <td>${item.total_costos}</td>
            <td>${item.ganancia_neta}</td>
          `;
          tbody.appendChild(tr);
        });
      }
      // Mostrar gráficos con Chart.js 1.0.2
      var ctxBar = document
        .getElementById("dineroMensualChart")
        .getContext("2d");
      // Paletas de colores por gráfico
      var coloresBarra = "rgba(255,159,64,0.5)"; // naranja
      // BAR CHART
      var chartDataBar = {
        labels: meses,
        datasets: [
          {
            label: "Dinero por mes",
            fillColor: coloresBarra,
            strokeColor: "rgba(255,159,64,1)",
            highlightFill: "rgba(255,159,64,0.7)",
            highlightStroke: "rgba(255,159,64,1)",
            data: dinero,
          },
        ],
      };
      window.dineroMensualChart = new Chart(ctxBar).Bar(chartDataBar, {
        responsive: true,
      });
    })
    .catch(function (err) {
      console.error("Error cargando datos:", err);
    });
}
