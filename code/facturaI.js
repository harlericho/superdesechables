const app = new function () {
    this.facturasInactivas = document.getElementById('tbody');
    this.listado = () => {
        fetch('../controllers/factura/facturaListadoIController.php', {
            method: 'GET',
        })
            .then(res => res.json())
            .then((data) => {
                if (data.length > 0) {
                    html = "<table class='table table-bordered text-center' id='example1'>"
                    html += "<thead>"
                    html += "<tr>"
                    html += "<th>Usuario facturado</th><th>Dni</th><th>Cliente(s)</th><th>Forma pago</th><th>Número factura</th><th>Fecha</th><th>Subtotal</th><th>Impuesto %</th><th>Total</th><th>Estado</th>"
                    html += "</tr>"
                    html += "</thead>"
                    html += "<tbody>"
                    data.forEach(element => {
                        html += "<tr>"
                        html += "<td> <i>" + element.usuario_nombres + "</i></td>"
                        html += "<td> <strong>" + element.cliente_dni + "</strong></td>"
                        html += "<td> <strong>" + element.cliente_apellidos + " " + element.cliente_nombres + "</strong></td>"
                        html += "<td> <strong>" + element.tipo_comp_descripcion + "</strong></td>"
                        html += "<td> <b>" + element.factura_num_comprobante + "</b></td>"
                        html += "<td class='text-blue'><strong>" + element.factura_fecha + "</strong></td>"
                        html += "<td class='text-yellow'> <b>" + element.factura_subtotal + "</b></td>"
                        html += "<td class='text-red'>" + parseInt(element.factura_impuesto) + "</td>"
                        html += "<td class='text-green'> <b>" + element.factura_total + "</b></td>"
                        html += "<td><span class='badge bg-red'>Inactivo</span></td>"
                    })
                    html += "</tr></tbody></table>"
                    this.facturasInactivas.innerHTML = html
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
                    this.facturasInactivas.innerHTML = "<tr><td colspan='9'>No hay facturas registradas</td></tr>"
                }
            })
            .catch(err => console.log(err));
    }
}
app.listado();