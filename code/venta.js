const app = new (function () {
  this.id = document.getElementsByName("id")[0];
  this.codigo = document.getElementsByName("codigo")[0];
  this.nombre = document.getElementsByName("nombre")[0];
  this.descripcion = document.getElementsByName("descripcion")[0];
  this.precio_v = document.getElementsByName("precio_v")[0];
  this.stock = document.getElementsByName("stock")[0];
  this.cantidad = document.getElementsByName("cantidad")[0];
  this.descuento = document.getElementsByName("descuento")[0];
  this.codigoMensaje = document.getElementById("codigoMensaje");
  this.selectorCliente = document.getElementById("selectorCliente");
  this.selectorFormapago = document.getElementById("selectorFormapago");

  this.numeroFactura = document.getElementsByName("numero_factura")[0];
  this.subTotalFactura = document.getElementsByName("subtotal_factura")[0];
  this.impuestoFactura = document.getElementsByName("impuesto_factura")[0];
  this.totalFactura = document.getElementsByName("total_factura")[0];
  this.clienteId = document.getElementsByName("cliente")[0];
  this.numeroFactura = document.getElementById("numero_factura");

  this.detalles = document.getElementById("tbody");

  this.obtener = () => {
    var form = new FormData();
    form.append("codigo", this.codigo.value);
    fetch("../controllers/venta/ventaListadoProdController.php", {
      method: "POST",
      body: form,
    })
      .then((res) => res.json())
      .then((data) => {
        if (data) {
          this.id.value = data.producto_id;
          this.nombre.value = data.producto_nombre;
          this.descripcion.value = data.producto_descripcion;
          this.precio_v.value = data.producto_precio_venta;
          this.stock.value = data.producto_stock;
          this.codigoMensaje.innerHTML =
            "<small class='text-green'>Código encontrado</small>";
        } else {
          this.id.value = "";
          this.nombre.value = "";
          this.descripcion.value = "";
          this.precio_v.value = "";
          this.stock.value = "";
          this.codigoMensaje.innerHTML =
            "<small class='text-red'>Código no encontrado</small>";
          this.codigo.focus();
        }
      })
      .catch((err) => console.log(err));
  };
  this.guardar = () => {
    const form = new FormData();
    form.append("id", this.id.value);
    form.append("codigo", this.codigo.value);
    form.append("nombre", this.nombre.value);
    form.append("stock", this.stock.value);
    form.append("cantidad", this.cantidad.value);
    form.append("descuento", this.descuento.value);
    form.append("precio_v", this.precio_v.value);
    fetch("../controllers/venta/ventaGuardarDetProdController.php", {
      method: "POST",
      body: form,
    })
      .then((res) => res.json())
      .then((data) => {
        if (data === 1) {
          swal("Producto agregado!!", {
            icon: "success",
          });
          this.listadoDetalles();
          this.limpiar();
        } else if (data === 2) {
          swal("Atención!", "Stock superior al del inventario", "warning");
          this.cantidad.focus();
        }
      })
      .catch((err) => console.log(err));
  };
  this.listadoDetalles = () => {
    fetch("../controllers/venta/ventaDetalleListadoController.php", {
      method: "GET",
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.length > 0) {
          let html = [];
          data.forEach((element) => {
            html += "<tr>";
            html +=
              "<td><strong>" + element.temp_cod_producto + "</strong></td>";
            html +=
              "<td><strong>" + element.temp_nombre_producto + "</strong></td>";
            html +=
              "<td><strong>" + element.temp_cantidad_vender + "</strong></td>";
            html +=
              "<td><strong>" + element.temp_precio_producto + "</strong></td>";
            html += "<td><strong>" + element.temp_descuento + "</strong></td>";
            html += "<td><strong>" + element.temp_total + "</strong></td>";
            html +=
              "<td><button type='button' class='btn btn-danger btn-sm' title='ELiminar' onClick='app.eliminar(" +
              element.temp_id_producto +
              "," +
              element.temp_cantidad_vender +
              "," +
              element.temp_id +
              ")'><i class= 'fa fa-trash'></i></button></td>";
            html += "</tr>";
          });
          this.detalles.innerHTML = html;
          this.sumarSubTotal();
        } else {
          this.detalles.innerHTML =
            "<tr><td colspan='7'>No hay detalles de productos</td></tr>";
          this.subTotalFactura.value = "0.00";
          this.totalFactura.value = "0.00";
        }
      })
      .catch((err) => console.log(err));
  };

  this.eliminar = (producto_id, temp_cantidad, temp_id) => {
    var form = new FormData();
    form.append("temp_cantidad", temp_cantidad);
    form.append("producto_id", producto_id);
    form.append("temp_id", temp_id);
    fetch("../controllers/venta/ventaDetalleFilaEliminarController.php", {
      method: "POST",
      body: form,
    })
      .then((res) => res.json())
      .then((data) => {
        if (data === 1) {
          this.listadoDetalles();
        }
      })
      .catch((err) => console.log(err));
  };
  this.listadoClientes = (clienteIdSeleccionar = null) => {
    fetch("../controllers/cliente/clienteListadoController.php", {
      method: "GET",
    })
      .then((res) => res.json())
      .then((data) => {
        html =
          "<select class='form-control select2' style='width: 100%;' name='cliente' id='cliente' autofocus required>";
        html +=
          "<option disabled='selected' " +
          (clienteIdSeleccionar ? "" : "selected='selected'") +
          ">Seleccione</option>";
        data.forEach((element) => {
          if (element.cliente_estado != "0") {
            const selected =
              clienteIdSeleccionar && element.cliente_id == clienteIdSeleccionar
                ? "selected='selected'"
                : "";
            html +=
              "<option value='" +
              element.cliente_id +
              "' " +
              selected +
              ">" +
              element.cliente_dni +
              " - " +
              element.cliente_nombres +
              " - " +
              element.cliente_apellidos +
              "</option>";
          }
        });
        html += "</select>";
        this.selectorCliente.innerHTML = html;
        $(".select2").select2();
        if (clienteIdSeleccionar) {
          setTimeout(() => {
            $("#cliente").val(clienteIdSeleccionar).trigger("change");
          }, 100);
        }
      })
      .catch((err) => console.log(err));
  };

  this.guardarNuevoCliente = (event) => {
    event.preventDefault();
    const formCliente = document.getElementById("formNuevoCliente");
    const formData = new FormData(formCliente);

    fetch("../controllers/cliente/clienteGuardarController.php", {
      method: "POST",
      body: formData,
    })
      .then((res) => res.text())
      .then((text) => {
        console.log("Respuesta del servidor:", text);
        try {
          const data = JSON.parse(text);
          if (data.success && data.code === 1) {
            swal(
              "Cliente guardado!",
              "El cliente se ha registrado exitosamente",
              "success",
            );
            $("#modalNuevoCliente").modal("hide");
            formCliente.reset();
            this.listadoClientes(data.cliente_id);
          } else if (data.code === 2) {
            swal(
              "DNI duplicado!",
              "Ya existe un cliente con ese DNI",
              "warning",
            );
          } else if (data.code === 3) {
            swal(
              "Email duplicado!",
              "Ya existe un cliente con ese email",
              "warning",
            );
          } else if (data.code === 4) {
            swal(
              "Teléfono duplicado!",
              "Ya existe un cliente con ese teléfono",
              "warning",
            );
          } else {
            swal("Error!", "No se pudo guardar el cliente", "error");
          }
        } catch (e) {
          console.error("Error parseando JSON:", e);
          console.error("Respuesta recibida:", text);
          swal("Error!", "Respuesta inválida del servidor: " + text, "error");
        }
      })
      .catch((err) => {
        console.error("Error al guardar:", err);
        swal("Error!", "Ocurrió un error al guardar el cliente", "error");
      });
  };
  this.listadoComprobante = () => {
    fetch("../controllers/comprobante/comprobanteListadoController.php", {
      method: "GET",
    })
      .then((res) => res.json())
      .then((data) => {
        html =
          "<select class='form-control select2' style='width: 100%;' name='comprobante' id='comprobante' autofocus required>";
        html +=
          "<option disabled='selected' selected='selected'>Seleccione</option>";
        data.forEach((element) => {
          html +=
            "<option value='" +
            element.tipo_comp_id +
            "'>" +
            element.tipo_comp_descripcion +
            "</option>";
        });
        html += "</select>";
        this.selectorFormapago.innerHTML = html;
        $(".select2").select2();
      })
      .catch((err) => console.log(err));
  };
  this.sumarSubTotal = () => {
    fetch("../controllers/venta/ventaSumSubDetalleController.php", {
      method: "GET",
    })
      .then((res) => res.json())
      .then((data) => {
        this.subTotalFactura.value = parseFloat(data.total).toFixed(2);
        this.totalFactura.value = parseFloat(data.total).toFixed(2);
      })
      .catch((err) => console.log(err));
  };
  this.calcularTotal = () => {
    if (this.impuestoFactura.value.length > 0) {
      var total =
        parseFloat(this.subTotalFactura.value) +
        (parseFloat(this.subTotalFactura.value) *
          parseFloat(this.impuestoFactura.value)) /
          100;
    } else {
      total = parseFloat(this.totalFactura.value);
    }
    //this.totalFactura.value = total;
    // Forzar el formateo a dos decimales SIEMPRE
    this.totalFactura.value = Number.isNaN(total) ? "0.00" : total.toFixed(2);
  };

  this.guardarFactura = () => {
    // Validar que existan productos en el detalle
    const filas = this.detalles.querySelectorAll("tr");
    const hayDetalles =
      filas.length > 0 &&
      !this.detalles.innerHTML.includes("No hay detalles de productos");

    if (!hayDetalles) {
      swal(
        "Atención!",
        "Debe agregar al menos un producto al detalle",
        "warning",
      );
      return;
    }

    const form = new FormData(document.getElementById("formFactura"));
    if (form.get("cliente") !== null) {
      if (form.get("comprobante") !== null) {
        fetch("../controllers/factura/facturaGuardarController.php", {
          method: "POST",
          body: form,
        })
          .then((res) => res.json())
          .then((data) => {
            this.totalFactura.value = "0.00";
            if (data === 1 || data.status === 1) {
              // Impresión automática con QZ Tray si el checkbox está marcado
              if (document.getElementById("imprimirTicketCheckbox").checked) {
                fetch(
                  `../controllers/factura/facturaTicketDataController.php?factura_id=${data.factura_id}`,
                )
                  .then((res) => res.json())
                  .then((ticketData) => {
                    if (ticketData && ticketData.ticket_escpos) {
                      printTicketQZ(ticketData.ticket_escpos);
                    } else {
                      console.error("No se recibió ticket_escpos:", ticketData);
                    }
                  })
                  .catch((err) => {
                    console.error("Error al obtener el ticket:", err);
                  });
              }

              swal("Factura registrada!!", {
                icon: "success",
              }).then(() => {
                // Abrir la factura en una nueva ventana
                const facturaId = data.factura_id;
                window.open(
                  "../controllers/factura/facturaPdfController.php?factura_id=" +
                    facturaId,
                  "_blank",
                );
              });
              this.limpiar_factura();
              this.listadoDetalles();
              this.mostrarSerieFactura();
            } else if (data === 2 || data.status === 2) {
              swal("Numero de factura ya existe!!", {
                icon: "error",
              });
              this.numeroFactura.focus();
              this.calcularTotal();
            }
          })
          .catch((err) => console.log(err));
      } else {
        swal("Atención!", "Seleccione un comprobante", "warning");
        return;
      }
    } else {
      swal("Atención!", "Seleccione un cliente", "warning");
      return;
    }
  };

  // Función global para impresión QZ Tray
  function printTicketQZ(ticket) {
    function doPrint() {
      qz.printers
        .find("XP-80C")
        .then((printer) => {
          const config = qz.configs.create(printer);
          const data = [{ type: "raw", format: "plain", data: ticket }];
          return qz.print(config, data);
        })
        .then(() => {
          return qz.websocket.disconnect();
        })
        .catch((err) => {
          alert("Error de impresión: " + err);
          qz.websocket.disconnect();
        });
    }
    if (!qz.websocket.isActive()) {
      qz.websocket
        .connect()
        .then(doPrint)
        .catch((err) => {
          alert("No se pudo conectar a QZ Tray: " + err);
        });
    } else {
      doPrint();
    }
  }
  this.limpiar = () => {
    $("#formProductoDetalle")[0].reset();
    this.mensajeInicio();
    this.id.value = "";
    this.codigo.focus();
  };
  this.limpiar_factura = () => {
    $("#formFactura")[0].reset();
    $("#cliente").val("Seleccione").trigger("change");
    $("#comprobante").val("Seleccione").trigger("change");
  };
  this.mensajeInicio = () => {
    this.codigoMensaje.innerHTML =
      "<small class='text-blue'>Debes escribir el código del producto</small>";
    this.detalles.innerHTML =
      "<tr><td colspan='7'>No hay detalles de productos</td></tr>";
  };
  this.recargar = () => {
    window.onbeforeunload = function () {
      return "¿Estás seguro que deseas salir de la actual página?";
    };
  };
  this.mostrarSerieFactura = () => {
    fetch("../controllers/configserie/configserieMostrarSerieController.php", {
      method: "GET",
    })
      .then((res) => res.json())
      .then((data) => {
        this.numeroFactura.value = data;
      })
      .catch((err) => console.log(err));
  };

  this.imprimirTicketDirecto = (facturaId) => {
    // Obtener datos de la factura
    fetch(
      `../controllers/factura/facturaTicketDataController.php?factura_id=${facturaId}`,
    )
      .then((res) => res.json())
      .then((ticketData) => {
        if (ticketData.factura && ticketData.detalles) {
          // Usar el conector del plugin
          ConectorPluginV3.imprimirTicket(
            ticketData.factura,
            ticketData.detalles,
            ticketData.cliente,
          )
            .then((resultado) => {
              console.log("✓ Ticket enviado a imprimir", resultado);
            })
            .catch((err) => {
              console.error("⚠ Error al imprimir:", err);
              swal(
                "Advertencia",
                "No se pudo imprimir el ticket. Verifica que el plugin esté corriendo en http://localhost:8000",
                "warning",
              );
            });
        }
      })
      .catch((err) => {
        console.error("Error al obtener datos:", err);
      });
  };
})();
app.listadoClientes();
app.listadoComprobante();
app.mostrarSerieFactura();
app.listadoDetalles();
//app.recargar();
