@extends ('layouts.app')
@section('title', 'Datos Históricos')
@section('content')

  <div class="min-h-screen flex flex-col">

        <!-- CONTENT -->
    <div class="flex-1 flex">
      <!-- Overlay (mobile) -->
      <div id="overlay" class="fixed inset-0 bg-black/50 z-30 hidden lg:hidden"></div>

 <!-- MAIN (Panel: Datos Históricos + Medias) -->
<main id="main" class="flex-1">
  <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">

    <!-- Título + acciones -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between mb-6">
      <div>
        <h1 class="text-2xl font-semibold tracking-wide">Datos Históricos</h1>
      </div>

      <!-- Acciones (solo exportar) -->
      <div class="flex flex-col sm:flex-row gap-2">
        <button
          id="btnExportCSV"
          class="inline-flex items-center justify-center rounded-xl border border-slate-800 bg-slate-900/40 px-4 py-2 text-sm text-slate-200 hover:bg-slate-900/70 transition"
          type="button"
        >
          Exportar (CSV)
        </button>
      </div>
    </div>

    <!-- Filtros -->
    <section class="rounded-2xl border border-slate-800 bg-slate-950/40 backdrop-blur p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
        <div class="md:col-span-4">
          <label class="text-xs text-slate-400">Buscar</label>
          <input
            id="q"
            type="text"
            placeholder="Ej: misión, id, comentario…"
            class="mt-1 w-full rounded-xl border border-slate-800 bg-slate-900/40 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-700"
          />
        </div>

        <div class="md:col-span-3">
          <label class="text-xs text-slate-400">Desde</label>
          <input
            id="fromDate"
            type="date"
            class="mt-1 w-full rounded-xl border border-slate-800 bg-slate-900/40 px-3 py-2 text-sm text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-700"
          />
        </div>

        <div class="md:col-span-3">
          <label class="text-xs text-slate-400">Hasta</label>
          <input
            id="toDate"
            type="date"
            class="mt-1 w-full rounded-xl border border-slate-800 bg-slate-900/40 px-3 py-2 text-sm text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-700"
          />
        </div>

        <div class="md:col-span-2 flex items-end gap-2">
          <button
            id="btnApply"
            class="w-full rounded-xl border border-slate-800 bg-slate-900/60 px-3 py-2 text-sm hover:bg-slate-900 transition"
            type="button"
          >
            Aplicar
          </button>
          <button
            id="btnReset"
            class="w-full rounded-xl border border-slate-800 bg-slate-900/20 px-3 py-2 text-sm text-slate-300 hover:bg-slate-900/40 transition"
            type="button"
          >
            Reset
          </button>
        </div>
      </div>
    </section>

    <!-- KPIs (Medias) -->
    <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <!-- Media Altitud -->
      <div class="rounded-2xl border border-slate-800 bg-slate-950/40 p-4">
        <p class="text-xs text-slate-400">Media Altitud</p>
        <p id="avgAlt" class="mt-2 text-2xl font-semibold">—</p>
        <p class="mt-1 text-xs text-slate-500">m (promedio)</p>
      </div>

      <!-- Media Velocidad de caída -->
      <div class="rounded-2xl border border-slate-800 bg-slate-950/40 p-4">
        <p class="text-xs text-slate-400">Media Vel. de Caída</p>
        <p id="avgFall" class="mt-2 text-2xl font-semibold">—</p>
        <p class="mt-1 text-xs text-slate-500">m/s (promedio)</p>
      </div>

      <!-- Media Tiempo de misión -->
      <div class="rounded-2xl border border-slate-800 bg-slate-950/40 p-4">
        <p class="text-xs text-slate-400">Media Tiempo de Misión</p>
        <p id="avgTime" class="mt-2 text-2xl font-semibold">—</p>
        <p class="mt-1 text-xs text-slate-500">s (promedio)</p>
      </div>

      <!-- Media Aceleración -->
      <div class="rounded-2xl border border-slate-800 bg-slate-950/40 p-4">
        <p class="text-xs text-slate-400">Media Aceleración</p>
        <p id="avgAcc" class="mt-2 text-2xl font-semibold">—</p>
        <p class="mt-1 text-xs text-slate-500">m/s² (promedio)</p>
      </div>
    </section>

    <!-- Tabla: histórico -->
    <section class="rounded-2xl border border-slate-800 bg-slate-950/40 backdrop-blur overflow-hidden">
      <div class="p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-slate-800">
        <div>
          <h2 class="text-lg font-semibold">Registros</h2>
          <p id="countInfo" class="text-xs text-slate-400">—</p>
        </div>

        <div class="flex items-center gap-2">
          <span class="text-xs text-slate-400">Mostrando 10 por página</span>
        </div>
      </div>

      <div class="telemetry-scroll overflow-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-slate-900/40 text-slate-300">
            <tr class="text-left">
              <th class="px-4 py-3 whitespace-nowrap">Fecha/Hora</th>
              <th class="px-4 py-3 whitespace-nowrap">Misión</th>
              <th class="px-4 py-3 whitespace-nowrap">Presión</th>
              <th class="px-4 py-3 whitespace-nowrap">Latitud</th>
              <th class="px-4 py-3 whitespace-nowrap">Longitud</th>
              <th class="px-4 py-3 whitespace-nowrap">RPM</th>
              <th class="px-4 py-3 whitespace-nowrap">Aceleración (m/s²)</th>
              <th class="px-4 py-3 whitespace-nowrap">Acciones</th>
            </tr>
          </thead>
          <tbody id="rows" class="divide-y divide-slate-800 text-slate-200">
            <!-- JS llena aquí -->
          </tbody>
        </table>
      </div>

      <!-- Paginación -->
      <div class="p-4 border-t border-slate-800 flex items-center justify-between gap-3">
        <button
          id="prevPage"
          class="rounded-xl border border-slate-800 bg-slate-900/20 px-3 py-2 text-sm text-slate-300 hover:bg-slate-900/40 transition disabled:opacity-40"
          type="button"
        >
          ← Anterior
        </button>

        <div class="text-xs text-slate-400">
          Página <span id="pageNow">1</span> / <span id="pageTotal">1</span>
        </div>

        <button
          id="nextPage"
          class="rounded-xl border border-slate-800 bg-slate-900/20 px-3 py-2 text-sm text-slate-300 hover:bg-slate-900/40 transition disabled:opacity-40"
          type="button"
        >
          Siguiente →
        </button>
      </div>
    </section>

    <!-- Modal detalle -->
    <div id="modal" class="fixed inset-0 z-[3000] hidden">
      <div class="absolute inset-0 bg-black/60"></div>
      <div class="relative mx-auto max-w-2xl px-4 sm:px-6 lg:px-8 h-full flex items-center">
        <div class="w-full rounded-2xl border border-slate-800 bg-slate-950 p-5 shadow-xl">
          <div class="flex items-start justify-between gap-3">
            <div>
              <h3 class="text-lg font-semibold">Detalle del registro</h3>
              <p id="modalSub" class="text-xs text-slate-400">—</p>
            </div>
            <button
              id="closeModal"
              class="rounded-xl border border-slate-800 bg-slate-900/30 px-3 py-2 text-sm hover:bg-slate-900/60 transition"
              type="button"
            >
              Cerrar
            </button>
          </div>

          <div id="modalBody" class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
            <!-- JS llena aquí -->
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de edición -->
    <div id="editModal" class="fixed inset-0 z-[3000] hidden">
      <div class="absolute inset-0 bg-black/60" data-close-edit></div>
      <div class="relative mx-auto max-w-lg px-4 sm:px-6 lg:px-8 h-full flex items-center">
        <form id="editForm" class="w-full rounded-2xl border border-slate-800 bg-slate-950 p-5 shadow-xl">
          <div class="flex items-start justify-between gap-3">
            <div>
              <h3 class="text-lg font-semibold">Editar registro</h3>
              <p class="text-xs text-slate-400">Solo se modificarán la misión y la presión.</p>
            </div>
            <button id="closeEditModal" class="rounded-xl border border-slate-800 bg-slate-900/30 px-3 py-2 text-sm hover:bg-slate-900/60 transition" type="button">Cerrar</button>
          </div>

          <div class="mt-4 space-y-4">
            <div>
              <label for="editMission" class="text-xs text-slate-400">Misión</label>
              <input id="editMission" name="mission" type="text" maxlength="50" required class="mt-1 w-full rounded-xl border border-slate-800 bg-slate-900/40 px-3 py-2 text-sm text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-700">
            </div>
            <div>
              <label for="editPres" class="text-xs text-slate-400">Presión</label>
              <input id="editPres" name="pres" type="number" step="any" required class="mt-1 w-full rounded-xl border border-slate-800 bg-slate-900/40 px-3 py-2 text-sm text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-700">
            </div>
            <p id="editError" class="hidden text-sm text-red-400"></p>
          </div>

          <div class="mt-5 flex justify-end">
            <button id="saveEdit" type="submit" class="rounded-xl border border-cyan-700 bg-cyan-900/40 px-4 py-2 text-sm text-cyan-100 hover:bg-cyan-900/70 transition disabled:opacity-50">Guardar cambios</button>
          </div>
        </form>
      </div>
    </div>

  </div>
</main>

<script>
  // Datos de BD inyectados por Laravel
  const serverData = @json($historicalData ?? []);
  const csrfToken = @json(csrf_token());
  const updateUrlTemplate = @json(route('datosh.update', ['id' => '__ID__']));

  // Fallback demo si no hay datos en BD
  let rawData = serverData.length ? serverData : [
    { ts: "2026-02-28T10:20:00", mission: "HYD-012", lat: 25.8694, lon: -97.5027, rpm: 1820, fall_speed: 12.4, mission_time: 95, accel: 9.7, notes: "Vuelo estable" },
    { ts: "2026-02-28T11:05:00", mission: "HYD-012", lat: 25.8696, lon: -97.5025, rpm: 1905, fall_speed: 13.1, mission_time: 98, accel: 10.4, notes: "Ligera deriva" },
    { ts: "2026-03-01T09:40:00", mission: "HYD-013", lat: 25.8702, lon: -97.5021, rpm: 1760, fall_speed: 11.8, mission_time: 90, accel: 9.2, notes: "Recuperación rápida" },
    { ts: "2026-03-01T12:10:00", mission: "HYD-014", lat: 25.8711, lon: -97.5018, rpm: 2010, fall_speed: 14.0, mission_time: 103, accel: 11.1, notes: "Apogeo alto" },
    { ts: "2026-03-01T12:45:00", mission: "HYD-014", lat: 25.8713, lon: -97.5015, rpm: 1988, fall_speed: 13.6, mission_time: 101, accel: 10.8, notes: "Paracaídas OK" },
  ];

  // Estado UI
  let filtered = [...rawData];
  let page = 1;
  const pageSize = 10;

  // Helpers
  const $ = (id) => document.getElementById(id);
  const fmt = (n, d=2) => (Number.isFinite(n) ? n.toFixed(d) : "—");
  const fmtInt = (n) => (Number.isFinite(n) ? Math.round(n).toString() : "—");
  const escapeHtml = (value) => String(value).replace(/[&<>'"]/g, character => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;'
  })[character]);
  const toLocal = (iso) => {
    const dt = new Date(iso);
    if (Number.isNaN(dt.getTime())) return iso;
    return dt.toLocaleString();
  };

  function mean(arr) {
    const nums = arr.filter((x) => Number.isFinite(x));
    if (!nums.length) return NaN;
    return nums.reduce((a,b) => a + b, 0) / nums.length;
  }

  function applyFilters() {
    const q = ($("q").value || "").trim().toLowerCase();
    const from = $("fromDate").value ? new Date($("fromDate").value + "T00:00:00") : null;
    const to   = $("toDate").value ? new Date($("toDate").value + "T23:59:59") : null;

    filtered = rawData.filter((r) => {
      const dt = new Date(r.ts);
      if (from && dt < from) return false;
      if (to && dt > to) return false;

      if (q) {
        const blob = [
          r.ts, r.mission,
          String(r.pres), String(r.lat), String(r.lon),
          String(r.rpm), String(r.accel)
        ].join(" ").toLowerCase();
        if (!blob.includes(q)) return false;
      }
      return true;
    });

    page = 1;
    renderAll();
  }

  function resetFilters() {
    $("q").value = "";
    $("fromDate").value = "";
    $("toDate").value = "";
    filtered = [...rawData];
    page = 1;
    renderAll();
  }

function renderKPIs() {
  // En tu data es lat, así que calculamos promedio de lat
  const avgAlt = mean(filtered.map(r => r.lat));
  const avgFall = mean(filtered.map(r => r.fall_speed));
  const avgTime = mean(filtered.map(r => r.mission_time));
  const avgAcc  = mean(filtered.map(r => r.accel));

  // OJO: aquí usamos los IDs que existen en tu HTML:
  $("avgAlt").textContent  = Number.isFinite(avgAlt) ? fmt(avgAlt, 5) : "—";   // avgAlt = promedio de latitud
  $("avgFall").textContent = Number.isFinite(avgFall) ? fmt(avgFall, 2) : "—";
  $("avgTime").textContent = Number.isFinite(avgTime) ? fmt(avgTime, 1) : "—";
  $("avgAcc").textContent  = Number.isFinite(avgAcc) ? fmt(avgAcc, 2) : "—";

  $("countInfo").textContent = `${filtered.length} registro(s) encontrados`;
}

  function renderTable() {
    const totalPages = Math.max(1, Math.ceil(filtered.length / pageSize));
    if (page > totalPages) page = totalPages;

    const start = (page - 1) * pageSize;
    const slice = filtered.slice(start, start + pageSize);

    $("rows").innerHTML = slice.map((r, idx) => `
      <tr class="hover:bg-slate-900/30 transition cursor-pointer" data-ix="${start + idx}">
        <td class="px-4 py-3 whitespace-nowrap text-slate-300">${toLocal(r.ts)}</td>
        <td class="px-4 py-3 whitespace-nowrap font-medium">${r.mission ? escapeHtml(r.mission) : "—"}</td>
        <td class="px-4 py-3 whitespace-nowrap">${Number.isFinite(r.pres) ? fmt(r.pres, 2) : "—"}</td>
        <td class="px-4 py-3 whitespace-nowrap">${Number.isFinite(r.lat) ? fmt(r.lat, 5) : "—"}</td>
        <td class="px-4 py-3 whitespace-nowrap">${Number.isFinite(r.lon) ? fmt(r.lon, 5) : "—"}</td>
        <td class="px-4 py-3 whitespace-nowrap">${Number.isFinite(r.rpm) ? fmtInt(r.rpm) : "—"}</td>
        <td class="px-4 py-3 whitespace-nowrap">${Number.isFinite(r.accel) ? fmt(r.accel, 2) : "—"}</td>
        <td class="px-4 py-3 whitespace-nowrap">
          ${r.id_db ? `<button type="button" class="edit-row rounded-lg border border-slate-700 bg-slate-900/40 px-3 py-1.5 text-xs hover:bg-slate-800 transition" data-id="${r.id_db}">Editar</button>` : "—"}
        </td>
      </tr>
    `).join("");

    $("pageNow").textContent = String(page);
    $("pageTotal").textContent = String(totalPages);

    $("prevPage").disabled = page <= 1;
    $("nextPage").disabled = page >= totalPages;

    // Click fila -> modal
    [...$("rows").querySelectorAll("tr")].forEach(tr => {
      tr.addEventListener("click", () => {
        const ix = Number(tr.getAttribute("data-ix"));
        openModal(filtered[ix]);
      });
    });

    [...$("rows").querySelectorAll(".edit-row")].forEach(button => {
      button.addEventListener("click", (event) => {
        event.stopPropagation();
        openEditModal(Number(button.dataset.id));
      });
    });
  }

  function renderAll() {
    renderKPIs();
    renderTable();
  }

  // Modal
  function openModal(r) {
    $("modal").classList.remove("hidden");
    $("modalSub").textContent = `${r.mission || "—"} · ${toLocal(r.ts)}`;

    const items = [
      ["Presión", Number.isFinite(r.pres) ? fmt(r.pres, 2) + " hPa" : "—"],
      ["Latitud", Number.isFinite(r.lat) ? fmt(r.lat, 6) + " °" : "—"],
      ["Longitud", Number.isFinite(r.lon) ? fmt(r.lon, 6) + " °" : "—"],
      ["RPM", Number.isFinite(r.rpm) ? fmtInt(r.rpm) + " rpm" : "—"],
      ["Aceleración", Number.isFinite(r.accel) ? fmt(r.accel, 2) + " m/s²" : "—"],
    ];

    $("modalBody").innerHTML = items.map(([k,v]) => `
      <div class="rounded-xl border border-slate-800 bg-slate-900/25 p-3">
        <p class="text-xs text-slate-400">${k}</p>
        <p class="mt-1 text-slate-100">${v}</p>
      </div>
    `).join("");
  }

  function closeModal() {
    $("modal").classList.add("hidden");
  }

  let editingId = null;

  function openEditModal(id) {
    const row = rawData.find(r => r.id_db === id);
    if (!row) return;

    editingId = id;
    $("editMission").value = row.mission ?? "";
    $("editPres").value = Number.isFinite(row.pres) ? row.pres : "";
    $("editError").classList.add("hidden");
    $("editModal").classList.remove("hidden");
  }

  function closeEditModal() {
    editingId = null;
    $("editModal").classList.add("hidden");
  }

  async function saveEdit(event) {
    event.preventDefault();
    if (!editingId) return;

    const saveButton = $("saveEdit");
    const error = $("editError");
    saveButton.disabled = true;
    error.classList.add("hidden");

    try {
      const response = await fetch(updateUrlTemplate.replace('__ID__', editingId), {
        method: "PATCH",
        headers: {
          "Accept": "application/json",
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": csrfToken,
        },
        body: JSON.stringify({
          mission: $("editMission").value.trim(),
          pres: $("editPres").value,
        }),
      });

      const result = await response.json();
      if (!response.ok) {
        const validationMessage = result.errors ? Object.values(result.errors).flat()[0] : null;
        throw new Error(validationMessage || result.message || "No se pudo actualizar el registro.");
      }

      const row = rawData.find(r => r.id_db === editingId);
      row.mission = result.data.mission;
      row.pres = result.data.pres;
      closeEditModal();
      applyFilters();
    } catch (exception) {
      error.textContent = exception.message;
      error.classList.remove("hidden");
    } finally {
      saveButton.disabled = false;
    }
  }

  // CSV Export simple
  function exportCSV() {
    const cols = ["ts","mission","pres","lat","lon","rpm","accel"];
    const lines = [
      cols.join(","),
      ...filtered.map(r => cols.map(c => {
        const val = (r[c] ?? "");
        const s = String(val).replaceAll('"','""');
        return /[,"\n]/.test(s) ? `"${s}"` : s;
      }).join(","))
    ];
    const blob = new Blob([lines.join("\n")], { type: "text/csv;charset=utf-8;" });
    const url = URL.createObjectURL(blob);

    const a = document.createElement("a");
    a.href = url;
    a.download = `hydronautas_historico_${new Date().toISOString().slice(0,10)}.csv`;
    document.body.appendChild(a);
    a.click();
    a.remove();
    URL.revokeObjectURL(url);
  }

  // Eventos
  $("btnApply").addEventListener("click", applyFilters);
  $("btnReset").addEventListener("click", resetFilters);

  $("prevPage").addEventListener("click", () => { page--; renderTable(); });
  $("nextPage").addEventListener("click", () => { page++; renderTable(); });

  $("btnExportCSV").addEventListener("click", exportCSV);

  $("closeModal").addEventListener("click", closeModal);
  $("modal").addEventListener("click", (e) => {
    if (e.target === $("modal").querySelector(".absolute")) closeModal();
  });
  $("closeEditModal").addEventListener("click", closeEditModal);
  $("editModal").querySelector("[data-close-edit]").addEventListener("click", closeEditModal);
  $("editForm").addEventListener("submit", saveEdit);

  // Render inicial
  renderAll();
</script>
@endsection
