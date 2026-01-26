const app = new (function () {
  this.guardar = () => {
    if (this.validacionInput() === false) {
      return;
    }
    if (this.validacionCsv() === false) {
      return;
    }
    const file = document.getElementById("file").files[0];
    const form = new FormData();
    form.append("file", file);

    // Mostrar loading
    swal({
      title: "Procesando...",
      text: "Por favor espere mientras se cargan los productos.",
      icon: "info",
      buttons: false,
      closeOnClickOutside: false,
      closeOnEsc: false,
    });

    fetch("../controllers/producto/productoGuardarMasivoController.php", {
      method: "POST",
      body: form,
    })
      .then((res) => res.json())
      .then((data) => {
        console.log(data);
        swal.close(); // Cerrar el loading

        if (data.status === "1") {
          swal({
            title: "Registro exitoso",
            text: data.message,
            icon: "success",
            button: "Aceptar",
          }).then(() => {
            // Limpiar el formulario
            document.getElementById("file").value = "";
          });
        }
        if (data.status === "2") {
          swal({
            title: "Advertencia",
            text: data.message,
            icon: "warning",
            button: "Aceptar",
          });
        }
        if (data.status === "3") {
          swal({
            title: "Error de validación",
            text: data.message,
            icon: "warning",
            button: "Aceptar",
          });
        }
        if (data.status === "4") {
          swal({
            title: "Error de datos",
            text: data.message,
            icon: "warning",
            button: "Aceptar",
          });
        }
        if (data.status === "0") {
          swal({
            title: "Error",
            text: data.message,
            icon: "error",
            button: "Aceptar",
          });
        }
      })
      .catch((err) => {
        console.log(err);
        swal.close();
        swal({
          title: "Error",
          text: "Error al procesar la solicitud. Intente nuevamente.",
          icon: "error",
          button: "Aceptar",
        });
      });
  };

  this.validacionInput = () => {
    const file = document.querySelector("input[name='file']");
    if (file.files.length === 0) {
      swal({
        title: "Error",
        text: "Seleccione un archivo.",
        icon: "warning",
        button: "Aceptar",
      });
      return false;
    }
  };

  this.validacionCsv = () => {
    const file1 = file.files[0];
    const allowedExtensions = ["csv"];
    const allowedMimeTypes = ["text/csv", "application/vnd.ms-excel"];

    // Obtener la extensión del archivo
    const fileName = file1.name;
    const fileExtension = fileName.split(".").pop().toLowerCase();

    // Validar extensión y tipo MIME
    if (
      !allowedExtensions.includes(fileExtension) ||
      !allowedMimeTypes.includes(file1.type)
    ) {
      swal({
        title: "Error",
        text: "Formato de archivo no válido. Solo se permiten archivos CSV.",
        icon: "warning",
        button: "Aceptar",
      });
      return false;
    }
  };
})();
