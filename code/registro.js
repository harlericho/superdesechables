const app = new function () {
    this.nombres = document.getElementsByName('nombres')[0];
    this.email = document.getElementsByName('email')[0];
    this.password = document.getElementsByName('password')[0];
    this.password2 = document.getElementsByName('password2')[0];

    this.registro = () => {
        var form = new FormData();
        form.append('nombres', this.nombres.value);
        form.append('email', this.email.value);
        form.append('password', this.password.value);
        form.append('password2', this.password2.value);
        if (this.password.value != this.password2.value) {
            swal({
                title: "Atención",
                text: "Las contraseñas no coinciden",
                icon: "warning",
                button: "Aceptar",
            });
            this.password2.focus();
        } else {
            fetch('../controllers/usuarioRegistroController.php', {
                method: 'POST',
                body: form
            })
                .then(res => res.json())
                .then((data) => {
                    if (data === 1) {
                        swal({
                            title: "Registro exitoso",
                            text: "Usuario registrado exitosamente",
                            icon: "success",
                            button: "Aceptar",
                        });
                        this.limpiar();
                    } else if (data === 2) {
                        swal({
                            title: "Error",
                            text: "El email ya existe",
                            icon: "error",
                            button: "Aceptar",
                        });
                        this.email.focus();
                    }
                })
                .catch(err => console.log(err));
        }
    }
    this.limpiar = () => {
        this.nombres.value = '';
        this.email.value = '';
        this.password.value = '';
        this.password2.value = '';
        this.nombres.focus();
    }
}