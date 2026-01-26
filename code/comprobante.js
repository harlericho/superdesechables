const app = new function () {
    this.comprobante = document.getElementById('tbody');
    this.descripcion = document.getElementsByName('descripcion')[0];

    this.listado = () => {
        fetch('../controllers/comprobante/comprobanteListadoController.php', {
            method: 'GET',
        })
            .then(res => res.json())
            .then((data) => {
                if (data.length > 0) {
                    html = []
                    data.forEach(element => {
                        html += "<tr>"
                        html += "<td><strong>" + element.tipo_comp_descripcion + "</strong></td>"
                        if (element.tipo_comp_estado == '1') {
                            html += "<td><span class='badge bg-green'>Activo</span></td>"
                        } else {
                            html += "<td><span class='badge bg-red' onClick='app.activar(" + element.tipo_comp_id + "," + element.tipo_comp_estado + ")'>Inactivo</span></td>"
                        }
                        html += "</tr>"
                    })
                    this.comprobante.innerHTML = html
                }else{
                    this.comprobante.innerHTML = "<tr><td colspan='2'>No hay comprobantes registrados</td></tr>"
                }
            })
            .catch(err => console.log(err));
    }
    this.guardar = () => {
        var form = new FormData();
        form.append('descripcion', this.descripcion.value.toUpperCase());
        fetch('../controllers/comprobante/comprobanteGuardarController.php', {
            method: 'POST',
            body: form
        })
            .then(res => res.json())
            .then((data) => {
                if (data === 1) {
                    swal({
                        title: "Registro exitoso",
                        text: "Comprobante registrado exitosamente",
                        icon: "success",
                        button: "Aceptar",
                    });
                    this.limpiar();
                    this.listado();
                } else if (data === 2) {
                    swal({
                        title: "Error",
                        text: "El comprobante ya existe",
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
}
app.listado();