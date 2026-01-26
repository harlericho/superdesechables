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
    fetch("../controllers/categoria/categoriaGuardarMasivoController.php", {
      method: "POST",
      body: form,
    })
      .then((res) => res.json())
      .then((data) => {
        console.log(data);
        if (data.status === "1") {
          swal({
            title: "Registro exitoso",
            text: data.message,
            icon: "success",
            button: "Aceptar",
          });
        }
        if (data.status === "2") {
          swal({
            title: "Error",
            text: data.message,
            icon: "warning",
            button: "Aceptar",
          });
        }
        if (data.status === "0") {
          swal({
            title: "Error",
            text: data.message,
            icon: "warning",
            button: "Aceptar",
          });
        }
      })
      .catch((err) => console.log(err));
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
        text: "Formato de archivo no válido.",
        icon: "warning",
        button: "Aceptar",
      });
      return false;
    }
  };
})();
