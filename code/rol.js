const app = new function () {
    this.roles = document.getElementById('tbody');
    this.descripcion = document.getElementsByName('descripcion')[0];

    this.listado = () => {
        fetch('../controllers/rol/rolListadoController.php', {
            method: 'GET',
        })
            .then(res => res.json())
            .then((data) => {
                if (data.length > 0) {
                    html = []
                    data.forEach(element => {
                        html += "<tr>"
                        html += "<td><strong>" + element.rol_descripcion + "</strong></td>"
                        if (element.rol_estado == '1') {
                            html += "<td><span class='badge bg-green'>Activo</span></td>"
                        } else {
                            html += "<td><span class='badge bg-red' onClick='app.activar(" + element.rol_id + "," + element.rol_estado + ")'>Inactivo</span></td>"
                        }
                        html += "<td><button type='button' class='btn btn-danger btn-sm' title='Eliminar' onClick='app.eliminar(" + element.rol_id + "," + element.rol_estado + ")'><i class= 'fa fa-trash'></i></button></td>"
                        html += "</tr>"
                    })
                    this.roles.innerHTML = html
                } else {
                    this.roles.innerHTML = "<tr><td colspan='3'>No hay roles registrados</td></tr>"
                }
            })
            .catch(err => console.log(err));
    }
    this.guardar = () => {
        var form = new FormData();
        form.append('descripcion', this.descripcion.value.toUpperCase());
        fetch('../controllers/rol/rolGuardarController.php', {
            method: 'POST',
            body: form
        })
            .then(res => res.json())
            .then((data) => {
                if (data === 1) {
                    swal({
                        title: "Registro exitoso",
                        text: "Rol registrado exitosamente",
                        icon: "success",
                        button: "Aceptar",
                    });
                    this.limpiar();
                    this.listado();
                } else if (data === 2) {
                    swal({
                        title: "Error",
                        text: "El rol ya existe",
                        icon: "error",
                        button: "Aceptar",
                    });
                    this.descripcion.focus();
                }
            })
            .catch(err => console.log(err));
    }
    this.limpiar = () => {
        this.descripcion.value = '';
        this.descripcion.focus();
    }
    this.eliminar = (id, estado) => {
        var form = new FormData();
        form.append('id', id);
        swal({
            title: "¿Está seguro de modificar el estado del rol?",
            text: "Solo cambiará el estado del rol",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        })
            .then((willDelete) => {
                if (willDelete) {
                    if (estado !== 0) {
                        fetch('../controllers/rol/rolInactivarController.php', {
                            method: 'POST',
                            body: form
                        })
                            .then(res => res.json())
                            .then((data) => {
                                if (data === 1) {
                                    swal("Rol ha cambiado su estado: inactivo", {
                                        icon: "success",
                                    });
                                    this.listado();
                                }
                            })
                            .catch(err => console.log(err));
                    } else {
                        swal({
                            title: "Eliminación fallida",
                            text: "Rol ya esta con estado inactivo",
                            icon: "error",
                            button: "Aceptar",
                        })
                    }
                } else {
                    swal("Operación cancelada");
                    return false;
                }
            });
    }
    this.activar = (id, estado) => {
        var form = new FormData();
        form.append('id', id);
        if (estado !== 1) {
            fetch('../controllers/rol/rolActivarController.php', {
                method: 'POST',
                body: form
            })
                .then(res => res.json())
                .then((data) => {
                    if (data === 1) {
                        swal({
                            title: "Activación exitosa",
                            text: "Rol ha cambiado su estado: activo",
                            icon: "success",
                            button: "Aceptar",
                        })
                        this.listado();
                    }
                })
                .catch(err => console.log(err));
        } else {
            swal({
                title: "Activación fallida",
                text: "Rol ya esta con estado activo",
                icon: "warning",
                button: "Aceptar",
            })
        }
    }
}
app.listado();