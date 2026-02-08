/**
 * Sistema de Cierre de Caja
 * Maneja toda la funcionalidad del cierre de caja diario
 */
class CierreCaja {
  constructor() {
    this.datosActuales = null;
    this.fechaActual = null;
    this.inicializar();
  }

  inicializar() {
    // Cargar historial al iniciar
    this.obtenerHistorial();

    // Event listeners
    document.getElementById("fecha").addEventListener("change", () => {
      this.limpiarDatos();
    });
  }

  /**
   * Obtener datos del día seleccionado
   */
  async obtenerDatos() {
    const fecha = document.getElementById("fecha").value;

    if (!fecha) {
      swal({
        title: "Error",
        text: "Seleccione una fecha válida",
        icon: "error",
        button: "Aceptar",
      });
      return;
    }

    // Mostrar loading
    swal({
      title: "Obteniendo datos...",
      text: "Calculando movimientos del día",
      icon: "info",
      buttons: false,
      closeOnClickOutside: false,
      closeOnEsc: false,
    });

    try {
      const formData = new FormData();
      formData.append("fecha", fecha);

      const response = await fetch(
        "../controllers/cierre_caja/obtenerDatosDiaController.php",
        {
          method: "POST",
          body: formData,
        },
      );

      // Verificar si la respuesta es exitosa
      if (!response.ok) {
        throw new Error(`Error HTTP: ${response.status}`);
      }

      const responseText = await response.text();

      // Verificar si hay errores de PHP antes del JSON
      if (responseText.includes("<br />")) {
        // Intentar extraer solo el JSON válido
        const jsonMatch = responseText.match(/\{.*\}$/);
        if (jsonMatch) {
          const data = JSON.parse(jsonMatch[0]);
          if (data.success) {
            // Cerrar el modal de loading
            swal.close();
            this.datosActuales = data;
            this.fechaActual = fecha;
            this.mostrarDatos(data);
            document.getElementById("btnRealizarCierre").disabled = false;
          } else {
            throw new Error(data.message || "Error al obtener datos");
          }
        } else {
          throw new Error(
            "Error del servidor: " + responseText.substring(0, 200),
          );
        }
      } else {
        const data = JSON.parse(responseText);
        if (data.success) {
          this.datosActuales = data;
          this.fechaActual = fecha;
          this.mostrarDatos(data);

          // Deshabilitar botón de cierre si ya existe un cierre para esta fecha
          const btnRealizarCierre =
            document.getElementById("btnRealizarCierre");
          if (data.cierre_existe) {
            btnRealizarCierre.disabled = true;
            btnRealizarCierre.innerHTML =
              '<i class="fa fa-check"></i> Ya Cerrado';
            btnRealizarCierre.classList.remove("btn-success");
            btnRealizarCierre.classList.add("btn-default");

            // Cerrar loading y mostrar mensaje informativo después
            swal({
              title: "Información",
              text:
                "Los datos se muestran correctamente. Ya existe un cierre de caja para la fecha " +
                fecha,
              icon: "info",
              button: "Aceptar",
            });
          } else {
            btnRealizarCierre.disabled = false;
            btnRealizarCierre.innerHTML =
              '<i class="fa fa-save"></i> Realizar Cierre';
            btnRealizarCierre.classList.remove("btn-default");
            btnRealizarCierre.classList.add("btn-success");

            // Solo cerrar loading si no hay cierre previo
            swal.close();
          }
        } else {
          throw new Error(data.message || "Error al obtener datos");
        }
      }
    } catch (error) {
      console.error("Error:", error);
      // Cerrar el modal de loading antes de mostrar el error
      swal.close();
      swal({
        title: "Error",
        text: error.message || "Error al obtener datos del día",
        icon: "error",
        button: "Aceptar",
      });
    }
  }

  /**
   * Mostrar datos en la interfaz
   */
  mostrarDatos(data) {
    // Mostrar resumen principal
    document.getElementById("resumenDia").style.display = "block";
    document.getElementById("mensajeSinDatos").style.display = "none";
    document.getElementById("seccionDetalles").style.display = "block";

    // Actualizar valores principales
    document.getElementById("saldoInicial").textContent =
      "$" + data.saldo_inicial;
    document.getElementById("saldoFinal").textContent =
      "$" + data.resumen.saldo_final;

    // Actualizar ventas por tipo de pago
    this.actualizarTablaVentasTipo(data.ventas_por_tipo || []);

    // Actualizar movimientos de caja
    this.actualizarTablaMovimientos(data.movimientos || []);

    // Actualizar balance con IVA
    document.getElementById("ventasSinIva").textContent =
      "$" + data.resumen.ventas_sin_iva;
    document.getElementById("ventasConIva").textContent =
      "$" + data.resumen.ventas_con_iva;
    document.getElementById("ivaCobrado").textContent =
      "$" + data.resumen.iva_cobrado;

    // Actualizar resumen final
    document.getElementById("totalIngresosEfectivo").textContent =
      "$" + data.resumen.total_ingresos_efectivo;
    document.getElementById("totalTransferencias").textContent =
      "$" + data.resumen.total_ingresos_transferencia;
    document.getElementById("totalCheques").textContent =
      "$" + data.resumen.total_ingresos_cheque;
    document.getElementById("otrosIngresos").textContent =
      "$" + data.resumen.total_ingresos_otros;
    document.getElementById("totalIngresosMovimientos").textContent =
      "$" + data.resumen.total_ingresos_mov;
    document.getElementById("totalEgresos").textContent =
      "$" + data.resumen.total_egresos;
    document.getElementById("saldoFinalDetalle").textContent =
      "$" + data.resumen.saldo_final;
  }

  /**
   * Actualizar tabla de ventas por tipo
   */
  actualizarTablaVentasTipo(ventas) {
    const tbody = document.getElementById("tbodyVentasTipo");
    tbody.innerHTML = "";

    if (ventas.length === 0) {
      tbody.innerHTML =
        '<tr><td colspan="3" class="text-center text-muted">No hay ventas registradas</td></tr>';
      return;
    }

    ventas.forEach((venta) => {
      const row = `
        <tr>
          <td>
            <span class="label label-primary">${venta.tipo_pago}</span>
          </td>
          <td>${venta.cantidad_facturas}</td>
          <td class="text-right">
            <strong>$${parseFloat(venta.total_ventas).toFixed(2)}</strong>
          </td>
        </tr>
      `;
      tbody.innerHTML += row;
    });
  }

  /**
   * Actualizar tabla de movimientos
   */
  actualizarTablaMovimientos(movimientos) {
    const tbody = document.getElementById("tbodyMovimientos");
    tbody.innerHTML = "";

    if (movimientos.length === 0) {
      tbody.innerHTML =
        '<tr><td colspan="3" class="text-center text-muted">No hay movimientos registrados</td></tr>';
      return;
    }

    movimientos.forEach((mov) => {
      const esIngreso = mov.mov_tipo === "INGRESO";
      const labelClass = esIngreso ? "label-success" : "label-danger";
      const icon = esIngreso ? "fa-arrow-up" : "fa-arrow-down";

      const row = `
        <tr>
          <td>
            <span class="label ${labelClass}">
              <i class="fa ${icon}"></i> ${mov.mov_tipo}
            </span>
          </td>
          <td>${mov.cantidad_movimientos}</td>
          <td class="text-right">
            <strong>$${parseFloat(mov.total_monto).toFixed(2)}</strong>
          </td>
        </tr>
      `;
      tbody.innerHTML += row;
    });
  }

  /**
   * Realizar cierre de caja
   */
  async realizarCierre() {
    if (!this.datosActuales || !this.fechaActual) {
      swal({
        title: "Error",
        text: "Primero debe obtener los datos del día",
        icon: "warning",
        button: "Aceptar",
      });
      return;
    }

    // Verificar si ya existe un cierre (doble seguridad)
    if (this.datosActuales.cierre_existe) {
      swal({
        title: "Cierre ya realizado",
        text: "Ya existe un cierre de caja para esta fecha. No se pueden realizar múltiples cierres por día.",
        icon: "error",
        button: "Aceptar",
      });
      return;
    }

    // Validar que las observaciones sean requeridas
    const observaciones = document.getElementById("observaciones").value.trim();
    if (!observaciones || observaciones.length < 5) {
      swal({
        title: "Observaciones requeridas",
        text: "Debe ingresar observaciones para el cierre de caja (mínimo 5 caracteres). Ejemplo: 'Cierre normal del día', 'Sin novedades', etc.",
        icon: "warning",
        button: "Aceptar",
      }).then(() => {
        // Enfocar el campo observaciones
        document.getElementById("observaciones").focus();
      });
      return;
    }

    // Confirmar cierre
    swal({
      title: "¿Realizar Cierre de Caja?",
      text: `Se realizará el cierre de caja para la fecha: ${this.fechaActual}\nSaldo final: $${this.datosActuales.resumen.saldo_final}\n\nEsta acción no se puede deshacer`,
      icon: "warning",
      buttons: {
        cancel: "Cancelar",
        confirm: {
          text: "Sí, realizar cierre",
          value: true,
        },
      },
      dangerMode: true,
    }).then((willProceed) => {
      if (willProceed) {
        this.ejecutarCierre();
      }
    });
  }
  /**
   * Ejecutar el cierre de caja
   */
  async ejecutarCierre() {
    try {
      const formData = new FormData();
      formData.append("fecha", this.fechaActual);
      formData.append(
        "observaciones",
        document.getElementById("observaciones").value,
      );

      const response = await fetch(
        "../controllers/cierre_caja/guardarCierreController.php",
        {
          method: "POST",
          body: formData,
        },
      );

      // Verificar respuesta y manejar errores de PHP
      if (!response.ok) {
        throw new Error(`Error HTTP: ${response.status}`);
      }

      const responseText = await response.text();

      if (responseText.includes("<br />")) {
        console.error("PHP Warnings/Errors detected:", responseText);
        const jsonMatch = responseText.match(/\{.*\}$/);
        if (jsonMatch) {
          var data = JSON.parse(jsonMatch[0]);
        } else {
          throw new Error(
            "Error del servidor: " + responseText.substring(0, 200),
          );
        }
      } else {
        var data = JSON.parse(responseText);
      }

      if (data.success) {
        // Mostrar directamente el modal de éxito
        swal({
          title: "¡Cierre Realizado!",
          text: `El cierre de caja se ha realizado exitosamente\nFecha: ${data.fecha}\nSaldo final: $${data.saldo_final}\nID de cierre: #${data.cierre_id}`,
          icon: "success",
          button: "Aceptar",
        }).then(() => {
          // Actualizar historial
          this.obtenerHistorial();

          // Actualizar SILENCIOSAMENTE el estado sin mostrar alertas adicionales
          this.actualizarEstadoSilencioso();

          // Limpiar solo las observaciones
          document.getElementById("observaciones").value = "";
        });
      } else {
        throw new Error(data.message || "Error al realizar el cierre");
      }
    } catch (error) {
      console.error("Error:", error);
      swal({
        title: "Error",
        text: error.message || "Error al realizar el cierre de caja",
        icon: "error",
        button: "Aceptar",
      });
    }
  }

  /**
   * Obtener historial de cierres
   */
  async obtenerHistorial() {
    try {
      const formData = new FormData();
      formData.append("limite", "30");

      const response = await fetch(
        "../controllers/cierre_caja/historialCierresController.php",
        {
          method: "POST",
          body: formData,
        },
      );

      // Verificar respuesta y manejar errores de PHP
      if (!response.ok) {
        throw new Error(`Error HTTP: ${response.status}`);
      }

      const responseText = await response.text();

      if (responseText.includes("<br />")) {
        console.error("PHP Warnings/Errors detected:", responseText);
        const jsonMatch = responseText.match(/\{.*\}$/);
        if (jsonMatch) {
          var data = JSON.parse(jsonMatch[0]);
        } else {
          console.error("No se pudo extraer JSON del historial");
          return;
        }
      } else {
        var data = JSON.parse(responseText);
      }

      if (data.success) {
        this.actualizarTablaHistorial(data.historial || []);
      }
    } catch (error) {
      console.error("Error al obtener historial:", error);
    }
  }

  /**
   * Actualizar tabla de historial
   */
  actualizarTablaHistorial(historial) {
    const tbody = document.getElementById("tbodyHistorial");
    tbody.innerHTML = "";

    if (historial.length === 0) {
      tbody.innerHTML =
        '<tr><td colspan="7" class="text-center text-muted">No hay cierres registrados</td></tr>';
      return;
    }

    historial.forEach((cierre) => {
      const fecha = new Date(cierre.cierre_fecha).toLocaleDateString("es-ES");
      const totalVentas =
        parseFloat(cierre.cierre_ingresos_efectivo || 0) +
        parseFloat(cierre.cierre_ingresos_transferencia || 0) +
        parseFloat(cierre.cierre_ingresos_cheque || 0) +
        parseFloat(cierre.cierre_ingresos_otros || 0);

      const row = `
        <tr>
          <td class="text-center">${fecha}</td>
          <td class="text-center">${cierre.cierre_hora}</td>
          <td>${cierre.usuario_nombres}</td>
          <td class="text-right">$${parseFloat(cierre.cierre_saldo_inicial || 0).toFixed(2)}</td>
          <td class="text-right">$${totalVentas.toFixed(2)}</td>
          <td class="text-right">
            <strong>$${parseFloat(cierre.cierre_saldo_final || 0).toFixed(2)}</strong>
          </td>
          <td class="text-center">
            <span class="label label-success">
              <i class="fa fa-check"></i> Cerrado
            </span>
          </td>
        </tr>
      `;
      tbody.innerHTML += row;
    });
  }

  /**
   * Limpiar datos de la interfaz
   */
  limpiarDatos() {
    document.getElementById("resumenDia").style.display = "none";
    document.getElementById("mensajeSinDatos").style.display = "block";
    document.getElementById("seccionDetalles").style.display = "none";

    // Resetear botón al estado original
    const btnRealizarCierre = document.getElementById("btnRealizarCierre");
    btnRealizarCierre.disabled = true;
    btnRealizarCierre.innerHTML = '<i class="fa fa-save"></i> Realizar Cierre';
    btnRealizarCierre.classList.remove("btn-default");
    btnRealizarCierre.classList.add("btn-success");

    this.datosActuales = null;
    this.fechaActual = null;
  }

  /**   * Actualizar estado sin mostrar alertas adicionales
   */
  actualizarEstadoSilencioso() {
    // Marcar que ya existe un cierre para evitar alertas
    if (this.datosActuales) {
      this.datosActuales.cierre_existe = true;
    }

    // Actualizar botón para mostrar estado "Ya Cerrado"
    const btnRealizarCierre = document.getElementById("btnRealizarCierre");
    btnRealizarCierre.disabled = true;
    btnRealizarCierre.innerHTML = '<i class="fa fa-check"></i> Ya Cerrado';
    btnRealizarCierre.classList.remove("btn-success");
    btnRealizarCierre.classList.add("btn-default");
  }

  /**   * Limpiar formulario completo
   */
  limpiarFormulario() {
    document.getElementById("fecha").value = new Date()
      .toISOString()
      .split("T")[0];
    document.getElementById("observaciones").value = "";
    this.limpiarDatos();
  }

  /**
   * Exportar reporte del día (funcionalidad adicional)
   */
  exportarReporte() {
    if (!this.datosActuales) {
      swal({
        title: "Error",
        text: "No hay datos para exportar",
        icon: "warning",
        button: "Aceptar",
      });
      return;
    }

    // Implementar exportación a PDF o Excel
    console.log("Exportando reporte...", this.datosActuales);
  }
}

// Instancia global
const cierreCaja = new CierreCaja();

// Funciones globales para compatibilidad
window.cierreCaja = cierreCaja;
