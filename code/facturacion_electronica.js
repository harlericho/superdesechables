/**
 * JavaScript para Facturación Electrónica
 * Maneja la configuración y pruebas del módulo FE
 */

$(document).ready(function () {
  // Cargar configuración existente
  cargarConfiguracion();

  // Guardar datos del emisor
  $("#formDatosEmisor").on("submit", function (e) {
    e.preventDefault();
    guardarDatosEmisor();
  });

  // Subir certificado
  $("#formCertificado").on("submit", function (e) {
    e.preventDefault();
    subirCertificado();
  });

  // Guardar configuración
  $("#formConfiguracion").on("submit", function (e) {
    e.preventDefault();
    guardarConfiguracion();
  });

  // Validar certificado
  $("#btnValidarCertificado").on("click", function () {
    validarCertificado();
  });

  // Pruebas
  $("#btnProbarConexionSRI").on("click", probarConexionSRI);
  $("#btnGenerarXMLPrueba").on("click", generarXMLPrueba);
  $("#btnEnviarFacturaPrueba").on("click", enviarFacturaPrueba);
});

/**
 * Cargar configuración existente
 */
function cargarConfiguracion() {
  $.ajax({
    url: "../controllers/facturacion_electronica/feConfigObtenerController.php",
    method: "GET",
    dataType: "json",
    success: function (response) {
      if (response.status === 1 && response.data) {
        const config = response.data;

        // Llenar formulario de datos del emisor
        $("#config_fe_id").val(config.config_fe_id);
        $("#config_fe_ruc").val(config.config_fe_ruc);
        $("#config_fe_razon_social").val(config.config_fe_razon_social);
        $("#config_fe_nombre_comercial").val(config.config_fe_nombre_comercial);
        $("#config_fe_direccion_matriz").val(config.config_fe_direccion_matriz);
        $("#config_fe_direccion_sucursal").val(
          config.config_fe_direccion_sucursal,
        );
        $("#config_fe_obligado_contabilidad").val(
          config.config_fe_obligado_contabilidad,
        );
        $("#config_fe_contribuyente_rimpe").val(
          config.config_fe_contribuyente_rimpe,
        );

        // Certificado
        if (config.config_fe_certificado_nombre) {
          $("#certificado-actual").show();
          $("#cert-nombre").text(config.config_fe_certificado_nombre);
          $("#cert-caducidad").text(
            config.config_fe_certificado_fecha_caducidad || "No especificada",
          );
          $("#certificado_archivo").prop("required", false);
        }

        $("#config_fe_certificado_fecha_caducidad").val(
          config.config_fe_certificado_fecha_caducidad,
        );

        // Configuración
        $(
          'input[name="config_fe_ambiente"][value="' +
            config.config_fe_ambiente +
            '"]',
        ).prop("checked", true);
        $(
          'input[name="config_fe_tipo_emision"][value="' +
            config.config_fe_tipo_emision +
            '"]',
        ).prop("checked", true);
        $("#config_fe_email_envio").val(config.config_fe_email_envio);
        $("#config_fe_email_copia").val(config.config_fe_email_copia);
        $("#config_fe_enviar_email_automatico").prop(
          "checked",
          config.config_fe_enviar_email_automatico == 1,
        );
        $("#config_fe_activo").prop("checked", config.config_fe_activo == 1);
        $("#config_fe_permitir_ventas_simples").prop("checked", config.config_fe_permitir_ventas_simples == 1);
      }
    },
    error: function () {
      console.log("No se pudo cargar la configuración");
    },
  });
}

/**
 * Guardar datos del emisor
 */
function guardarDatosEmisor() {
  const formData = $("#formDatosEmisor").serialize();

  $.ajax({
    url: "../controllers/facturacion_electronica/feConfigGuardarController.php",
    method: "POST",
    data: formData,
    dataType: "json",
    success: function (response) {
      if (response.status === 1) {
        swal({
          title: "¡Éxito!",
          text: "Datos del emisor guardados correctamente",
          icon: "success",
          timer: 1500,
          button: false,
        });
        cargarConfiguracion();
      } else {
        swal({
          title: "Error",
          text: response.message || "No se pudo guardar la configuración",
          icon: "error",
        });
      }
    },
    error: function () {
      swal({
        title: "Error",
        text: "Error al comunicarse con el servidor",
        icon: "error",
      });
    },
  });
}

/**
 * Subir certificado digital
 */
function subirCertificado() {
  const formData = new FormData($("#formCertificado")[0]);

  $.ajax({
    url: "../controllers/facturacion_electronica/feCertificadoSubirController.php",
    method: "POST",
    data: formData,
    processData: false,
    contentType: false,
    dataType: "json",
    success: function (response) {
      if (response.status === 1) {
        swal({
          title: "¡Éxito!",
          text: "Certificado cargado correctamente",
          icon: "success",
          timer: 1500,
          button: false,
        });
        cargarConfiguracion();
      } else {
        swal({
          title: "Error",
          text: response.message || "No se pudo cargar el certificado",
          icon: "error",
        });
      }
    },
    error: function () {
      swal({
        title: "Error",
        text: "Error al subir el certificado",
        icon: "error",
      });
    },
  });
}

/**
 * Guardar configuración general
 */
function guardarConfiguracion() {
  const formData = $("#formConfiguracion").serialize();

  $.ajax({
    url: "../controllers/facturacion_electronica/feConfigActualizarController.php",
    method: "POST",
    data: formData,
    dataType: "json",
    success: function (response) {
      if (response.status === 1) {
        swal({
          title: "¡Éxito!",
          text: "Configuración guardada correctamente",
          icon: "success",
          timer: 1500,
          button: false,
        });
      } else {
        swal({
          title: "Error",
          text: response.message || "No se pudo guardar la configuración",
          icon: "error",
        });
      }
    },
    error: function () {
      swal({
        title: "Error",
        text: "Error al comunicarse con el servidor",
        icon: "error",
      });
    },
  });
}

/**
 * Validar certificado digital
 */
function validarCertificado() {
  $.ajax({
    url: "../controllers/facturacion_electronica/feValidarCertificadoController.php",
    method: "POST",
    dataType: "json",
    success: function (response) {
      if (response.status === 1) {
        let mensaje = "Emisor: " + response.data.emisor + "\n";
        mensaje += "Válido desde: " + response.data.valido_desde + "\n";
        mensaje += "Válido hasta: " + response.data.valido_hasta + "\n";
        mensaje += "Días restantes: " + response.data.dias_restantes;
        swal({
          title: "Certificado Válido",
          text: mensaje,
          icon: "success",
        });
      } else {
        swal({
          title: "Certificado Inválido",
          text: response.message,
          icon: "error",
        });
      }
    },
    error: function () {
      swal({
        title: "Error",
        text: "No se pudo validar el certificado",
        icon: "error",
      });
    },
  });
}

/**
 * Probar conexión con SRI
 */
function probarConexionSRI() {
  $("#resultado-pruebas").text("Probando conexión con el SRI...\n");

  $.ajax({
    url: "../controllers/facturacion_electronica/feProbarConexionController.php",
    method: "POST",
    dataType: "json",
    success: function (response) {
      let resultado = "";

      if (response.status === 1) {
        resultado += "✓ Conexión exitosa con el SRI\n\n";
        resultado += "Ambiente: " + response.data.ambiente + "\n";
        resultado += "URL Recepción: " + response.data.url_recepcion + "\n";
        resultado +=
          "URL Autorización: " + response.data.url_autorizacion + "\n";
        resultado +=
          "Estado del servicio: " + response.data.estado_servicio + "\n";
      } else {
        resultado += "✗ Error en la conexión\n\n";
        resultado += "Mensaje: " + response.message + "\n";
      }

      $("#resultado-pruebas").text(resultado);
    },
    error: function () {
      $("#resultado-pruebas").text("✗ Error al conectar con el servidor\n");
    },
  });
}

/**
 * Generar XML de prueba
 */
function generarXMLPrueba() {
  $("#resultado-pruebas").text("Generando XML de prueba...\n");

  $.ajax({
    url: "../controllers/facturacion_electronica/feGenerarXMLPruebaController.php",
    method: "POST",
    dataType: "json",
    success: function (response) {
      if (response.status === 1) {
        $("#resultado-pruebas").text(response.data.xml);

        swal({
          title: "XML Generado",
          text: "El XML de prueba se generó correctamente",
          icon: "success",
        });
      } else {
        $("#resultado-pruebas").text(
          "✗ Error al generar XML\n\n" + response.message,
        );
      }
    },
    error: function () {
      $("#resultado-pruebas").text("✗ Error al generar XML de prueba\n");
    },
  });
}

/**
 * Enviar factura de prueba al SRI
 */
function enviarFacturaPrueba() {
  swal({
    title: "¿Enviar factura de prueba?",
    text: "Se generará y enviará una factura de prueba al SRI",
    icon: "warning",
    buttons: ["Cancelar", "Sí, enviar"],
    dangerMode: false,
  }).then((willSend) => {
    if (willSend) {
      $("#resultado-pruebas").text(
        "Generando y enviando factura de prueba al SRI...\n",
      );

      $.ajax({
        url: "../controllers/facturacion_electronica/feEnviarPruebaController.php",
        method: "POST",
        dataType: "json",
        success: function (response) {
          let resultado = "";

          if (response.status === 1) {
            resultado += "✓ Factura de prueba procesada exitosamente\n\n";
            resultado +=
              "Clave de acceso: " + response.data.clave_acceso + "\n";
            resultado += "Estado SRI: " + response.data.estado_sri + "\n";
            resultado +=
              "Número autorización: " +
              response.data.numero_autorizacion +
              "\n";
            resultado +=
              "Fecha autorización: " + response.data.fecha_autorizacion + "\n";
            resultado += "\nMensaje del SRI:\n" + response.data.mensaje_sri;

            swal({
              title: "¡Prueba Exitosa!",
              text: "La factura de prueba fue autorizada por el SRI",
              icon: "success",
            });
          } else {
            resultado += "✗ Error en el proceso\n\n";
            resultado += "Mensaje: " + response.message + "\n";

            if (response.data && response.data.detalles) {
              resultado += "\nDetalles:\n" + response.data.detalles;
            }

            swal({
              title: "Error en la Prueba",
              text: response.message,
              icon: "error",
            });
          }

          $("#resultado-pruebas").text(resultado);
        },
        error: function () {
          $("#resultado-pruebas").text(
            "✗ Error al comunicarse con el servidor\n",
          );
        },
      });
    }
  });
}
