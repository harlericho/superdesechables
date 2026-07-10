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
  this.descuentoGlobal = document.getElementsByName("descuento_global")[0];
  this.impuestoFactura = document.getElementsByName("impuesto_factura")[0];
  this.totalFactura = document.getElementsByName("total_factura")[0];
  this.clienteId = document.getElementsByName("cliente")[0];
  this.numeroFactura = document.getElementById("numero_factura");

  this.detalles = document.getElementById("tbody");
  this._rawProductSum = 0;
  this.buscarTimeout = null; // Para el debounce

  this.obtener = () => {
    // Limpiar el timeout anterior si existe
    if (this.buscarTimeout) {
      clearTimeout(this.buscarTimeout);
    }

    // Esperar 300ms antes de buscar (para que termine de escribir la pistola)
    this.buscarTimeout = setTimeout(() => {
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
    }, 300);
  };
  this.buscarInmediato = () => {
    // Buscar inmediatamente cuando se presiona Enter (sin debounce)
    if (this.buscarTimeout) {
      clearTimeout(this.buscarTimeout);
    }

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
          this._detallesData = data;
          let html = "";
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
          this._detallesData = [];
          this.detalles.innerHTML =
            "<tr><td colspan='7'>No hay detalles de productos</td></tr>";
          this._rawProductSum = 0;
          this.calcularTotal();
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
              element.cliente_nombres +
              " " +
              element.cliente_apellidos +
              " (" +
              element.cliente_dni +
              ")" +
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
        this._rawProductSum = parseFloat(data.total) || 0;
        this.calcularTotal();
      })
      .catch((err) => console.log(err));
  };
  this.calcularTotal = () => {
    const toggleEl = document.getElementById("toggleIncluyeIva");
    const incluyeIva = toggleEl && toggleEl.checked;
    const rawSum = this._rawProductSum;
    const tax = parseFloat(this.impuestoFactura.value) || 0;

    let descuentoPorcentaje =
      parseFloat(
        this.descuentoGlobal && this.descuentoGlobal.value
          ? this.descuentoGlobal.value
          : 0,
      ) || 0;
    if (descuentoPorcentaje < 0) descuentoPorcentaje = 0;
    if (descuentoPorcentaje > 100) descuentoPorcentaje = 100;

    if (incluyeIva && tax > 0) {
      let descuento = rawSum * (descuentoPorcentaje / 100);
      let totalFinal = rawSum - descuento;
      
      if (this._detallesData && this._detallesData.length > 0) {
          let factorIva = 1 + tax / 100;
          let sumPrecioSinImpuesto = 0;
          let sumIva = 0;
          
          let subtotalBrutoDetalles = 0;
          this._detallesData.forEach((element) => {
              subtotalBrutoDetalles += Math.round((parseFloat(element.temp_total) / factorIva) * 100) / 100;
          });
          
          let descuentoGlobalMonto = descuento / factorIva;

          this._detallesData.forEach((element) => {
              let precioNetoProducto = Math.round((parseFloat(element.temp_total) / factorIva) * 100) / 100;
              let cantidad = parseFloat(element.temp_cantidad_vender);
              let precioUnit = cantidad > 0 ? Math.ceil((precioNetoProducto / cantidad) * 100) / 100 : 0;
              let subtotalLinea = Math.round(cantidad * precioUnit * 100) / 100;
              let descuentoProducto = Math.round(Math.max(0, subtotalLinea - precioNetoProducto) * 100) / 100;
              
              let descuentoGlobalLinea = 0;
              if (descuentoGlobalMonto > 0 && subtotalBrutoDetalles > 0) {
                  descuentoGlobalLinea = descuentoGlobalMonto * (precioNetoProducto / subtotalBrutoDetalles);
              }
              let descuentoLinea = Math.round((descuentoProducto + descuentoGlobalLinea) * 100) / 100;
              let precioSinImpuesto = Math.round((subtotalLinea - descuentoLinea) * 100) / 100;
              let valorIva = Math.round((precioSinImpuesto * tax / 100) * 100) / 100;
              
              sumPrecioSinImpuesto += precioSinImpuesto;
              sumIva += valorIva;
          });
          
          let totalCalculadoCent = Math.round((sumPrecioSinImpuesto + sumIva) * 100);
          let totalRealCent = Math.round(totalFinal * 100);
          let diffCent = totalRealCent - totalCalculadoCent;
          
          if (diffCent !== 0) {
              sumPrecioSinImpuesto += diffCent / 100;
          }
          
          this.subTotalFactura.value = sumPrecioSinImpuesto.toFixed(2);
          this.totalFactura.value = totalFinal.toFixed(2);
      } else {
          let subtotalNeto = totalFinal / (1 + tax / 100);
          this.subTotalFactura.value = subtotalNeto.toFixed(2);
          this.totalFactura.value = totalFinal.toFixed(2);
      }
    } else {
      // Modo normal: rawSum es el subtotal sin IVA → agregar IVA encima
      this.subTotalFactura.value = rawSum.toFixed(2);
      let descuento = rawSum * (descuentoPorcentaje / 100);
      let base = rawSum - descuento;
      if (base < 0) base = 0;
      let total = tax > 0 ? base + (base * tax) / 100 : base;
      this.totalFactura.value = Number.isNaN(total) ? "0.00" : total.toFixed(2);
    }

    // Listener para el descuento global
    if (this.descuentoGlobal && !this._descuentoGlobalListener) {
      this.descuentoGlobal.addEventListener("input", () => {
        app.calcularTotal();
      });
      this._descuentoGlobalListener = true;
    }
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
    // Calcular el valor del descuento global y enviarlo al backend
    let subtotal = parseFloat(this.subTotalFactura.value) || 0;
    let descuentoPorcentaje =
      parseFloat(
        this.descuentoGlobal && this.descuentoGlobal.value
          ? this.descuentoGlobal.value
          : 0,
      ) || 0;
    if (descuentoPorcentaje < 0) descuentoPorcentaje = 0;
    if (descuentoPorcentaje > 100) descuentoPorcentaje = 100;
    if (this.descuentoGlobal) {
      const toggleIva = document.getElementById("toggleIncluyeIva");
      const incluyeIvaGuardar = toggleIva && toggleIva.checked;
      if (incluyeIvaGuardar) {
        // Descuento calculado sobre el precio bruto (con IVA) para mostrar correctamente en PDF.
        // El subtotal enviado ya es el neto post-descuento, el backend NO debe volver a restarlo.
        let descuentoBruto = this._rawProductSum * (descuentoPorcentaje / 100);
        form.set("descuento_global", descuentoBruto.toFixed(2));
        form.set("descuento_global_porcentaje", descuentoPorcentaje.toFixed(2));
        form.set("incluye_iva", "1");
      } else {
        let descuentoValor = subtotal * (descuentoPorcentaje / 100);
        form.set("descuento_global", descuentoValor.toFixed(2));
        form.set("descuento_global_porcentaje", descuentoPorcentaje.toFixed(2));
      }
    }
        if (form.get("cliente") === null) {
          swal("Atención!", "Seleccione un cliente", "warning");
          return;
        }
        if (form.get("comprobante") === null) {
          swal("Atención!", "Seleccione un comprobante", "warning");
          return;
        }

        // ── Mostrar modal de progreso SRI ────────────────────────────────────────
        const pasos = [
          {
            titulo: "Registrando venta...",
            detalle: "Guardando factura en el sistema",
            pct: 10,
            label: "Paso 1 de 5",
          },
          {
            titulo: "Generando XML electrónico...",
            detalle: "Preparando comprobante para el SRI",
            pct: 30,
            label: "Paso 2 de 5",
          },
          {
            titulo: "Firmando con certificado digital...",
            detalle: "Aplicando firma XAdES-BES",
            pct: 50,
            label: "Paso 3 de 5",
          },
          {
            titulo: "Conectando al SRI Ecuador...",
            detalle: "Estableciendo conexión segura con celcer.sri.gob.ec",
            pct: 68,
            label: "Paso 4 de 5",
          },
          {
            titulo: "Esperando autorización del SRI...",
            detalle: "El SRI está validando el comprobante",
            pct: 85,
            label: "Paso 5 de 5",
          },
        ];
        const timers = [];
        let pasoActual = 0;

        const actualizarPaso = (idx) => {
          const p = pasos[idx];
          document.getElementById("sriPasoTitulo").textContent = p.titulo;
          document.getElementById("sriPasoDetalle").textContent = p.detalle;
          document.getElementById("sriProgressBar").style.width = p.pct + "%";
          document.getElementById("sriPasoNum").textContent = p.label;
        };

        actualizarPaso(0);
        $("#modalSriProceso").modal("show");

        // Tiempos estimados para cada paso siguiente
        const delays = [1800, 3200, 5000, 7500];
        delays.forEach((ms, i) => {
          timers.push(setTimeout(() => actualizarPaso(i + 1), ms));
        });

        const cerrarModal = () => {
          timers.forEach(clearTimeout);
          document.getElementById("sriProgressBar").style.width = "100%";
          setTimeout(() => $("#modalSriProceso").modal("hide"), 350);
        };
        // ── Fin modal progreso ───────────────────────────────────────────────────

        fetch("../controllers/factura/facturaGuardarController.php", {
          method: "POST",
          body: form,
        })
          .then((res) => res.json())
          .then((data) => {
            cerrarModal();
            this.totalFactura.value = "0.00";
            if (data === 1 || data.status === 1) {
              let mensaje = "Factura registrada!!";
              let icono = "success";
              if (data.factura_electronica) {
                const fe = data.factura_electronica;
                if (fe.autorizado) {
                  mensaje =
                    "¡Factura electrónica AUTORIZADA por el SRI!\n\nN° Autorización: " +
                    (fe.numero_autorizacion || fe.clave_acceso || "").substring(
                      0,
                      24,
                    ) +
                    "...";
                } else if (
                  fe.estado_sri === "EN_PROCESO" ||
                  fe.estado_sri === "PENDIENTE"
                ) {
                  mensaje =
                    "Factura guardada. El SRI está procesando el comprobante.\nEstado: " +
                    fe.estado_sri +
                    "\nPuede verificar más tarde.";
                  icono = "warning";
                } else {
                  mensaje =
                    "Factura guardada. " +
                    (fe.mensaje ||
                      "Error al autorizar en el SRI. Estado: " +
                        (fe.estado_sri || "ERROR"));
                  icono = "warning";
                }
              }

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

              swal(mensaje, { icon: icono }).then(() => {
                const facturaId = data.factura_id;
                window.open(
                  "../controllers/factura/facturaPdfController.php?factura_id=" +
                    facturaId +
                    "&enviar=1",
                  "_blank",
                );
              });
              this.limpiar_factura();
              this.listadoDetalles();
              this.mostrarSerieFactura();
              this.cargarImpuestoActivo();
            } else if (data === 2 || data.status === 2) {
              swal("Numero de factura ya existe!!", { icon: "error" });
              this.numeroFactura.focus();
              this.calcularTotal();
            }
          })
          .catch((err) => {
            cerrarModal();
            console.log(err);
            swal(
              "Error",
              "No se pudo completar la venta. Verifique la conexión.",
              "error",
            );
          });
  };

  // Función global para impresión QZ Tray
  function printTicketQZ(ticket) {
    function doPrint(printerName) {
      qz.printers
        .find(printerName)
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
    fetch("../controllers/configserie/configImpresoraObtenerController.php")
      .then((r) => r.json())
      .then((cfg) => {
        const printerName = cfg && cfg.impresora ? cfg.impresora : "XP-80C";
        if (!qz.websocket.isActive()) {
          qz.websocket
            .connect()
            .then(() => doPrint(printerName))
            .catch((err) => {
              alert("No se pudo conectar a QZ Tray: " + err);
            });
        } else {
          doPrint(printerName);
        }
      })
      .catch(() => {
        // Fallback: intentar con nombre por defecto
        if (!qz.websocket.isActive()) {
          qz.websocket.connect().then(() => doPrint("XP-80C"));
        } else {
          doPrint("XP-80C");
        }
      });
  }
  this.limpiar = () => {
    $("#formProductoDetalle")[0].reset();
    this.codigoMensaje.innerHTML =
      "<small class='text-blue'>Debes escribir el código del producto</small>";
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

  this.cargarImpuestoActivo = () => {
    fetch(
      "../controllers/impuesto/impuestoController.php?action=obtenerImpuestoActivo",
      {
        method: "GET",
      },
    )
      .then((res) => {
        return res.json();
      })
      .then((data) => {
        if (data && data.impuesto_porcentaje !== undefined) {
          this.impuestoFactura.value = data.impuesto_porcentaje;
          document.getElementById("impuesto_id").value = data.impuesto_id;
          this.impuestoFactura.placeholder =
            data.impuesto_nombre + " (" + data.impuesto_porcentaje + "%)";
          this.calcularTotal(); // Recalcular total con el nuevo impuesto
        } else {
          this.impuestoFactura.value = 0;
          this.impuestoFactura.placeholder = "Sin impuesto configurado";
        }
      })
      .catch((err) => {
        this.impuestoFactura.value = 0;
        this.impuestoFactura.placeholder = "Error al cargar impuesto";
      });
  };
})();
app.listadoClientes();
app.listadoComprobante();
app.mostrarSerieFactura();
app.cargarImpuestoActivo();
app.listadoDetalles();
// Recalcular total cuando se modifica manualmente el porcentaje de impuesto
app.impuestoFactura.addEventListener("input", function () {
  app.calcularTotal();
});
// Event listeners para el campo de código (pistola de código de barras)
app.codigo.addEventListener("keyup", function (e) {
  // Si presiona Enter, buscar inmediatamente
  if (e.key === "Enter" || e.keyCode === 13) {
    app.buscarInmediato();
  } else {
    // Para otras teclas, usar búsqueda con debounce
    app.obtener();
  }
});
// Toggle: precios con IVA incluido
document
  .getElementById("toggleIncluyeIva")
  .addEventListener("change", function () {
    app.calcularTotal();
  });

//app.recargar();
