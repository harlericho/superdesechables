/**
 * Control de sesión - Monitoreo de expiración
 * Verifica periódicamente el estado de la sesión y alerta al usuario
 */

$(document).ready(function () {
  // console.log("Script de verificación de sesión iniciado");

  let CHECK_INTERVAL = 120000; // Valor por defecto: 2 minutos
  let WARNING_TIME = 5; // Valor por defecto: 5 minutos
  let warningShown = false;
  let sessionLifetime = 0;

  function initializeSessionConfig() {
    $.ajax({
      url: "../controllers/main/getSessionConfigController.php",
      type: "GET",
      dataType: "json",
      async: false,
      success: function (response) {
        sessionLifetime = response.lifetime_minutes;

        // Calcular intervalos dinámicamente basados en el tiempo de sesión
        if (sessionLifetime <= 5) {
          // Para sesiones cortas (≤5 min): verificar cada 10 segundos, alertar al 50%
          CHECK_INTERVAL = 10000;
          WARNING_TIME = Math.max(0.5, sessionLifetime * 0.5);
        } else if (sessionLifetime <= 15) {
          // Para sesiones medias (6-15 min): verificar cada 30 segundos, alertar a 3 min
          CHECK_INTERVAL = 30000;
          WARNING_TIME = 3;
        } else {
          // Para sesiones largas (>15 min): verificar cada 2 minutos, alertar a 5 min
          CHECK_INTERVAL = 120000;
          WARNING_TIME = 5;
        }

        // console.log("Configuración de sesión cargada:");
        // console.log("- Duración total:", sessionLifetime, "minutos");
        // console.log("- Verificar cada:", CHECK_INTERVAL / 1000, "segundos");
        // console.log("- Alertar cuando queden:", WARNING_TIME, "minutos");
      },
      error: function () {
        console.warn(
          "No se pudo cargar configuración, usando valores por defecto",
        );
      },
    });
  }

  function checkSessionStatus() {
    // console.log("Verificando estado de sesión...");
    $.ajax({
      url: "../controllers/main/checkSessionController.php",
      type: "GET",
      dataType: "json",
      success: function (response) {
        // console.log("Respuesta del servidor:", response);
        // console.log("Sesión - Tiempo restante:", response.remaining, "minutos");
        // console.log("Sesión - Expirada:", response.expired);
        // console.log("WARNING_TIME configurado:", WARNING_TIME, "minutos");
        // console.log("warningShown actual:", warningShown);

        if (response.expired) {
          // Sesión expirada, redirigir al login
          console.log("¡Sesión expirada! Mostrando alerta...");
          swal({
            title: "Sesión Expirada",
            text: "Su sesión ha expirado por inactividad. Por favor, inicie sesión nuevamente.",
            icon: "warning",
            button: "Aceptar",
            closeOnClickOutside: false,
            closeOnEsc: false,
          }).then(function () {
            window.location.href = "../views/out.php";
          });
        } else if (response.remaining <= WARNING_TIME && !warningShown) {
          // Mostrar advertencia solo UNA VEZ cuando quedan menos minutos que WARNING_TIME
          warningShown = true;
          console.log("Mostrando advertencia de sesión (solo una vez)...");

          // Determinar qué texto mostrar
          let timeText;
          if (response.remaining_seconds < 60) {
            timeText = response.remaining_seconds + " segundos";
          } else if (response.remaining === 1) {
            timeText = "1 minuto";
          } else {
            timeText = response.remaining + " minutos";
          }

          swal({
            title: "Advertencia de Sesión",
            text:
              "Su sesión expirará en " +
              timeText +
              ". Por favor, guarde su trabajo.",
            icon: "info",
            button: "Entendido",
          });
        } else if (response.remaining > WARNING_TIME && warningShown) {
          // Solo resetear si hubo actividad y el tiempo aumentó significativamente
          console.log("Sesión renovada por actividad, reseteando advertencia");
          warningShown = false;
        }
      },
      error: function (xhr, status, error) {
        console.error(
          "Error al verificar el estado de la sesión:",
          status,
          error,
        );
        console.error("Respuesta del servidor:", xhr.responseText);
      },
    });
  }

  // Inicializar configuración desde el servidor
  initializeSessionConfig();

  // Verificar el estado de la sesión periódicamente
  // console.log(
  //   "Iniciando intervalo de verificación cada",
  //   CHECK_INTERVAL / 1000,
  //   "segundos",
  // );
  setInterval(checkSessionStatus, CHECK_INTERVAL);

  // Verificar inmediatamente al cargar
  checkSessionStatus();

  // Nota: No reseteamos warningShown con clicks
  // Solo se resetea cuando el servidor confirme que la sesión se renovó
});
