@extends ('layouts.app')
@section('title', 'Dashboard')
@section('content')
  @php
    $altitudeThreshold = (float) ($requirements['altitude_threshold'] ?? 100);
    $airTimeThreshold = (float) ($requirements['air_time_threshold'] ?? 45);
    $fallSpeedThreshold = (float) ($requirements['fall_speed_threshold'] ?? 8);
  @endphp
  <div class="min-h-screen flex flex-col">

    <!-- CONTENT -->
    <div class="flex-1 flex">
      <!-- Overlay (mobile) -->
      <div id="overlay" class="fixed inset-0 bg-black/50 z-30 hidden lg:hidden"></div>

      <!-- MAIN -->
      <main id="main" class="flex-1">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6 space-y-6">

          <!-- Row: Stage + Chart -->
          <section class="grid grid-cols-2 xl:grid-cols-1 gap-6">
            <article class="xl:col-span-1 rounded-2xl border border-slate-800 bg-slate-900/30 p-5">
              <header class="flex items-center justify-between">
                <h1 class="text-lg font-semibold">Etapa de mision</h1>
                <span class="text-xs px-2.5 py-1 rounded-full border border-slate-700 bg-slate-950/60 text-slate-300">
                  Tiempo real
                </span>
              </header>
              
              <div class="mt-4 grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div class="rounded-xl border border-slate-800 bg-slate-950/40 p-4">
                  <p class="text-sm text-slate-400">Estado actual</p>
                  <div class="mt-2 flex items-center gap-2">
                    <span class="inline-flex h-2.5 w-2.5 rounded-full bg-emerald-400"></span>
                    <p class="text-2xl font-semibold" id="missionStage">Desacople</p>
                  </div>
                  <div class="mt-4">
                    <div class="flex items-center justify-between text-xs text-slate-400 mb-1">
                      <span>Progreso de mision</span>
                      <span id="missionProgressLabel" class="text-slate-200">0%</span>
                    </div>
                    <div class="h-2 w-full rounded-full bg-slate-800 overflow-hidden">
                      <div id="missionProgressBar" class="h-full w-0 bg-emerald-400"></div>
                    </div>
                  </div>
                  <div class="mt-4 rounded-lg border border-slate-800 bg-slate-900/40 p-3">
                    <p class="text-xs text-slate-400">Tiempo de mision</p>
                    <p class="mt-1 text-lg font-semibold text-slate-100 tabular-nums" id="missionTime">T+00:00</p>
                  </div>
                  <div class="mt-4">
                    <p class="text-xs text-slate-400 mb-2">Fases</p>
                    <div class="flex flex-wrap gap-2 text-xs">
                      <span data-stage-chip="espera" class="px-2.5 py-1 rounded-full border border-slate-700 bg-slate-950/60 text-slate-300">Espera</span>
                      <span data-stage-chip="ascenso" class="px-2.5 py-1 rounded-full border border-slate-700 bg-slate-950/60 text-slate-300">Ascenso</span>
                      <span data-stage-chip="desacople" class="px-2.5 py-1 rounded-full border border-slate-700 bg-slate-950/60 text-slate-300">Desacople</span>
                      <span data-stage-chip="descenso" class="px-2.5 py-1 rounded-full border border-slate-700 bg-slate-950/60 text-slate-300">Descenso</span>
                      <span data-stage-chip="aterrizaje" class="px-2.5 py-1 rounded-full border border-slate-700 bg-slate-950/60 text-slate-300">Aterrizaje</span>
                    </div>
                  </div>
                  <div class="mt-4 rounded-lg border border-slate-800 bg-slate-900/40 p-3">
                    <p class="text-xs text-slate-400">Altura maxima</p>
                    <p class="mt-1 text-lg font-semibold text-slate-100 tabular-nums">
                      <span id="apogeeMaxVal">0.00</span>
                      <span class="text-sm font-medium text-slate-400">m</span>
                    </p>
                  </div>
                </div>
            <!-- REQUISITOS con estado de color -->
            <aside class="rounded-xl border border-slate-800 bg-slate-950/40 p-4">
              <header class="flex items-right justify-between">
                <h2 class="text-lg font-semibold">Requisitos</h2>
            <span class="text-xs px-2.5 py-1 rounded-full 
             border border-emerald-500 
             bg-emerald-500/20 
             text-emerald-300">
              En vivo
              </span>
              </header>

              <div class="mt-4 space-y-4">
                <!-- Apogeo mÃ­nimo 100m -->
                <div id="cardApogee" class="rounded-xl border border-slate-800 bg-slate-950/40 p-4">
                  <div class="flex items-start justify-between gap-3">
                    <div>
                      <p class="text-sm text-slate-400">Altura (min. {{ number_format($altitudeThreshold, 2, '.', '') }} m)</p>
                      <p class="mt-2 text-2xl font-semibold">
                        <span id="apogeeVal"></span>
                        <span class="text-sm font-medium text-slate-400">m</span>
                      </p>
                    </div>
                    <span id="apogeeBadge" class="text-[11px] px-2.5 py-1 rounded-full border border-slate-700 bg-slate-950/60 text-slate-300">
                      â€”
                    </span>
                  </div>
                </div>

                <!-- Velocidad mÃ¡xima 8 m/s -->
                <div id="cardFall" class="rounded-xl border border-slate-800 bg-slate-950/40 p-4">
                  <div class="flex items-start justify-between gap-3">
                    <div>
                      <p class="text-sm text-slate-400">Velocidad de caida (max. {{ number_format($fallSpeedThreshold, 2, '.', '') }} m/s)</p>
                      <p class="mt-2 text-2xl font-semibold">
                        <span id="fallVal"></span>
                        <span class="text-sm font-medium text-slate-400">m/s</span>
                      </p>
                    </div>
                    <span id="fallBadge" class="text-[11px] px-2.5 py-1 rounded-full border border-slate-700 bg-slate-950/60 text-slate-300">
                      â€”
                    </span>
                  </div>
                </div>
                  <div id="cardAireTime" class="rounded-xl border border-slate-800 bg-slate-950/40 p-4">
                  <div class="flex items-start justify-between gap-3">
                    <div>
                      <p class="text-sm text-slate-400">Tiempo en el aire (min. {{ number_format($airTimeThreshold, 2, '.', '') }} s)</p>
                      <p class="mt-2 text-2xl font-semibold">
                        <span id="aireTimeVal"></span>
                        <span class="text-sm font-medium text-slate-400">s</span>
                      </p>
                    </div>
                    <span id="aireTimeBadge" class="text-[11px] px-2.5 py-1 rounded-full border border-slate-700 bg-slate-950/60 text-slate-300">
                      â€”
                    </span>
                  </div>
                </div>
              </div>
            </aside>

              </div>
            </article>
          </section>

          <section class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            <div class="lg:col-span-2 space-y-6">
              @include('Graficas.altura_vs_tiempo')
              @include('Graficas.aceleracion_vs_tiempo')
              @include('Graficas.velocidad_vertical_vs_tiempo')
            </div>

            <aside class="lg:col-span-1 lg:sticky lg:top-24 self-start">
              <article class="telemetry-scroll rounded-2xl border border-slate-800 bg-slate-900/30 p-5 max-h-[calc(100vh-7rem)] overflow-y-auto">
                <header class="flex items-center justify-between flex-wrap gap-2">
                  <h2 class="text-lg font-semibold">Telemetria (SI)</h2>
                  <p class="text-xs text-slate-400">Formato: 2 decimales | Lat/Long: 6 decimales</p>
                </header>

                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-4">
                  <div class="rounded-xl border border-slate-800 bg-slate-950/40 p-4">
                    <p class="text-sm text-slate-400">Presion</p>
                    <p class="mt-2 text-2xl font-semibold">
                      <span id="pVal">101325.00</span>
                      <span class="text-sm font-medium text-slate-400">Pa</span>
                    </p>
                  </div>

                  <div class="rounded-xl border border-slate-800 bg-slate-950/40 p-4">
                    <p class="text-sm text-slate-400">Temperatura</p>
                    <p class="mt-2 text-2xl font-semibold">
                      <span id="tVal">28.50</span>
                      <span class="text-sm font-medium text-slate-400">C</span>
                    </p>
                  </div>

                  <div class="rounded-xl border border-slate-800 bg-slate-950/40 p-4">
                    <p class="text-sm text-slate-400">Humedad</p>
                    <p class="mt-2 text-2xl font-semibold">
                      <span id="hVal">55.20</span>
                      <span class="text-sm font-medium text-slate-400">%RH</span>
                    </p>
                  </div>

                  <div class="rounded-xl border border-slate-800 bg-slate-950/40 p-4">
                    <p class="text-sm text-slate-400">Latitud</p>
                    <p class="mt-2 text-2xl font-semibold tabular-nums">
                      <span id="latVal">25.839818</span>
                      <span class="text-sm font-medium text-slate-400">deg</span>
                    </p>
                  </div>

                  <div class="rounded-xl border border-slate-800 bg-slate-950/40 p-4">
                    <p class="text-sm text-slate-400">Longitud</p>
                    <p class="mt-2 text-2xl font-semibold tabular-nums">
                      <span id="lonVal">-97.454501</span>
                      <span class="text-sm font-medium text-slate-400">deg</span>
                    </p>
                  </div>

                  <div class="rounded-xl border border-slate-800 bg-slate-950/40 p-4">
                    <p class="text-sm text-slate-400">Altitud</p>
                    <p class="mt-2 text-2xl font-semibold tabular-nums">
                      <span id="altVal">12.345436</span>
                      <span class="text-sm font-medium text-slate-400">m</span>
                    </p>
                  </div>

                  <div class="rounded-xl border border-slate-800 bg-slate-950/40 p-4">
                    <p class="text-sm text-slate-400">Aceleracion</p>
                    <p class="mt-2 text-2xl font-semibold">
                      <span id="aVal">0.98</span>
                      <span class="text-sm font-medium text-slate-400">m/s2</span>
                    </p>
                  </div>

                  <div class="rounded-xl border border-slate-800 bg-slate-950/40 p-4">
                    <p class="text-sm text-slate-400">Aceleracion en X</p>
                    <p class="mt-2 text-2xl font-semibold">
                      <span id="axVal">0.98</span>
                      <span class="text-sm font-medium text-slate-400">g</span>
                    </p>
                  </div>

                  <div class="rounded-xl border border-slate-800 bg-slate-950/40 p-4">
                    <p class="text-sm text-slate-400">Aceleracion en Y</p>
                    <p class="mt-2 text-2xl font-semibold">
                      <span id="ayVal">0.98</span>
                      <span class="text-sm font-medium text-slate-400">g</span>
                    </p>
                  </div>

                  <div class="rounded-xl border border-slate-800 bg-slate-950/40 p-4">
                    <p class="text-sm text-slate-400">Aceleracion en Z</p>
                    <p class="mt-2 text-2xl font-semibold">
                      <span id="azVal">0.98</span>
                      <span class="text-sm font-medium text-slate-400">g</span>
                    </p>
                  </div>

                  <div class="rounded-xl border border-slate-800 bg-slate-950/40 p-4">
                    <p class="text-sm text-slate-400">RPM</p>
                    <p class="mt-2 text-2xl font-semibold">
                      <span id="rpmVal">54</span>
                      <span class="text-sm font-medium text-slate-400">RPM</span>
                    </p>
                  </div>
                </div>
              </article>
            </aside>
          </section>
          <section class="rounded-2xl border border-slate-800 bg-slate-900/30 p-5">
  <header class="flex items-center justify-between flex-wrap gap-2">
    <h2 class="text-lg font-semibold">Ubicacion (GPS)</h2>
  </header>

  <div id="map" class="mt-4 h-72 w-full rounded-xl border border-slate-800 overflow-hidden"></div>

  <p class="mt-3 text-xs text-slate-400">
    Coordenadas actuales:
    <span id="coordsLabel" class="text-slate-200 tabular-nums">â€”</span>
  </p>
</section>
        </div>
      </main>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js"></script>
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <!-- Vanilla JS -->
  <script>
    (function () {
      const sidebar = document.getElementById("sidebar");
      const overlay = document.getElementById("overlay");
      const btnSidebar = document.getElementById("btnSidebar");
      const btnCloseSidebar = document.getElementById("btnCloseSidebar");
      function openSidebar() {
        sidebar.classList.remove("-translate-x-full");
        overlay.classList.remove("hidden");
        btnSidebar.setAttribute("aria-expanded", "true");
      }
      function closeSidebar() {
        sidebar.classList.add("-translate-x-full");
        overlay.classList.add("hidden");
        btnSidebar.setAttribute("aria-expanded", "false");
      }

      btnSidebar?.addEventListener("click", openSidebar);
      btnCloseSidebar?.addEventListener("click", closeSidebar);
      overlay?.addEventListener("click", closeSidebar);

      const btnFolder = document.getElementById("btnFolder");
      const folderContent = document.getElementById("folderContent");
      const chev = document.getElementById("chev");
      btnFolder?.addEventListener("click", () => {
        const expanded = btnFolder.getAttribute("aria-expanded") === "true";
        btnFolder.setAttribute("aria-expanded", String(!expanded));
        folderContent.classList.toggle("hidden");
        chev.style.transform = expanded ? "rotate(0deg)" : "rotate(180deg)";
        chev.style.transition = "transform 150ms ease";
      });

      const APOGEE_MIN = Number(@json($altitudeThreshold));
      const FALL_MAX = Number(@json($fallSpeedThreshold));
      const AIRE_TIME_MIN = Number(@json($airTimeThreshold));

      function fmt2(n) { return Number(n).toFixed(2); }
      function fmt6(n) { return Number(n).toFixed(6); }
      function formatMissionTime(seconds) {
        const total = Math.max(0, Math.floor(Number(seconds) || 0));
        const minutes = String(Math.floor(total / 60)).padStart(2, "0");
        const secs = String(total % 60).padStart(2, "0");
        return `T+${minutes}:${secs}`;
      }

      function setText(id, value) {
        const el = document.getElementById(id);
        if (el) el.textContent = value;
      }

      const telemetry = {
        pressure_pa: null,
        temperature_c: null,
        humidity_rh: null,
        lat_deg: null,
        lon_deg: null,
        alt_m: null,
        accel_ms2: null,
        accel_x_g: null,
        accel_y_g: null,
        accel_z_g: null,
        rpm: null,
        apogee_m: 0,
        fall_ms: 0,
        air_time_s: 0
      };

      let maxApogeeSeen = 0;
      let missionStartAt = null;
      let lastSampleAt = null;
      let lastAltitude = null;
      let lastTelemetryRowId = null;

      function paintTelemetryAside() {
        setText("pVal", telemetry.pressure_pa === null ? "--" : fmt2(telemetry.pressure_pa));
        setText("tVal", telemetry.temperature_c === null ? "--" : fmt2(telemetry.temperature_c));
        setText("hVal", telemetry.humidity_rh === null ? "--" : fmt2(telemetry.humidity_rh));
        setText("latVal", telemetry.lat_deg === null ? "--" : fmt6(telemetry.lat_deg));
        setText("lonVal", telemetry.lon_deg === null ? "--" : fmt6(telemetry.lon_deg));
        setText("altVal", telemetry.alt_m === null ? "--" : fmt2(telemetry.alt_m));
        setText("aVal", telemetry.accel_ms2 === null ? "--" : fmt2(telemetry.accel_ms2));
        setText("axVal", telemetry.accel_x_g === null ? "--" : fmt2(telemetry.accel_x_g));
        setText("ayVal", telemetry.accel_y_g === null ? "--" : fmt2(telemetry.accel_y_g));
        setText("azVal", telemetry.accel_z_g === null ? "--" : fmt2(telemetry.accel_z_g));
        setText("rpmVal", telemetry.rpm === null ? "--" : String(Math.round(telemetry.rpm)));
      }

      const chartTimeline = ["5", "10", "15", "20", "25", "30"];
      function createRealtimeChart(canvasId, label, color, initialValue) {
        if (typeof Chart === "undefined") return null;
        const canvas = document.getElementById(canvasId);
        if (!canvas) return null;
        return new Chart(canvas.getContext("2d"), {
          type: "line",
          data: {
            labels: chartTimeline,
            datasets: [{
              label,
              data: Array(chartTimeline.length).fill(initialValue),
              borderColor: color,
              backgroundColor: color,
              borderWidth: 3,
              pointRadius: 2,
              tension: 0.25
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: false,
            plugins: {
              legend: { display: false }
            },
            scales: {
              x: { title: { display: true, text: "Tiempo (s)" } },
              y: { beginAtZero: false }
            }
          }
        });
      }

      function pushChartValue(chart, value) {
        if (!chart) return;
        const points = chart.data.datasets[0].data;
        points.push(value);
        while (points.length > chartTimeline.length) points.shift();
        chart.update("none");
      }

      const chartAlturaTiempo = createRealtimeChart("chartAlturaTiempo", "Altura (m)", "#34d399", 0);
      const chartAceleracionTiempo = createRealtimeChart("chartAceleracionTiempo", "Aceleracion", "#f59e0b", 0);
      const chartVelocidadTiempo = createRealtimeChart("chartVelocidadTiempo", "Velocidad vertical (m/s)", "#38bdf8", 0);

      const missionTimeEl = document.getElementById("missionTime");
      const missionStageEl = document.getElementById("missionStage");
      const missionProgressLabel = document.getElementById("missionProgressLabel");
      const missionProgressBar = document.getElementById("missionProgressBar");
      const stageChips = document.querySelectorAll("[data-stage-chip]");
      const stages = ["espera", "ascenso", "desacople", "descenso", "aterrizaje"];
      const stageLabels = {
        espera: "Espera",
        ascenso: "Ascenso",
        desacople: "Desacople",
        descenso: "Descenso",
        aterrizaje: "Aterrizaje"
      };

      function stageFromAirTime(seconds) {
        const s = Number(seconds) || 0;
        if (s < 10) return "espera";
        if (s < 30) return "ascenso";
        if (s < 40) return "desacople";
        if (s < 50) return "descenso";
        return "aterrizaje";
      }

      function updateMissionFromAirTime(seconds) {
        const currentStage = stageFromAirTime(seconds);
        const stageIndex = Math.max(0, stages.indexOf(currentStage));
        const missionProgress = Math.round((stageIndex / (stages.length - 1)) * 100);

        if (missionTimeEl) missionTimeEl.textContent = formatMissionTime(seconds);
        if (missionStageEl) missionStageEl.textContent = stageLabels[currentStage];
        if (missionProgressLabel) missionProgressLabel.textContent = `${missionProgress}%`;
        if (missionProgressBar) missionProgressBar.style.width = `${missionProgress}%`;

        stageChips.forEach((chip) => {
          const isActive = chip.getAttribute("data-stage-chip") === currentStage;
          chip.classList.remove(
            "border-emerald-500/50", "bg-emerald-500/10", "text-emerald-200",
            "border-slate-700", "bg-slate-950/60", "text-slate-300"
          );
          if (isActive) {
            chip.classList.add("border-emerald-500/50", "bg-emerald-500/10", "text-emerald-200");
          } else {
            chip.classList.add("border-slate-700", "bg-slate-950/60", "text-slate-300");
          }
        });
      }

      let map = null;
      let marker = null;
      let lastLat = null;
      let lastLon = null;

      function updateCoordsLabel(lat, lon) {
        setText("coordsLabel", `${lat.toFixed(6)}, ${lon.toFixed(6)}`);
      }

      function initLeafletMap(lat, lon) {
        if (typeof L === "undefined" || map) return;
        map = L.map("map", { zoomControl: true, attributionControl: true }).setView([lat, lon], 16);
        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
          maxZoom: 19,
          updateWhenIdle: true,
          keepBuffer: 1,
          crossOrigin: true
        }).addTo(map);
        marker = L.marker([lat, lon]).addTo(map);
        lastLat = lat;
        lastLon = lon;
        updateCoordsLabel(lat, lon);
      }

      function updateLeafletMarker(lat, lon) {
        if (!map || !marker) return;
        if (lastLat === lat && lastLon === lon) return;
        marker.setLatLng([lat, lon]);
        if (!map.getBounds().contains([lat, lon])) {
          map.setView([lat, lon], map.getZoom(), { animate: false });
        }
        lastLat = lat;
        lastLon = lon;
        updateCoordsLabel(lat, lon);
      }

      function applyStatus(cardEl, badgeEl, status, label) {
        cardEl.classList.remove(
          "border-emerald-500/60", "bg-emerald-500/10",
          "border-amber-500/60", "bg-amber-500/10",
          "border-rose-500/60", "bg-rose-500/10"
        );
        badgeEl.classList.remove(
          "border-emerald-500/50", "bg-emerald-500/10", "text-emerald-200",
          "border-amber-500/50", "bg-amber-500/10", "text-amber-200",
          "border-rose-500/50", "bg-rose-500/10", "text-rose-200"
        );

        if (status === "ok") {
          cardEl.classList.add("border-emerald-500/60", "bg-emerald-500/10");
          badgeEl.classList.add("border-emerald-500/50", "bg-emerald-500/10", "text-emerald-200");
        } else if (status === "near") {
          cardEl.classList.add("border-amber-500/60", "bg-amber-500/10");
          badgeEl.classList.add("border-amber-500/50", "bg-amber-500/10", "text-amber-200");
        } else {
          cardEl.classList.add("border-rose-500/60", "bg-rose-500/10");
          badgeEl.classList.add("border-rose-500/50", "bg-rose-500/10", "text-rose-200");
        }

        badgeEl.textContent = label;
      }

      function statusByMin(value, minOk, nearMargin) {
        if (value >= minOk) return "ok";
        if (value >= (minOk - nearMargin)) return "near";
        return "bad";
      }

      function statusByMax(value, maxOk, nearMargin) {
        if (value <= maxOk) return "ok";
        if (value <= (maxOk + nearMargin)) return "near";
        return "bad";
      }

      const apogeeCard = document.getElementById("cardApogee");
      const apogeeBadge = document.getElementById("apogeeBadge");
      const apogeeMaxEl = document.getElementById("apogeeMaxVal");
      const fallCard = document.getElementById("cardFall");
      const fallBadge = document.getElementById("fallBadge");
      const aireTimeCard = document.getElementById("cardAireTime");
      const aireTimeBadge = document.getElementById("aireTimeBadge");

      function paintRequirements() {
        const currentAltitude = Number(telemetry.alt_m) || 0;
        setText("apogeeVal", fmt2(currentAltitude));
        setText("fallVal", fmt2(telemetry.fall_ms));
        setText("aireTimeVal", fmt2(telemetry.air_time_s));
        if (currentAltitude > maxApogeeSeen) {
          maxApogeeSeen = currentAltitude;
          if (apogeeMaxEl) apogeeMaxEl.textContent = fmt2(maxApogeeSeen);
        }
        updateMissionFromAirTime(telemetry.air_time_s);

        const apogeeStatus = statusByMin(currentAltitude, APOGEE_MIN, 5);
        const fallStatus = statusByMax(telemetry.fall_ms, FALL_MAX, 1);
        const aireTimeStatus = statusByMin(telemetry.air_time_s, AIRE_TIME_MIN, 3);

        applyStatus(
          apogeeCard,
          apogeeBadge,
          apogeeStatus,
          apogeeStatus === "ok" ? "CUMPLE" : apogeeStatus === "near" ? "CERCA" : "FUERA"
        );
        applyStatus(
          fallCard,
          fallBadge,
          fallStatus,
          fallStatus === "ok" ? "CUMPLE" : fallStatus === "near" ? "CERCA" : "FUERA"
        );
        applyStatus(
          aireTimeCard,
          aireTimeBadge,
          aireTimeStatus,
          aireTimeStatus === "ok" ? "CUMPLE" : aireTimeStatus === "near" ? "CERCA" : "FUERA"
        );
      }

      function applyTelemetryFromApi(sample) {
        const rowId = Number(sample?.id_db);
        if (Number.isFinite(rowId)) {
          if (lastTelemetryRowId === rowId) return;
          lastTelemetryRowId = rowId;
        }

        const now = Date.now();

        telemetry.pressure_pa = Number(sample.pres);
        telemetry.temperature_c = Number(sample.temp);
        telemetry.humidity_rh = Number(sample.hum);
        telemetry.lat_deg = Number(sample.lat);
        telemetry.lon_deg = Number(sample.long);
        telemetry.alt_m = Number(sample.alt);
        telemetry.accel_x_g = Number(sample.accX);
        telemetry.accel_y_g = Number(sample.accY);
        telemetry.accel_z_g = Number(sample.accZ);
        telemetry.rpm = Number(sample.RPM);

        telemetry.accel_ms2 = Math.sqrt(
          Math.pow(telemetry.accel_x_g, 2) +
          Math.pow(telemetry.accel_y_g, 2) +
          Math.pow(telemetry.accel_z_g, 2)
        );

        if (lastSampleAt !== null && lastAltitude !== null) {
          const dt = (now - lastSampleAt) / 1000;
          if (dt > 0.05) {
            const vz = (telemetry.alt_m - lastAltitude) / dt;
            telemetry.fall_ms = Math.max(0, -vz);
          }
        }

        if (missionStartAt === null) missionStartAt = now;
        telemetry.air_time_s = (now - missionStartAt) / 1000;
        telemetry.apogee_m = telemetry.alt_m;

        lastSampleAt = now;
        lastAltitude = telemetry.alt_m;

        paintTelemetryAside();
        paintRequirements();
        pushChartValue(chartAlturaTiempo, telemetry.alt_m);
        pushChartValue(chartAceleracionTiempo, telemetry.accel_ms2);
        pushChartValue(chartVelocidadTiempo, telemetry.fall_ms);

        if (Number.isFinite(telemetry.lat_deg) && Number.isFinite(telemetry.lon_deg)) {
          if (!map) initLeafletMap(telemetry.lat_deg, telemetry.lon_deg);
          updateLeafletMarker(telemetry.lat_deg, telemetry.lon_deg);
        }
      }

      async function fetchLatestTelemetry() {
        try {
          const res = await fetch("/api/lecturas/ultima", {
            method: "GET",
            headers: { "Accept": "application/json" },
            cache: "no-store",
          });
          if (!res.ok) throw new Error(`HTTP ${res.status}`);
          const json = await res.json();
          if (!json?.ok || !json?.data) return;
          applyTelemetryFromApi(json.data);
        } catch (error) {
          console.error("No se pudo leer /api/lecturas/ultima:", error);
        }
      }

      paintTelemetryAside();
      paintRequirements();
      fetchLatestTelemetry();
      setInterval(fetchLatestTelemetry, 300);

      const mq = window.matchMedia("(min-width: 1024px)");
      mq.addEventListener?.("change", (e) => {
        if (e.matches) {
          overlay.classList.add("hidden");
          sidebar.classList.remove("-translate-x-full");
          btnSidebar.setAttribute("aria-expanded", "false");
        } else {
          sidebar.classList.add("-translate-x-full");
        }
      });
    })();
  </script>
</body>
</html>
@endsection

