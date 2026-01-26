const app = new (function () {
  // Marcar que estamos usando gráfico dinámico
  window.appGraficoVentas = true;

  this.numVentasA = document.getElementById("numVentasA");
  this.numVentasI = document.getElementById("numVentasI");
  this.numClientes = document.getElementById("numClientes");
  this.numProductos = document.getElementById("numProductos");
  this.numProveedores = document.getElementById("numProveedores");
  this.numRoles = document.getElementById("numRoles");
  this.numDinero = document.getElementById("numDinero");
  this.numUsuarios = document.getElementById("numUsuarios");
  this.numCategorias = document.getElementById("numCategorias");

  this.contarDescripcion = () => {
    fetch("../controllers/main/mainListadoController.php", {
      method: "GET",
    })
      .then((res) => res.json())
      .then((data) => {
        if (data) {
          if (data["numRoles"] > 0) {
            this.numRoles.innerHTML = data["numRoles"];
          } else {
            this.numRoles.innerHTML = "0";
          }
          if (data["numClientes"] > 0) {
            this.numClientes.innerHTML = data["numClientes"];
          } else {
            this.numClientes.innerHTML = "0";
          }
          if (data["numProductos"] > 0) {
            this.numProductos.innerHTML = data["numProductos"];
          } else {
            this.numProductos.innerHTML = "0";
          }
          if (data["numProveedores"] > 0) {
            this.numProveedores.innerHTML = data["numProveedores"];
          } else {
            this.numProveedores.innerHTML = "0";
          }
          if (data["numFacturasA"] > 0) {
            this.numVentasA.innerHTML = data["numFacturasA"];
          } else {
            this.numVentasA.innerHTML = "0";
          }
          if (data["numFacturasI"] > 0) {
            this.numVentasI.innerHTML = data["numFacturasI"];
          } else {
            this.numVentasI.innerHTML = "0";
          }
          if (data["ventas"] > 0) {
            this.numDinero.innerHTML = "$ " + data["ventas"];
          } else {
            this.numDinero.innerHTML = "$ 0.00";
          }
          if (data["numUsuarios"] > 0) {
            this.numUsuarios.innerHTML = data["numUsuarios"];
          } else {
            this.numUsuarios.innerHTML = "0";
          }
          if (data["numCategorias"] > 0) {
            this.numCategorias.innerHTML = data["numCategorias"];
          } else {
            this.numCategorias.innerHTML = "0";
          }
          if (data["stockProductos"] > 0) {
            swal(
              "Advertencia!",
              "Existe: " +
                data["stockProductos"] +
                " producto con stock mínimo",
              "warning",
            );
          }
        }
      })
      .catch((err) => {});
  };

  // Cargar datos del gráfico de ventas
  this.cargarGraficoVentas = () => {
    fetch("../controllers/main/obtenerDatosGraficoController.php", {
      method: "GET",
    })
      .then((res) => res.json())
      .then((datos) => {
        // console.log("Datos recibidos del servidor:", datos);

        if (datos && datos.length >= 2) {
          // Validar que todos los valores sean números válidos
          const datosValidos = datos.map((item) => ({
            y: item.y,
            ventas: parseFloat(item.ventas) || 0,
            cantidad: parseInt(item.cantidad) || 0,
          }));

          // Actualizar el gráfico Morris con datos reales
          if (
            typeof Morris !== "undefined" &&
            document.getElementById("revenue-chart")
          ) {
            const grafico = new Morris.Area({
              element: "revenue-chart",
              resize: true,
              data: datosValidos,
              xkey: "y",
              ykeys: ["ventas"],
              labels: ["Total Ventas ($)"],
              lineColors: ["#3c8dbc"],
              fillOpacity: 0.6,
              hideHover: "auto",
              pointSize: 4,
              behaveLikeLine: true,
              gridLineColor: "#e0e0e0",
              smooth: true,
              parseTime: false,
              ymin: 0,
              padding: 10,
              yLabelFormat: function (y) {
                return "$" + y.toFixed(2);
              },
              hoverCallback: function (index, options, content, row) {
                if (row.ventas > 0) {
                  return (
                    '<div style="padding: 8px; background: white; border: 2px solid #3c8dbc; border-radius: 5px;">' +
                    "<strong style='color: #3c8dbc; font-size: 14px;'>" +
                    row.y +
                    "</strong><br>" +
                    "<span style='color: #333;'>Total Ventas: <strong style='color: #27ae60; font-size: 16px;'>$" +
                    row.ventas.toFixed(2) +
                    "</strong></span><br>" +
                    "<span style='color: #666;'>Cantidad: " +
                    row.cantidad +
                    " venta(s)</span>" +
                    "</div>"
                  );
                } else {
                  return (
                    '<div style="padding: 8px;">' +
                    "<strong>" +
                    row.y +
                    "</strong><br>" +
                    "<span style='color: #999;'>Sin ventas</span>" +
                    "</div>"
                  );
                }
              },
            });

            // Agregar etiquetas de valores en puntos con ventas
            setTimeout(function () {
              const svg = document.querySelector("#revenue-chart svg");
              if (svg) {
                datosValidos.forEach((item, index) => {
                  if (item.ventas > 0) {
                    const text = document.createElementNS(
                      "http://www.w3.org/2000/svg",
                      "text",
                    );
                    text.setAttribute("class", "venta-label-" + index);
                    text.setAttribute("fill", "#2c3e50");
                    text.setAttribute("font-size", "11");
                    text.setAttribute("font-weight", "bold");
                    text.textContent = "$" + item.ventas.toFixed(2);

                    // Intentar posicionar cerca del punto
                    const circle = svg.querySelector(
                      'circle[data-index="' + index + '"]',
                    );
                    if (circle) {
                      const x = parseFloat(circle.getAttribute("cx"));
                      const y = parseFloat(circle.getAttribute("cy")) - 10;
                      text.setAttribute("x", x);
                      text.setAttribute("y", y);
                      text.setAttribute("text-anchor", "middle");
                      svg.appendChild(text);
                    }
                  }
                });
              }
            }, 500);
          }
        } else {
          console.warn("No hay suficientes datos para mostrar el gráfico");
        }
      })
      .catch((err) => console.error("Error al cargar gráfico:", err));
  };
})();

app.contarDescripcion();
app.cargarGraficoVentas();
