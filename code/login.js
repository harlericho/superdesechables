const app = new (function () {
  this.email = document.getElementsByName("email")[0];
  this.password = document.getElementsByName("password")[0];

  this.login = () => {
    var form = new FormData();
    form.append("email", this.email.value.toLowerCase());
    form.append("password", this.password.value);
    fetch("../controllers/login/loginController.php", {
      method: "POST",
      body: form,
    })
      .then((res) => res.json())
      .then((data) => {
        console.log(data);
        if (data === 1) {
          swal({
            title: "Ingreso exitoso",
            text: "Bienvenido al sistema",
            icon: "success",
            button: "Aceptar",
          }).then(() => {
            window.location.href = "../views/index.php";
          });
        } else if (data === 2) {
          swal({
            title: "Error",
            text: "El email o la contraseña son incorrectos",
            icon: "error",
            button: "Aceptar",
          });
          this.email.focus();
        } else if (data === 3) {
          swal({
            title: "Error",
            text: "El usuario no esta activo",
            icon: "error",
            button: "Aceptar",
          });
          this.email.focus();
        }
      })
      .catch((err) => console.log(err));
  };
})();
