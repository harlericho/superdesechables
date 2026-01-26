const app = new function () {
    this.usuarios = document.getElementById('tbody');
    this.selectorRol = document.getElementById('selectorRol');
    this.id = document.getElementsByName('id')[0];
    this.nombres = document.getElementsByName('nombres')[0];
    this.email = document.getElementsByName('email')[0];
    this.password = document.getElementsByName('password')[0];

    this.listado = () => {
        fetch('../controllers/usuario/usuarioListadoController.php', {
            method: 'GET',
        })
            .then(res => res.json())
            .then((data) => {
                if (data !== null) {
                    html = "<table class='table table-bordered text-center' id='example1'>"
                    html += "<thead>"
                    html += "<tr>"
                    html += "<th>Rol</th><th>Nombre(s)</th><th>Email</th><th>Password</th><th>Estado</th><th style='width: 40px' colpsan='2'>Accciones</th>"
                    html += "</tr>"
                    html += "</thead>"
                    html += "<tbody>"
                    data.forEach(element => {
                        html += "<tr>"
                        html += "<td> <strong>" + element.descripcion + "</strong></td>"
                        html += "<td>" + element.nombres + "</td>"
                        html += "<td>" + element.email + "</td>"
                        html += "<td> <b>" + element.password + "</b></td>"
                        if (element.estado == '1') {
                            html += "<td><span class='badge bg-green'>Activo</span></td>"
                        } else {
                            html += "<td><span class='badge bg-red' onClick='app.activar(" + element.id + "," + element.estado + ")'>Inactivo</span></td>"
                        }
                        html += "<td><button type='button' class='btn btn-danger btn-sm' style='margin-right: 5%;' title='Eliminar' onClick='app.eliminar(" + element.id + "," + element.estado + ")'><i class= 'fa fa-trash'></i></button><button type='button' class='btn btn-info btn-sm' title='Editar' onClick='app.editar(" + element.id + ")'><i class= 'fa fa-pencil'></i></button></td>"
                        // html += "</tbody>"
                    })
                    html += "</tr></tbody></table>"
                    this.usuarios.innerHTML = html
                    // todo : Inicializar variables datatable
                    $('#example1').DataTable({
                        "language": {
                            "lengthMenu": "Mostrar _MENU_ registros por página",
                            "zeroRecords": "No se encontraron resultados en su búsqueda",
                            "searchPlaceholder": "Buscar registros",
                            "search": "Buscar:",
                            "info": "Mostrando registros de _START_ al _END_ de un total de  _TOTAL_ registros",
                            "infoEmpty": "No existen registros",
                            "infoFiltered": "(filtrado de un total de _MAX_ registros)",
                            "loadingRecords": "Cargando...",
                            "processing": "Procesando...",
                            "paginate": {
                                "first": "Primero",
                                "last": "Último",
                                "next": "Siguiente",
                            }
                        }

                    })
                } else {
                    this.usuarios.innerHTML = "<tr><td colspan='6'>No hay usuarios registrados</td></tr>"
                }
            })
            .catch(err => console.log(err));
    }
    this.guardar = () => {
        var form = new FormData(document.getElementById('formUsuario'));
        if (this.id.value === '') {
            if (form.get('rol') !== null) {
                fetch('../controllers/usuario/usuarioGuardarController.php', {
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
                            this.listado();
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
            } else {
                swal({
                    title: "Atención",
                    text: "Elegir un rol",
                    icon: "warning",
                    button: "Aceptar",
                });
                return;
            }

        } else {
            fetch('../controllers/usuario/usuarioActualizarController.php', {
                method: 'POST',
                body: form
            })
                .then(res => res.json())
                .then((data) => {
                    if (data === 1) {
                        swal({
                            title: "Actualización exitosa",
                            text: "Usuario actualizado exitosamente",
                            icon: "success",
                            button: "Aceptar",
                        });
                        this.limpiar();
                        this.listado();
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
        $('#formUsuario')[0].reset();
        $("#rol").val("Seleccione").trigger("change");
        this.id.value = '';
    }
    this.eliminar = (id, estado) => {
        var form = new FormData();
        form.append('id', id);
        swal({
            title: "¿Está seguro de modificar el estado del usuario?",
            text: "Solo cambiará el estado del usuario",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        })
            .then((willDelete) => {
                if (willDelete) {
                    if (estado !== 0) {
                        fetch('../controllers/usuario/usuarioInactivarController.php', {
                            method: 'POST',
                            body: form
                        })
                            .then(res => res.json())
                            .then((data) => {
                                if (data === 1) {
                                    swal("Usuario ha cambiado su estado: inactivo", {
                                        icon: "success",
                                    });
                                    this.listado();
                                }
                            })
                            .catch(err => console.log(err));
                    } else {
                        swal({
                            title: "Eliminación fallida",
                            text: "Usuario ya esta con estado inactivo",
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
            fetch('../controllers/usuario/usuarioActivarController.php', {
                method: 'POST',
                body: form
            })
                .then(res => res.json())
                .then((data) => {
                    if (data === 1) {
                        swal({
                            title: "Activación exitosa",
                            text: "Usuario ha cambiado su estado: activo",
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
                text: "Usuario ya esta con estado activo",
                icon: "warning",
                button: "Aceptar",
            })
        }
    }
    this.roles = () => {
        fetch('../controllers/rol/rolListadoController.php', {
            method: 'GET',
        })
            .then(res => res.json())
            .then((data) => {
                // html = []
                html = "<select class='form-control select2' style='width: 100%;' name='rol' id='rol' autofocus required>"
                html += "<option disabled='selected' selected='selected'>Seleccione</option>"
                data.forEach(element => {
                    if (element.rol_estado != '0') {
                        if (element.rol_descripcion === 'EMPLEADO' || element.rol_descripcion === 'ADMINISTRADOR') {
                            html += "<option value='" + element.rol_id + "'>" + element.rol_descripcion + "</option>"
                        } else {
                            html += "<option value='" + element.rol_id + "' disabled >" + element.rol_descripcion + "</option>"
                        }
                    }
                })
                html += "</select>"
                this.selectorRol.innerHTML = html
                // todo : Inicializar variables select roles
                $('.select2').select2()
            })
            .catch(err => console.log(err));
    }
    this.editar = (id) => {
        var form = new FormData();
        form.append('id', id);
        fetch('../controllers/usuario/usuarioEditarController.php', {
            method: 'POST',
            body: form
        })
            .then(res => res.json())
            .then((data) => {
                this.nombres.value = data[0]['nombres'];
                this.email.value = data[0]['email'];
                this.password.value = data[0]['password'];
                this.id.value = data[0]['id'];
                $('#rol').val(data[0]['rol']).trigger('change');
                swal("Atención!", "Esta en el modo actualizar datos", "warning");
            })
            .catch(err => console.log(err));
    }
}
app.listado();
app.roles();