const app = new (function () {
  this.cargar = () => {
    fetch("../controllers/configserie/configImpresoraObtenerController.php", {
      method: "GET",
    })
      .then((res) => res.json())
      .then((data) => {
        const nombre = data.impresora || "XP-80C";
        document.getElementById("impresoraActual").textContent = nombre;
        document.getElementById("impresora_ticket").value = nombre;
      })
      .catch((err) => console.log(err));
  };

  this.guardar = () => {
    const form = new FormData(document.getElementById("formImpresora"));
    fetch("../controllers/configserie/configImpresoraGuardarController.php", {
      method: "POST",
      body: form,
    })
      .then((res) => res.json())
      .then((data) => {
        const msg = document.getElementById("impresoraMsg");
        if (data.status === 1) {
          msg.className = "text-success";
          msg.textContent = "✔ " + data.message;
          this.cargar();
        } else {
          msg.className = "text-danger";
          msg.textContent = "✖ " + (data.message || "Error al guardar");
        }
        setTimeout(() => {
          msg.textContent = "";
        }, 3000);
      })
      .catch((err) => {
        console.log(err);
        const msg = document.getElementById("impresoraMsg");
        msg.className = "text-danger";
        msg.textContent = "✖ Error de conexión";
      });
  };

  this.listarImpresoras = () => {
    const contenedor = document.getElementById("listaImpresoras");

    function mostrarLista() {
      qz.printers
        .find()
        .then((printers) => {
          if (!printers || printers.length === 0) {
            contenedor.innerHTML =
              '<p class="text-muted">No se encontraron impresoras.</p>';
            return;
          }
          let html = "<ul class='list-group'>";
          printers.forEach((p) => {
            html +=
              "<li class='list-group-item'>" +
              "<i class='fa fa-print'></i> " +
              p +
              " <button type='button' class='btn btn-xs btn-default pull-right' " +
              "onclick=\"document.getElementById('impresora_ticket').value='" +
              p.replace(/'/g, "\\'") +
              "'\" title='Usar esta impresora'>" +
              "<i class='fa fa-check'></i> Seleccionar" +
              "</button></li>";
          });
          html += "</ul>";
          contenedor.innerHTML = html;
        })
        .catch((err) => {
          contenedor.innerHTML =
            "<p class='text-warning'><i class='fa fa-exclamation-triangle'></i> " +
            "No se pudo obtener la lista: asegúrese de que QZ Tray esté corriendo.</p>";
          console.log(err);
        });
    }

    if (!qz.websocket.isActive()) {
      qz.websocket
        .connect()
        .then(mostrarLista)
        .catch(() => {
          contenedor.innerHTML =
            "<p class='text-warning'><i class='fa fa-exclamation-triangle'></i> " +
            "QZ Tray no está activo. Inicie QZ Tray para ver la lista de impresoras.</p>";
        });
    } else {
      mostrarLista();
    }
  };
})();

app.cargar();
app.listarImpresoras();
