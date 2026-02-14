const app = new (function () {
  this.email = document.getElementById("emailRecuperar");

  this.recuperarPassword = () => {
    // Validar que el email no esté vacío
    if (!this.email.value.trim()) {
      swal({
        title: "Error",
        text: "Por favor ingresa tu correo electrónico",
        icon: "warning",
        button: "Aceptar",
      });
      this.email.focus();
      return;
    }

    // Mostrar indicador de carga
    swal({
      title: "Enviando solicitud...",
      text: "Por favor espera",
      icon: "info",
      buttons: false,
      closeOnClickOutside: false,
      closeOnEsc: false,
    });

    var form = new FormData();
    form.append("email", this.email.value.toLowerCase().trim());

    fetch("../controllers/login/recuperarPasswordController.php", {
      method: "POST",
      body: form,
    })
      .then((res) => res.json())
      .then((data) => {
        console.log(data);

        if (data.status === "success") {
          swal({
            title: "Solicitud Enviada",
            text: data.message,
            icon: "success",
            button: "Aceptar",
          }).then(() => {
            // Redirigir al login después de 2 segundos
            setTimeout(() => {
              window.location.href = "login.php";
            }, 1000);
          });
        } else if (data.status === "error") {
          swal({
            title: "Error",
            text: data.message,
            icon: "error",
            button: "Aceptar",
          });
          this.email.focus();
        } else if (data.status === "not_found") {
          swal({
            title: "Usuario no encontrado",
            text: data.message,
            icon: "warning",
            button: "Aceptar",
          });
          this.email.focus();
        } else {
          swal({
            title: "Error",
            text: "Ocurrió un error inesperado. Por favor intenta nuevamente.",
            icon: "error",
            button: "Aceptar",
          });
        }
      })
      .catch((err) => {
        console.error("Error:", err);
        swal({
          title: "Error de Conexión",
          text: "No se pudo conectar con el servidor. Por favor verifica tu conexión e intenta nuevamente.",
          icon: "error",
          button: "Aceptar",
        });
      });
  };
})();
