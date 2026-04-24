(function () {
  "use strict";

  const ENDPOINT = "dashboard_data.php";

  function chartTheme() {
    // Seu layout é dark: define cores com contraste premium
    return {
      tick: "#E7E9EE",
      grid: "rgba(255,255,255,.08)"
    };
  }

  function palette(i) {
    const colors = [
      "rgba(0, 123, 255, .65)",
      "rgba(102, 16, 242, .65)",
      "rgba(32, 201, 151, .65)",
      "rgba(255, 193, 7, .65)",
      "rgba(220, 53, 69, .65)",
      "rgba(23, 162, 184, .65)"
    ];
    return colors[i % colors.length];
  }

  function mount(id, factory) {
    const el = document.getElementById(id);
    if (!el) return;
    new Chart(el.getContext("2d"), factory());
  }

  async function main() {
    if (typeof Chart === "undefined") {
      console.error("Chart.js não carregou. Verifique o include do CDN no index.php.");
      return;
    }

    const theme = chartTheme();

    let payload;
    try {
      const r = await fetch(ENDPOINT, { credentials: "same-origin" });
      payload = await r.json();
    } catch (e) {
      console.error("Falha ao buscar dados:", e);
      return;
    }

    if (!payload || payload.ok !== true) {
      console.error("Endpoint retornou erro:", payload);
      return;
    }

    // Unidades (bar)
    mount("chartUnidades", () => {
      const labels = (payload.unidades || []).map(x => x.label);
      const values = (payload.unidades || []).map(x => Number(x.total || 0));
      return {
        type: "bar",
        data: { labels, datasets: [{
          label: "Colaboradores por Unidade",
          data: values,
          backgroundColor: palette(0),
          borderRadius: 10,
          maxBarThickness: 42
        }]},
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { labels: { color: theme.tick } } },
          scales: {
            x: { ticks: { color: theme.tick }, grid: { color: theme.grid } },
            y: { ticks: { color: theme.tick }, grid: { color: theme.grid }, beginAtZero: true }
          }
        }
      };
    });

    // Setores (horizontal)
    mount("chartSetores", () => {
      const labels = (payload.setores || []).map(x => x.label);
      const values = (payload.setores || []).map(x => Number(x.total || 0));
      return {
        type: "bar",
        data: { labels, datasets: [{
          label: "Colaboradores por Setor",
          data: values,
          backgroundColor: palette(1),
          borderRadius: 10,
          maxBarThickness: 28
        }]},
        options: {
          indexAxis: "y",
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { labels: { color: theme.tick } } },
          scales: {
            x: { ticks: { color: theme.tick }, grid: { color: theme.grid }, beginAtZero: true },
            y: { ticks: { color: theme.tick }, grid: { color: theme.grid } }
          }
        }
      };
    });

    // Cargos (doughnut)
    mount("chartCargos", () => {
      const labels = (payload.cargos || []).map(x => x.label);
      const values = (payload.cargos || []).map(x => Number(x.total || 0));
      const colors = labels.map((_, i) => palette(i));
      return {
        type: "doughnut",
        data: { labels, datasets: [{
          label: "Distribuição de Cargos",
          data: values,
          backgroundColor: colors,
          borderColor: "rgba(255,255,255,.10)",
          borderWidth: 1
        }]},
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { labels: { color: theme.tick } } }
        }
      };
    });

    // Status M365 (pie)
    mount("chartStatusM365", () => {
      const labels = (payload.status_m365 || []).map(x => x.label);
      const values = (payload.status_m365 || []).map(x => Number(x.total || 0));
      const colors = labels.map((_, i) => palette(i + 2));
      return {
        type: "pie",
        data: { labels, datasets: [{
          label: "Status Microsoft 365",
          data: values,
          backgroundColor: colors,
          borderColor: "rgba(255,255,255,.10)",
          borderWidth: 1
        }]},
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { labels: { color: theme.tick } } }
        }
      };
    });
  }

  document.addEventListener("DOMContentLoaded", main);
})();
