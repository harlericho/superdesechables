const app = new (function () {
  this.guardar = () => {
    // en proceo de desarrollo
    swal({
      title: "En desarrollo",
      text: "La funcionalidad de cierre de caja chica está en desarrollo.",
      icon: "info",
      button: "Aceptar",
    });
  };
})();
