const app = new (function () {
  this.nombres = document.getElementById("nombres");
  this.email = document.getElementById("email");
  this.rol = document.getElementById("rol");
  this.usuario_nombres = document.getElementById("usuario_nombres");
  this.usuario_email = document.getElementById("usuario_email");
  this.mostrarDatos = () => {
    fetch("../controllers/main/mainPerfilController.php")
      .then((res) => res.json())
      .then((data) => {
        this.nombres.innerHTML = data[0].usuario_nombres;
        this.email.innerHTML = data[0].usuario_email;
        this.rol.innerHTML = data[0].rol_descripcion;
        this.usuario_nombres.value = data[0].usuario_nombres;
        this.usuario_email.value = data[0].usuario_email;
      })
      .catch((err) => console.log(err));
  };
  this.actualizarDatos = () => {
    var form = new FormData();
    form.append("usuario_nombres", this.usuario_nombres.value.toUpperCase());
    fetch("../controllers/main/mainPerfilGuardarController.php", {
      method: "POST",
      body: form,
    })
      .then((res) => res.json())
      .then((data) => {
        if (data === true) {
          swal({
            title: "Actualización exitosa",
            text: "Datos actualizado exitosamente",
            icon: "success",
            button: "Aceptar",
          });
          this.mostrarDatos();
        }
      })
      .catch((err) => console.log(err));
  };
})();
app.mostrarDatos();
