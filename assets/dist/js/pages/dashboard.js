/*
 * Author: Abdullah A Almsaeed
 * Date: 4 Jan 2014
 * Description:
 *      This is a demo file used only for the main dashboard (index.html)
 **/

$(function () {
  "use strict";

  // Make the dashboard widgets sortable Using jquery UI
  $(".connectedSortable").sortable({
    containment: $("section.content"),
    placeholder: "sort-highlight",
    connectWith: ".connectedSortable",
    handle: ".box-header, .nav-tabs",
    forcePlaceholderSize: true,
    zIndex: 999999,
  });
  $(".connectedSortable .box-header, .connectedSortable .nav-tabs-custom").css(
    "cursor",
    "move",
  );

  // jQuery UI sortable for the todo list
  $(".todo-list").sortable({
    placeholder: "sort-highlight",
    handle: ".handle",
    forcePlaceholderSize: true,
    zIndex: 999999,
  });

  // bootstrap WYSIHTML5 - text editor
  $(".textarea").wysihtml5();

  $(".daterange").daterangepicker(
    {
      ranges: {
        Today: [moment(), moment()],
        Yesterday: [moment().subtract(1, "days"), moment().subtract(1, "days")],
        "Last 7 Days": [moment().subtract(6, "days"), moment()],
        "Last 30 Days": [moment().subtract(29, "days"), moment()],
        "This Month": [moment().startOf("month"), moment().endOf("month")],
        "Last Month": [
          moment().subtract(1, "month").startOf("month"),
          moment().subtract(1, "month").endOf("month"),
        ],
      },
      startDate: moment().subtract(29, "days"),
      endDate: moment(),
    },
    function (start, end) {
      window.alert(
        "You chose: " +
          start.format("MMMM D, YYYY") +
          " - " +
          end.format("MMMM D, YYYY"),
      );
    },
  );

  /* jQueryKnob */
  $(".knob").knob();

  // jvectormap data
  var visitorsData = {
    US: 398, // USA
    SA: 400, // Saudi Arabia
    CA: 1000, // Canada
    DE: 500, // Germany
    FR: 760, // France
    CN: 300, // China
    AU: 700, // Australia
    BR: 600, // Brazil
    IN: 800, // India
    GB: 320, // Great Britain
    RU: 3000, // Russia
  };
  // World map by jvectormap
  $("#world-map").vectorMap({
    map: "world_mill_en",
    backgroundColor: "transparent",
    regionStyle: {
      initial: {
        fill: "#e4e4e4",
        "fill-opacity": 1,
        stroke: "none",
        "stroke-width": 0,
        "stroke-opacity": 1,
      },
    },
    series: {
      regions: [
        {
          values: visitorsData,
          scale: ["#92c1dc", "#ebf4f9"],
          normalizeFunction: "polynomial",
        },
      ],
    },
    onRegionLabelShow: function (e, el, code) {
      if (typeof visitorsData[code] != "undefined")
        el.html(el.html() + ": " + visitorsData[code] + " new visitors");
    },
  });

  // Sparkline charts
  var myvalues = [1000, 1200, 920, 927, 931, 1027, 819, 930, 1021];
  $("#sparkline-1").sparkline(myvalues, {
    type: "line",
    lineColor: "#92c1dc",
    fillColor: "#ebf4f9",
    height: "50",
    width: "80",
  });
  myvalues = [515, 519, 520, 522, 652, 810, 370, 627, 319, 630, 921];
  $("#sparkline-2").sparkline(myvalues, {
    type: "line",
    lineColor: "#92c1dc",
    fillColor: "#ebf4f9",
    height: "50",
    width: "80",
  });
  myvalues = [15, 19, 20, 22, 33, 27, 31, 27, 19, 30, 21];
  $("#sparkline-3").sparkline(myvalues, {
    type: "line",
    lineColor: "#92c1dc",
    fillColor: "#ebf4f9",
    height: "50",
    width: "80",
  });

  // The Calendar - Configurado en español con Bootstrap Datepicker
  var diasConVentasData = [];

  function cargarDiasConVentas(mes, anio) {
    fetch(
      "../controllers/main/obtenerDiasConVentasController.php?mes=" +
        mes +
        "&anio=" +
        anio,
    )
      .then((res) => res.json())
      .then((data) => {
        diasConVentasData = data;

        // Forzar actualización del calendario
        setTimeout(function () {
          $("#calendar").datepicker("update");

          // Esperar a que se renderice y luego agregar tooltips
          setTimeout(function () {
            agregarTooltips();
          }, 200);
        }, 100);
      })
      .catch((err) => {});
  }

  function agregarTooltips() {
    // Ya no agregar tooltips, solo dejar los íconos
  }

  $("#calendar")
    .datepicker({
      language: "es",
      todayHighlight: true,
      todayBtn: "linked",
      autoclose: false,
      orientation: "auto",
      beforeShowDay: function (date) {
        var dateStr =
          date.getFullYear() +
          "-" +
          String(date.getMonth() + 1).padStart(2, "0") +
          "-" +
          String(date.getDate()).padStart(2, "0");

        var tienVentas = diasConVentasData.some(
          (item) => item.fecha === dateStr,
        );

        if (tienVentas) {
          return {
            classes: "dia-con-venta",
            tooltip: "Día con ventas",
          };
        }
        return {};
      },
    })
    .on("changeDate", function (e) {
      // Limpiar tooltips al cambiar de fecha/mes/año
      $(".calendar-tooltip").remove();

      setTimeout(function () {
        try {
          var viewDate = $("#calendar").datepicker("getViewDate");
          if (viewDate && typeof viewDate.getMonth === "function") {
            var currentMonth = viewDate.getMonth() + 1;
            var currentYear = viewDate.getFullYear();
            cargarDiasConVentas(currentMonth, currentYear);
          } else {
            // Fallback: usar la fecha del evento o fecha actual
            var date = e.date || new Date();
            var currentMonth = date.getMonth() + 1;
            var currentYear = date.getFullYear();
            cargarDiasConVentas(currentMonth, currentYear);
          }
        } catch (error) {
          console.error("Error en changeDate:", error);
          var today = new Date();
          cargarDiasConVentas(today.getMonth() + 1, today.getFullYear());
        }
      }, 150);
    })
    .on("show", function (e) {
      // Limpiar tooltips al mostrar el calendario
      $(".calendar-tooltip").remove();
    });

  // Detectar cambio de mes mediante las flechas
  $(document).on("click", "#calendar .datepicker-switch", function () {
    setTimeout(function () {
      try {
        var viewDate = $("#calendar").datepicker("getViewDate");
        if (viewDate && typeof viewDate.getMonth === "function") {
          var currentMonth = viewDate.getMonth() + 1;
          var currentYear = viewDate.getFullYear();
        }
      } catch (error) {}
    }, 100);
  });

  $(document).on("click", "#calendar .prev, #calendar .next", function () {
    $(".calendar-tooltip").remove();
    setTimeout(function () {
      try {
        var viewDate = $("#calendar").datepicker("getViewDate");
        if (viewDate && typeof viewDate.getMonth === "function") {
          var currentMonth = viewDate.getMonth() + 1;
          var currentYear = viewDate.getFullYear();
          cargarDiasConVentas(currentMonth, currentYear);
        }
      } catch (error) {}
    }, 250);
  });

  // Recargar tooltips cuando el calendario termina de renderizar
  $(document).on("DOMSubtreeModified", "#calendar .datepicker", function () {
    clearTimeout(window.tooltipReloadTimer);
    window.tooltipReloadTimer = setTimeout(function () {
      if (diasConVentasData.length > 0 && $(".dia-con-venta").length > 0) {
        agregarTooltips();
      }
    }, 300);
  });

  // Cargar días con ventas del mes actual
  var today = new Date();
  cargarDiasConVentas(today.getMonth() + 1, today.getFullYear());

  // Permitir navegar al hacer clic en días de otros meses
  $(document).on(
    "click",
    ".datepicker .day.old, .datepicker .day.new",
    function () {
      $(".calendar-tooltip").remove();
      var clickedDate = $(this).data("date");
      if (clickedDate) {
        var date = new Date(clickedDate);
        $("#calendar").datepicker("setDate", date);
        cargarDiasConVentas(date.getMonth() + 1, date.getFullYear());
      }
    },
  );

  // Limpiar tooltips al hacer scroll
  $(window).on("scroll", function () {
    $(".calendar-tooltip").remove();
  });

  // Agregar estilos CSS para mejorar el calendario
  if (!document.getElementById("calendar-custom-styles")) {
    var style = document.createElement("style");
    style.id = "calendar-custom-styles";
    style.innerHTML = `
      /* Fondo del calendario morado */
      .box-solid.bg-blue-gradient {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
      }
      
      /* Botones minimizar y cerrar del calendario */
      .box-solid.bg-blue-gradient .box-tools .btn {
        background-color: rgba(255, 255, 255, 0.3) !important;
        border-color: rgba(255, 255, 255, 0.5) !important;
        color: white !important;
      }
      .box-solid.bg-blue-gradient .box-tools .btn:hover {
        background-color: rgba(255, 255, 255, 0.5) !important;
        border-color: white !important;
      }
      
      .datepicker .day.active,
      .datepicker .day.today.active {
        background-color: #667eea !important;
        background-image: none !important;
        color: white !important;
        font-weight: bold !important;
      }
      .datepicker .day.today {
        background-color: #e74c3c !important;
        color: white !important;
        font-weight: bold !important;
        box-shadow: 0 0 12px rgba(231, 76, 60, 0.8) !important;
        animation: pulse 2s infinite !important;
      }
      @keyframes pulse {
        0%, 100% { box-shadow: 0 0 12px rgba(231, 76, 60, 0.8); }
        50% { box-shadow: 0 0 20px rgba(231, 76, 60, 1); }
      }
      .datepicker .day.today:hover {
        background-color: #c0392b !important;
      }
      .datepicker table tr td.today.active:hover {
        background-color: #5568d3 !important;
      }
      .datepicker .day.dia-con-venta {
        background-color: #f39c12 !important;
        color: white !important;
        font-weight: bold !important;
        position: relative !important;
      }
      .datepicker .day.dia-con-venta:after {
        content: '💰' !important;
        position: absolute !important;
        bottom: 1px !important;
        right: 2px !important;
        font-size: 10px !important;
      }
      .datepicker .day.dia-con-venta:hover {
        background-color: #e08e0b !important;
        transform: scale(1.05) !important;
        transition: all 0.2s ease !important;
      }
      .datepicker .day.today.dia-con-venta {
        background-color: #e74c3c !important;
        box-shadow: 0 0 15px rgba(231, 76, 60, 1) !important;
      }
    `;
    document.head.appendChild(style);
  }

  // SLIMSCROLL FOR CHAT WIDGET
  $("#chat-box").slimScroll({
    height: "250px",
  });

  /* Morris.js Charts solo si existen los contenedores */
  var area, line, donut;

  // No inicializar el gráfico revenue-chart aquí, se inicializa desde index.js con datos dinámicos
  // Solo inicializamos si no es la página principal
  if (
    document.getElementById("revenue-chart") &&
    typeof appGraficoVentas === "undefined"
  ) {
    // Este código solo se ejecutará si NO se carga desde index.php
    area = new Morris.Area({
      element: "revenue-chart",
      resize: true,
      data: [
        { y: "2011 Q1", item1: 2666, item2: 2666 },
        { y: "2011 Q2", item1: 2778, item2: 2294 },
        { y: "2011 Q3", item1: 4912, item2: 1969 },
        { y: "2011 Q4", item1: 3767, item2: 3597 },
        { y: "2012 Q1", item1: 6810, item2: 1914 },
        { y: "2012 Q2", item1: 5670, item2: 4293 },
        { y: "2012 Q3", item1: 4820, item2: 3795 },
        { y: "2012 Q4", item1: 15073, item2: 5967 },
        { y: "2013 Q1", item1: 10687, item2: 4460 },
        { y: "2013 Q2", item1: 8432, item2: 5713 },
      ],
      xkey: "y",
      ykeys: ["item1", "item2"],
      labels: ["Item 1", "Item 2"],
      lineColors: ["#a0d0e0", "#3c8dbc"],
      hideHover: "auto",
    });
  }
  if (document.getElementById("line-chart")) {
    line = new Morris.Line({
      element: "line-chart",
      resize: true,
      data: [
        { y: "2011 Q1", item1: 2666 },
        { y: "2011 Q2", item1: 2778 },
        { y: "2011 Q3", item1: 4912 },
        { y: "2011 Q4", item1: 3767 },
        { y: "2012 Q1", item1: 6810 },
        { y: "2012 Q2", item1: 5670 },
        { y: "2012 Q3", item1: 4820 },
        { y: "2012 Q4", item1: 15073 },
        { y: "2013 Q1", item1: 10687 },
        { y: "2013 Q2", item1: 8432 },
      ],
      xkey: "y",
      ykeys: ["item1"],
      labels: ["Item 1"],
      lineColors: ["#efefef"],
      lineWidth: 2,
      hideHover: "auto",
      gridTextColor: "#fff",
      gridStrokeWidth: 0.4,
      pointSize: 4,
      pointStrokeColors: ["#efefef"],
      gridLineColor: "#efefef",
      gridTextFamily: "Open Sans",
      gridTextSize: 10,
    });
  }
  if (document.getElementById("sales-chart")) {
    donut = new Morris.Donut({
      element: "sales-chart",
      resize: true,
      colors: ["#3c8dbc", "#f56954", "#00a65a"],
      data: [
        { label: "Download Sales", value: 12 },
        { label: "In-Store Sales", value: 30 },
        { label: "Mail-Order Sales", value: 20 },
      ],
      hideHover: "auto",
    });
  }

  // Fix for charts under tabs
  $(".box ul.nav a").on("shown.bs.tab", function () {
    if (area) area.redraw();
    if (donut) donut.redraw();
    if (line) line.redraw();
  });

  /* The todo list plugin */
  $(".todo-list").todoList({
    onCheck: function () {},
    onUnCheck: function () {},
  });
});
