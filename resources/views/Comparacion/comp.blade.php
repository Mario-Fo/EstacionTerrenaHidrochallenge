@extends ('layouts.app')
@section('title', 'Comparación de Misiones')
@section('content')

    <!-- MAIN -->
    <main id="main" class="flex-1">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">

        <div class="mb-6">
          <h1 class="text-2xl font-semibold tracking-wide">Comparador de Misiones</h1>
          <p class="text-slate-400 text-sm">
            Carga (CSV) y filtra cada misión por día/mes/año. Observa medias y registros lado a lado.
          </p>
        </div>

        <!-- Controles globales -->
        <section class="rounded-2xl border border-slate-800 bg-slate-950/40 backdrop-blur p-4 mb-6">
          <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div class="text-sm text-slate-300">
              Demo activo: Misiones simuladas HYD-012 vs HYD-014.
            </div>
            <button
              id="btnResetAll"
              class="rounded-xl border border-slate-800 bg-slate-900/20 px-4 py-2 text-sm text-slate-200 hover:bg-slate-900/40 transition"
              type="button"
            >
              Reset (Todo)
            </button>
          </div>
        </section>

        <!-- 2 columnas -->
        <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">

          <!-- ===================== MISION A ===================== -->
          <article class="rounded-2xl border border-slate-800 bg-slate-950/40 backdrop-blur overflow-hidden">
            <div class="p-4 border-b border-slate-800 flex items-start justify-between gap-3">
              <div>
                <h2 class="text-lg font-semibold">Misión A</h2>
                <p id="infoA" class="text-xs text-slate-400">—</p>
              </div>

              <div class="flex flex-col sm:flex-row gap-2">
                <label class="inline-flex items-center justify-center rounded-xl border border-slate-800 bg-slate-900/40 px-3 py-2 text-sm text-slate-200 hover:bg-slate-900/70 transition cursor-pointer">
                  Importar CSV
                  <input id="fileA" type="file" accept=".csv" class="hidden" />
                </label>
              </div>
            </div>

            <!-- filtros A -->
            <div class="p-4 border-b border-slate-800">
              <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">

                <div class="md:col-span-4">
                  <label class="text-xs text-slate-400">Misión</label>
                  <select id="missionA" class="mt-1 w-full rounded-xl border border-slate-800 bg-slate-900/40 px-3 py-2 text-sm"></select>
                </div>

                <div class="md:col-span-3">
                  <label class="text-xs text-slate-400">Día</label>
                  <select id="dayA" class="mt-1 w-full rounded-xl border border-slate-800 bg-slate-900/40 px-3 py-2 text-sm">
                    <option value="">Todos</option>
                  </select>
                </div>

                <div class="md:col-span-3">
                  <label class="text-xs text-slate-400">Mes</label>
                  <div class="mt-1 flex gap-2">
                    <select id="monthA" class="flex-1 min-w-[120px] rounded-xl border border-slate-800 bg-slate-900/40 px-3 py-2 text-sm text-slate-100">
                      <option value="">Todos</option>
                    </select>

                    <!-- Botón aplicar A al lado del Mes -->
                    <button
                      id="applyA"
                      class="shrink-0 rounded-xl border border-slate-800 bg-slate-900/60 px-4 py-2 text-sm hover:bg-slate-900 transition"
                      type="button"
                    >
                      Aplicar
                    </button>
                  </div>
                </div>

              </div>
            </div>

            <!-- KPIs A -->
            <div class="p-4 grid grid-cols-2 lg:grid-cols-5 gap-3 border-b border-slate-800">
              <div class="rounded-xl border border-slate-800 bg-slate-900/20 p-3">
                <p class="text-xs text-slate-400">Media Altitud</p>
                <p id="avgAltA" class="mt-1 text-lg font-semibold">—</p>
                <p class="text-xs text-slate-500">m</p>
              </div>
              <div class="rounded-xl border border-slate-800 bg-slate-900/20 p-3">
                <p class="text-xs text-slate-400">Media RPM</p>
                <p id="avgRPMA" class="mt-1 text-lg font-semibold">—</p>
                <p class="text-xs text-slate-500">rpm</p>
              </div>
              <div class="rounded-xl border border-slate-800 bg-slate-900/20 p-3">
                <p class="text-xs text-slate-400">Media Vel. Caída</p>
                <p id="avgFallA" class="mt-1 text-lg font-semibold">—</p>
                <p class="text-xs text-slate-500">m/s</p>
              </div>
              <div class="rounded-xl border border-slate-800 bg-slate-900/20 p-3">
                <p class="text-xs text-slate-400">Media Tiempo</p>
                <p id="avgTimeA" class="mt-1 text-lg font-semibold">—</p>
                <p class="text-xs text-slate-500">s</p>
              </div>
              <div class="rounded-xl border border-slate-800 bg-slate-900/20 p-3">
                <p class="text-xs text-slate-400">Media Aceleración</p>
                <p id="avgAccA" class="mt-1 text-lg font-semibold">—</p>
                <p class="text-xs text-slate-500">m/s²</p>
              </div>
            </div>

            <!-- Tabla A -->
            <div class="telemetry-scroll overflow-auto">
              <table class="min-w-full text-sm">
                <thead class="bg-slate-900/40 text-slate-300">
                  <tr class="text-left">
                    <th class="px-4 py-3 whitespace-nowrap">Fecha/Hora</th>
                    <th class="px-4 py-3 whitespace-nowrap">Misión</th>
                    <th class="px-4 py-3 whitespace-nowrap">Altitud (m)</th>
                    <th class="px-4 py-3 whitespace-nowrap">RPM</th>
                    <th class="px-4 py-3 whitespace-nowrap">Vel. Caída</th>
                    <th class="px-4 py-3 whitespace-nowrap">Tiempo</th>
                    <th class="px-4 py-3 whitespace-nowrap">Acel.</th>
                    <th class="px-4 py-3 whitespace-nowrap">Notas</th>
                  </tr>
                </thead>
                <tbody id="rowsA" class="divide-y divide-slate-800 text-slate-200"></tbody>
              </table>
            </div>
            <div class="p-4 border-t border-slate-800 flex items-center justify-between gap-3">
              <p id="pageInfoA" class="text-xs text-slate-400">Página 1 de 1</p>
              <div class="flex items-center gap-2">
                <button id="prevA" type="button" class="rounded-xl border border-slate-800 bg-slate-900/40 px-3 py-2 text-xs text-slate-200 hover:bg-slate-900/70 transition">Anterior</button>
                <button id="nextA" type="button" class="rounded-xl border border-slate-800 bg-slate-900/40 px-3 py-2 text-xs text-slate-200 hover:bg-slate-900/70 transition">Siguiente</button>
              </div>
            </div>
          </article>

          <!-- ===================== MISION B ===================== -->
          <article class="rounded-2xl border border-slate-800 bg-slate-950/40 backdrop-blur overflow-hidden">
            <div class="p-4 border-b border-slate-800 flex items-start justify-between gap-3">
              <div>
                <h2 class="text-lg font-semibold">Misión B</h2>
                <p id="infoB" class="text-xs text-slate-400">—</p>
              </div>

              <div class="flex flex-col sm:flex-row gap-2">
                <label class="inline-flex items-center justify-center rounded-xl border border-slate-800 bg-slate-900/40 px-3 py-2 text-sm text-slate-200 hover:bg-slate-900/70 transition cursor-pointer">
                  Importar CSV
                  <input id="fileB" type="file" accept=".csv" class="hidden" />
                </label>
              </div>
            </div>

            <!-- filtros B -->
            <div class="p-4 border-b border-slate-800">
              <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">

                <div class="md:col-span-4">
                  <label class="text-xs text-slate-400">Misión</label>
                  <select id="missionB" class="mt-1 w-full rounded-xl border border-slate-800 bg-slate-900/40 px-3 py-2 text-sm"></select>
                </div>

                <div class="md:col-span-3">
                  <label class="text-xs text-slate-400">Día</label>
                  <select id="dayB" class="mt-1 w-full rounded-xl border border-slate-800 bg-slate-900/40 px-3 py-2 text-sm">
                    <option value="">Todos</option>
                  </select>
                </div>

                <div class="md:col-span-3">
                  <label class="text-xs text-slate-400">Mes</label>
                  <div class="mt-1 flex gap-2">
                    <select id="monthB" class="flex-1 min-w-[120px] rounded-xl border border-slate-800 bg-slate-900/40 px-3 py-2 text-sm text-slate-100">
                      <option value="">Todos</option>
                    </select>

                    <!-- Botón aplicar B al lado del Mes -->
                    <button
                      id="applyB"
                      class="shrink-0 rounded-xl border border-slate-800 bg-slate-900/60 px-4 py-2 text-sm hover:bg-slate-900 transition"
                      type="button"
                    >
                      Aplicar
                    </button>
                  </div>
                </div>

              </div>
            </div>

            <!-- KPIs B -->
            <div class="p-4 grid grid-cols-2 lg:grid-cols-5 gap-3 border-b border-slate-800">
              <div class="rounded-xl border border-slate-800 bg-slate-900/20 p-3">
                <p class="text-xs text-slate-400">Media Altitud</p>
                <p id="avgAltB" class="mt-1 text-lg font-semibold">—</p>
                <p class="text-xs text-slate-500">m</p>
              </div>
              <div class="rounded-xl border border-slate-800 bg-slate-900/20 p-3">
                <p class="text-xs text-slate-400">Media RPM</p>
                <p id="avgRPMB" class="mt-1 text-lg font-semibold">—</p>
                <p class="text-xs text-slate-500">rpm</p>
              </div>
              <div class="rounded-xl border border-slate-800 bg-slate-900/20 p-3">
                <p class="text-xs text-slate-400">Media Vel. Caída</p>
                <p id="avgFallB" class="mt-1 text-lg font-semibold">—</p>
                <p class="text-xs text-slate-500">m/s</p>
              </div>
              <div class="rounded-xl border border-slate-800 bg-slate-900/20 p-3">
                <p class="text-xs text-slate-400">Media Tiempo</p>
                <p id="avgTimeB" class="mt-1 text-lg font-semibold">—</p>
                <p class="text-xs text-slate-500">s</p>
              </div>
              <div class="rounded-xl border border-slate-800 bg-slate-900/20 p-3">
                <p class="text-xs text-slate-400">Media Aceleración</p>
                <p id="avgAccB" class="mt-1 text-lg font-semibold">—</p>
                <p class="text-xs text-slate-500">m/s²</p>
              </div>
            </div>

            <!-- Tabla B -->
            <div class="telemetry-scroll overflow-auto">
              <table class="min-w-full text-sm">
                <thead class="bg-slate-900/40 text-slate-300">
                  <tr class="text-left">
                    <th class="px-4 py-3 whitespace-nowrap">Fecha/Hora</th>
                    <th class="px-4 py-3 whitespace-nowrap">Misión</th>
                    <th class="px-4 py-3 whitespace-nowrap">Altitud (m)</th>
                    <th class="px-4 py-3 whitespace-nowrap">RPM</th>
                    <th class="px-4 py-3 whitespace-nowrap">Vel. Caída</th>
                    <th class="px-4 py-3 whitespace-nowrap">Tiempo</th>
                    <th class="px-4 py-3 whitespace-nowrap">Acel.</th>
                    <th class="px-4 py-3 whitespace-nowrap">Notas</th>
                  </tr>
                </thead>
                <tbody id="rowsB" class="divide-y divide-slate-800 text-slate-200"></tbody>
              </table>
            </div>
            <div class="p-4 border-t border-slate-800 flex items-center justify-between gap-3">
              <p id="pageInfoB" class="text-xs text-slate-400">Página 1 de 1</p>
              <div class="flex items-center gap-2">
                <button id="prevB" type="button" class="rounded-xl border border-slate-800 bg-slate-900/40 px-3 py-2 text-xs text-slate-200 hover:bg-slate-900/70 transition">Anterior</button>
                <button id="nextB" type="button" class="rounded-xl border border-slate-800 bg-slate-900/40 px-3 py-2 text-xs text-slate-200 hover:bg-slate-900/70 transition">Siguiente</button>
              </div>
            </div>
          </article>

        </section>

      </div>
    </main>
  </div>

  <script>
    // Datos de BD inyectados por Laravel
    const serverData = @json($missionData ?? []);

    // Fallback demo si no hay datos en BD
    const demoData = serverData.length ? serverData : [
      { ts: "2026-02-28T10:20:00", mission: "HYD-012", altitude: 25.8694, rpm: 1820, fall_speed: 12.4, mission_time: 95,  accel: 9.7,  notes: "Vuelo estable" },
      { ts: "2026-02-28T11:05:00", mission: "HYD-012", altitude: 25.8696, rpm: 1905, fall_speed: 13.1, mission_time: 98,  accel: 10.4, notes: "Ligera deriva" },
      { ts: "2026-03-01T09:40:00", mission: "HYD-013", altitude: 25.8702, rpm: 1760, fall_speed: 11.8, mission_time: 90,  accel: 9.2,  notes: "Recuperación rápida" },
      { ts: "2026-03-01T12:10:00", mission: "HYD-014", altitude: 25.8711, rpm: 2010, fall_speed: 14.0, mission_time: 103, accel: 11.1, notes: "Apogeo alto" },
      { ts: "2026-03-01T12:45:00", mission: "HYD-014", altitude: 25.8713, rpm: 1988, fall_speed: 13.6, mission_time: 101, accel: 10.8, notes: "Paracaídas OK" },
    ];

    const state = {
      A: { raw: [...demoData], filtered: [], page: 1, perPage: 10 },
      B: { raw: [...demoData], filtered: [], page: 1, perPage: 10 },
    };

    const $ = (id) => document.getElementById(id);
    const fmt = (n, d=2) => (Number.isFinite(n) ? n.toFixed(d) : "—");
    const fmtInt = (n) => (Number.isFinite(n) ? Math.round(n).toString() : "—");
    const toLocal = (iso) => {
      const dt = new Date(iso);
      if (Number.isNaN(dt.getTime())) return iso;
      return dt.toLocaleString();
    };

    function mean(nums) {
      const ok = nums.filter(x => Number.isFinite(x));
      if (!ok.length) return NaN;
      return ok.reduce((a,b)=>a+b,0)/ok.length;
    }

    function uniqueSorted(arr) {
      return [...new Set(arr)].sort((a,b) => (a > b ? 1 : a < b ? -1 : 0));
    }

    function getYMD(iso) {
      const d = new Date(iso);
      return { m: d.getMonth()+1, day: d.getDate() };
    }

    function fillMissionOptions(panel) {
      const sel = $("mission" + panel);
      const missions = uniqueSorted(state[panel].raw.map(r => r.mission).filter(Boolean));
      sel.innerHTML = missions.map(m => `<option value="${m}">${m}</option>`).join("");
      if (panel === "A") sel.value = missions.includes("PRUEBA") ? "PRUEBA" : missions[0] || "";
      if (panel === "B") sel.value = missions.includes("PRUEBA_E2E") ? "PRUEBA_E2E" : missions[1] || missions[0] || "";
    }

    function fillDateDropdowns(panel) {
      const mission = $("mission" + panel).value;
      const rows = state[panel].raw.filter(r => !mission || r.mission === mission);
      const ymd = rows.map(r => getYMD(r.ts));
      const months = uniqueSorted(ymd.map(x => x.m));
      const days = uniqueSorted(ymd.map(x => x.day));

      const mSel = $("month" + panel);
      const dSel = $("day" + panel);

      const keepM = mSel.value, keepD = dSel.value;

      mSel.innerHTML = `<option value="">Todos</option>` + months.map(m => `<option value="${m}">${String(m).padStart(2,"0")}</option>`).join("");
      dSel.innerHTML = `<option value="">Todos</option>` + days.map(d => `<option value="${d}">${String(d).padStart(2,"0")}</option>`).join("");

      if (keepM && months.includes(Number(keepM))) mSel.value = keepM; else mSel.value = "";
      if (keepD && days.includes(Number(keepD))) dSel.value = keepD; else dSel.value = "";
    }

    function applyPanel(panel) {
      const mission = $("mission" + panel).value;
      const month = $("month" + panel).value ? Number($("month" + panel).value) : null;
      const day = $("day" + panel).value ? Number($("day" + panel).value) : null;

      // Ya no hay búsqueda, pero dejamos esto por compatibilidad (no afecta nada)
      const qEl = $("q" + panel);
      const q = qEl ? (qEl.value || "").trim().toLowerCase() : "";

      const rows = state[panel].raw.filter(r => {
        if (mission && r.mission !== mission) return false;
        const ymd = getYMD(r.ts);
        if (month && ymd.m !== month) return false;
        if (day && ymd.day !== day) return false;
        if (q) {
          const blob = [r.ts, r.mission, r.notes].join(" ").toLowerCase();
          if (!blob.includes(q)) return false;
        }
        return true;
      });

      state[panel].filtered = rows;
      state[panel].page = 1;
      renderPanel(panel);
    }

    function renderPanel(panel) {
      const rows = state[panel].filtered;
      const perPage = state[panel].perPage;
      const totalPages = Math.max(1, Math.ceil(rows.length / perPage));
      if (state[panel].page > totalPages) state[panel].page = totalPages;
      if (state[panel].page < 1) state[panel].page = 1;
      const page = state[panel].page;
      const start = (page - 1) * perPage;
      const end = start + perPage;
      const pageRows = rows.slice(start, end);

      $("info" + panel).textContent = `${rows.length} registro(s)`;

      const avgAlt = mean(rows.map(r => r.altitude));
      const avgRPM = mean(rows.map(r => r.rpm));
      const avgFall = mean(rows.map(r => r.fall_speed));
      const avgTime = mean(rows.map(r => r.mission_time));
      const avgAcc = mean(rows.map(r => r.accel));

      $("avgAlt" + panel).textContent = Number.isFinite(avgAlt) ? fmt(avgAlt, 3) : "—";
      $("avgRPM" + panel).textContent = Number.isFinite(avgRPM) ? fmtInt(avgRPM) : "—";
      $("avgFall" + panel).textContent = Number.isFinite(avgFall) ? fmt(avgFall, 2) : "—";
      $("avgTime" + panel).textContent = Number.isFinite(avgTime) ? fmt(avgTime, 1) : "—";
      $("avgAcc" + panel).textContent = Number.isFinite(avgAcc) ? fmt(avgAcc, 2) : "—";

      $("rows" + panel).innerHTML = pageRows.map(r => `
        <tr class="hover:bg-slate-900/30 transition">
          <td class="px-4 py-3 whitespace-nowrap text-slate-300">${toLocal(r.ts)}</td>
          <td class="px-4 py-3 whitespace-nowrap font-medium">${r.mission || "—"}</td>
          <td class="px-4 py-3 whitespace-nowrap">${Number.isFinite(r.altitude) ? fmt(r.altitude, 3) : "—"}</td>
          <td class="px-4 py-3 whitespace-nowrap">${Number.isFinite(r.rpm) ? fmtInt(r.rpm) : "—"}</td>
          <td class="px-4 py-3 whitespace-nowrap">${Number.isFinite(r.fall_speed) ? fmt(r.fall_speed, 2) : "—"}</td>
          <td class="px-4 py-3 whitespace-nowrap">${Number.isFinite(r.mission_time) ? fmt(r.mission_time, 1) : "—"}</td>
          <td class="px-4 py-3 whitespace-nowrap">${Number.isFinite(r.accel) ? fmt(r.accel, 2) : "—"}</td>
          <td class="px-4 py-3 whitespace-nowrap text-slate-400">${r.notes || "—"}</td>
        </tr>
      `).join("");

      $("pageInfo" + panel).textContent = `Página ${page} de ${totalPages}`;
      $("prev" + panel).disabled = page <= 1;
      $("next" + panel).disabled = page >= totalPages;
    }

    // CSV Import por panel (requiere encabezados):
    // ts, mission, altitude, rpm, fall_speed, mission_time, accel, notes
    function importCSV(panel, file) {
      const reader = new FileReader();
      reader.onload = () => {
        const text = String(reader.result || "");
        const lines = text.split(/\r?\n/).filter(Boolean);
        if (lines.length < 2) return;

        function parseLine(line) {
          const out = [];
          let cur = "", inQ = false;
          for (let i=0;i<line.length;i++){
            const ch = line[i];
            if (ch === '"' && line[i+1] === '"') { cur += '"'; i++; continue; }
            if (ch === '"') { inQ = !inQ; continue; }
            if (ch === "," && !inQ) { out.push(cur); cur=""; continue; }
            cur += ch;
          }
          out.push(cur);
          return out;
        }

        const header = parseLine(lines[0]).map(h => h.trim());
        const idx = (name) => header.indexOf(name);

        const required = ["ts","mission","altitude","rpm","fall_speed","mission_time","accel","notes"];
        const ok = required.every(c => idx(c) !== -1);
        if (!ok) {
          alert("CSV inválido. Encabezados requeridos:\n" + required.join(", "));
          return;
        }

        const parsed = [];
        for (let i=1;i<lines.length;i++){
          const p = parseLine(lines[i]);
          parsed.push({
            ts: p[idx("ts")] || "",
            mission: p[idx("mission")] || "",
            altitude: Number(p[idx("altitude")]),
            rpm: Number(p[idx("rpm")]),
            fall_speed: Number(p[idx("fall_speed")]),
            mission_time: Number(p[idx("mission_time")]),
            accel: Number(p[idx("accel")]),
            notes: p[idx("notes")] || "",
          });
        }

        state[panel].raw = parsed;
        fillMissionOptions(panel);
        fillDateDropdowns(panel);
        applyPanel(panel);
      };
      reader.readAsText(file);
    }

    function resetAll() {
      state.A.raw = [...demoData];
      state.B.raw = [...demoData];

      fillMissionOptions("A");
      fillMissionOptions("B");
      fillDateDropdowns("A");
      fillDateDropdowns("B");

      applyPanel("A");
      applyPanel("B");
    }

    function bindPanel(panel) {
      $("mission" + panel).addEventListener("change", () => {
        fillDateDropdowns(panel);
      });

      // Ahora se aplica SOLO cuando presionas "Aplicar"
      $("apply" + panel).addEventListener("click", () => applyPanel(panel));

      $("file" + panel).addEventListener("change", (e) => {
        const file = e.target.files?.[0];
        if (file) importCSV(panel, file);
        e.target.value = "";
      });

      $("prev" + panel).addEventListener("click", () => {
        state[panel].page -= 1;
        renderPanel(panel);
      });

      $("next" + panel).addEventListener("click", () => {
        state[panel].page += 1;
        renderPanel(panel);
      });
    }

    // Init
    fillMissionOptions("A");
    fillMissionOptions("B");
    fillDateDropdowns("A");
    fillDateDropdowns("B");

    bindPanel("A");
    bindPanel("B");

    $("btnResetAll").addEventListener("click", resetAll);

    // primera carga
    applyPanel("A");
    applyPanel("B");
  </script>
</body>
</html>
@endsection
