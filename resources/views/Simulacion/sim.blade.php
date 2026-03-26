@extends ('layouts.app')

@section('content')
    <!-- Main -->
    <main class="flex-1">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
        <!-- Form + Status -->
        <section class="grid grid-cols-1 xl:grid-cols-3 gap-6">
          <!-- Formulario -->
          <article class="xl:col-span-1 rounded-2xl border border-slate-800 bg-slate-900/30 p-5">
            <header class="mb-4">
              <h3 class="text-lg font-semibold">Parámetros de simulación</h3>
              <p class="text-sm text-slate-400 mt-1">Ingresa datos de prueba para la misión</p>
            </header>

            <form id="simulationForm" class="space-y-4">
              <div>
                <label class="block text-sm text-slate-300 mb-1">Nombre de la misión</label>
                <input id="missionName" type="text" value="HYDRONAUTAS TEST FLIGHT"
                  class="w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-3 outline-none focus:border-cyan-400 text-sm" />
              </div>

              <div>
                <label class="block text-sm text-slate-300 mb-1">Altura objetivo (m)</label>
                <input id="targetHeight" type="number" value="120"
                  class="w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-3 outline-none focus:border-cyan-400 text-sm" />
              </div>

              <div>
                <label class="block text-sm text-slate-300 mb-1">Velocidad máxima permitida (m/s)</label>
                <input id="maxFallSpeed" type="number" value="8"
                  class="w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-3 outline-none focus:border-cyan-400 text-sm" />
              </div>

              <div>
                <label class="block text-sm text-slate-300 mb-1">Apogeo mínimo esperado (m)</label>
                <input id="minApogee" type="number" value="100"
                  class="w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-3 outline-none focus:border-cyan-400 text-sm" />
              </div>

              <div>
                <label class="block text-sm text-slate-300 mb-1">Latitud</label>
                <input id="inputLat" type="number" step="0.000001" value="25.839818"
                  class="w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-3 outline-none focus:border-cyan-400 text-sm" />
              </div>

              <div>
                <label class="block text-sm text-slate-300 mb-1">Longitud</label>
                <input id="inputLon" type="number" step="0.000001" value="-97.454501"
                  class="w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-3 outline-none focus:border-cyan-400 text-sm" />
              </div>

              <div>
                <label class="block text-sm text-slate-300 mb-1">Tipo de carga útil</label>
                <select id="payloadType"
                  class="w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-3 outline-none focus:border-cyan-400 text-sm">
                  <option>Sensor ambiental</option>
                  <option>Telemetría básica</option>
                  <option>Cámara</option>
                  <option>CanSat</option>
                </select>
              </div>

              <button type="button" id="runSimulation"
                class="w-full rounded-xl bg-cyan-500/20 border border-cyan-400/40 text-cyan-300 px-4 py-3 font-medium hover:bg-cyan-500/30 transition">
                Simular vuelo
              </button>
            </form>
          </article>

          <!-- Estado -->
          <article class="xl:col-span-2 rounded-2xl border border-slate-800 bg-slate-900/30 p-5">
            <header class="flex items-center justify-between flex-wrap gap-3">
              <div>
                <h3 class="text-lg font-semibold">Estado de la simulación</h3>
                <p class="text-sm text-slate-400">Vista general del vuelo demo</p>
              </div>
              <span id="simStatusBadge" class="px-3 py-1 rounded-full border border-slate-700 bg-slate-950/60 text-slate-300 text-xs">
                En espera
              </span>
            </header>

            <div class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="rounded-xl border border-slate-800 bg-slate-950/40 p-4">
                <p class="text-sm text-slate-400">Misión</p>
                <p id="missionLabel" class="mt-2 text-xl font-semibold">HYDRONAUTAS TEST FLIGHT</p>
              </div>

              <div class="rounded-xl border border-slate-800 bg-slate-950/40 p-4">
                <p class="text-sm text-slate-400">Resultado</p>
                <p id="simulationResult" class="mt-2 text-xl font-semibold text-emerald-300">Lista para simular</p>
              </div>

              <div class="rounded-xl border border-slate-800 bg-slate-950/40 p-4">
                <p class="text-sm text-slate-400">Etapa actual</p>
                <p id="currentStage" class="mt-2 text-2xl font-semibold">Espera</p>
              </div>

              <div class="rounded-xl border border-slate-800 bg-slate-950/40 p-4">
                <p class="text-sm text-slate-400">Payload</p>
                <p id="payloadLabel" class="mt-2 text-xl font-semibold">Sensor ambiental</p>
              </div>
            </div>

            <div class="mt-5 rounded-xl border border-slate-800 bg-slate-950/40 p-4">
              <p class="text-sm text-slate-400 mb-3">Secuencia de misión</p>
              <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                <div id="stageEspera" class="stage-card rounded-xl border border-emerald-500/50 bg-emerald-500/10 px-4 py-3 text-center text-sm font-medium text-emerald-300">
                  Espera
                </div>
                <div id="stageAscenso" class="stage-card rounded-xl border border-slate-700 bg-slate-900/40 px-4 py-3 text-center text-sm">
                  Ascenso
                </div>
                <div id="stageDesacople" class="stage-card rounded-xl border border-slate-700 bg-slate-900/40 px-4 py-3 text-center text-sm">
                  Desacople
                </div>
                <div id="stageDescenso" class="stage-card rounded-xl border border-slate-700 bg-slate-900/40 px-4 py-3 text-center text-sm">
                  Descenso
                </div>
                <div id="stageAterrizaje" class="stage-card rounded-xl border border-slate-700 bg-slate-900/40 px-4 py-3 text-center text-sm">
                  Aterrizaje
                </div>
              </div>
            </div>
          </article>
        </section>

        <!-- Gráficas demo -->
        <section class="grid grid-cols-1 xl:grid-cols-3 gap-6">
          <article class="xl:col-span-2 rounded-2xl border border-slate-800 bg-slate-900/30 p-5">
            <header class="flex items-center justify-between flex-wrap gap-2">
              <h3 class="text-lg font-semibold">Altura vs Tiempo</h3>
              <div class="flex gap-2">
                <span class="text-xs px-2.5 py-1 rounded-full border border-slate-700 bg-slate-950/60 text-slate-300">X: Tiempo</span>
                <span class="text-xs px-2.5 py-1 rounded-full border border-slate-700 bg-slate-950/60 text-slate-300">Y: Altura</span>
              </div>
            </header>

            <div class="mt-4 rounded-xl border border-slate-800 bg-slate-950/40 p-4">
              <svg viewBox="0 0 900 260" class="w-full h-[240px]">
                <g opacity="0.35" stroke="currentColor" class="text-slate-700">
                  <line x1="60" y1="20" x2="60" y2="220"/>
                  <line x1="60" y1="220" x2="860" y2="220"/>
                  <line x1="60" y1="180" x2="860" y2="180"/>
                  <line x1="60" y1="140" x2="860" y2="140"/>
                  <line x1="60" y1="100" x2="860" y2="100"/>
                  <line x1="60" y1="60" x2="860" y2="60"/>
                  <line x1="220" y1="20" x2="220" y2="220"/>
                  <line x1="380" y1="20" x2="380" y2="220"/>
                  <line x1="540" y1="20" x2="540" y2="220"/>
                  <line x1="700" y1="20" x2="700" y2="220"/>
                  <line x1="860" y1="20" x2="860" y2="220"/>
                </g>

                <g fill="currentColor" class="text-slate-400" font-size="12">
                  <text x="16" y="25">Altura</text>
                  <text x="820" y="250">Tiempo</text>
                </g>

                <path
                  d="M60 220 L140 205 L220 182 L300 150 L380 115 L460 90 L540 72 L620 60 L700 56 L780 55 L860 55"
                  fill="none"
                  stroke="currentColor"
                  class="text-emerald-400"
                  stroke-width="3"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
              </svg>
            </div>
          </article>

          <article class="rounded-2xl border border-slate-800 bg-slate-900/30 p-5">
            <header class="flex items-center justify-between">
              <h3 class="text-lg font-semibold">Validación demo</h3>
              <span class="text-xs px-2.5 py-1 rounded-full border border-emerald-500/40 bg-emerald-500/10 text-emerald-300">
                Simulada
              </span>
            </header>

            <div class="mt-4 space-y-4">
              <div id="reqApogee" class="rounded-xl border border-emerald-500/50 bg-emerald-500/10 p-4">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-sm text-slate-300">Apogeo mínimo</p>
                    <p id="reqApogeeText" class="text-xl font-semibold mt-2">100.00 m</p>
                  </div>
                  <span class="px-2.5 py-1 rounded-full text-xs border border-emerald-500/40 bg-emerald-500/10 text-emerald-300">
                    CUMPLE
                  </span>
                </div>
              </div>

              <div id="reqFall" class="rounded-xl border border-emerald-500/50 bg-emerald-500/10 p-4">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-sm text-slate-300">Velocidad máxima</p>
                    <p id="reqFallText" class="text-xl font-semibold mt-2">8.00 m/s</p>
                  </div>
                  <span class="px-2.5 py-1 rounded-full text-xs border border-emerald-500/40 bg-emerald-500/10 text-emerald-300">
                    CUMPLE
                  </span>
                </div>
              </div>

              <div class="rounded-xl border border-slate-800 bg-slate-950/40 p-4">
                <p class="text-sm text-slate-400">Mensaje del sistema</p>
                <p id="systemMessage" class="mt-2 text-sm text-slate-200">
                  Todo listo para ejecutar una simulación frontend.
                </p>
              </div>
            </div>
          </article>
        </section>

        <!-- Telemetría -->
        <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <article class="lg:col-span-2 rounded-2xl border border-slate-800 bg-slate-900/30 p-5">
            <header class="flex items-center justify-between flex-wrap gap-2">
              <h3 class="text-lg font-semibold">Telemetría simulada</h3>
              <span class="text-xs text-slate-400">Valores visuales demo</span>
            </header>

            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
              <div class="rounded-xl border border-slate-800 bg-slate-950/40 p-4">
                <p class="text-sm text-slate-400">Presión</p>
                <p id="pressureVal" class="mt-2 text-2xl font-semibold">101325.00 <span class="text-sm text-slate-400">Pa</span></p>
              </div>

              <div class="rounded-xl border border-slate-800 bg-slate-950/40 p-4">
                <p class="text-sm text-slate-400">Temperatura</p>
                <p id="tempVal" class="mt-2 text-2xl font-semibold">28.50 <span class="text-sm text-slate-400">°C</span></p>
              </div>

              <div class="rounded-xl border border-slate-800 bg-slate-950/40 p-4">
                <p class="text-sm text-slate-400">Humedad</p>
                <p id="humVal" class="mt-2 text-2xl font-semibold">55.20 <span class="text-sm text-slate-400">%RH</span></p>
              </div>

              <div class="rounded-xl border border-slate-800 bg-slate-950/40 p-4">
                <p class="text-sm text-slate-400">Latitud</p>
                <p id="latVal" class="mt-2 text-2xl font-semibold tabular-nums">25.839818 <span class="text-sm text-slate-400">°</span></p>
              </div>

              <div class="rounded-xl border border-slate-800 bg-slate-950/40 p-4">
                <p class="text-sm text-slate-400">Longitud</p>
                <p id="lonVal" class="mt-2 text-2xl font-semibold tabular-nums">-97.454501 <span class="text-sm text-slate-400">°</span></p>
              </div>

              <div class="rounded-xl border border-slate-800 bg-slate-950/40 p-4">
                <p class="text-sm text-slate-400">Aceleración</p>
                <p id="accVal" class="mt-2 text-2xl font-semibold">1.20 <span class="text-sm text-slate-400">G</span></p>
              </div>
            </div>
          </article>

          <aside class="rounded-2xl border border-slate-800 bg-slate-900/30 p-5">
            <header class="flex items-center justify-between">
              <h3 class="text-lg font-semibold">Salida del sistema</h3>
              <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-emerald-500/40 bg-emerald-500/10 text-emerald-300 text-xs">
                <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                En vivo
              </span>
            </header>

            <div class="mt-4 space-y-4">
              <div class="rounded-xl border border-slate-800 bg-slate-950/40 p-4">
                <p class="text-sm text-slate-400">Decisión</p>
                <p id="decisionText" class="mt-2 text-2xl font-semibold text-emerald-300">Funciona</p>
              </div>

              <div class="rounded-xl border border-slate-800 bg-slate-950/40 p-4">
                <p class="text-sm text-slate-400">Resumen</p>
                <p id="summaryText" class="mt-2 text-sm text-slate-200">
                  La demo muestra una misión visualmente válida y lista para pruebas frontend.
                </p>
              </div>
            </div>
          </aside>
        </section>
      </div>
    </main>
  </div>

  <script>
    const runButton = document.getElementById("runSimulation");

    const missionName = document.getElementById("missionName");
    const targetHeight = document.getElementById("targetHeight");
    const maxFallSpeed = document.getElementById("maxFallSpeed");
    const minApogee = document.getElementById("minApogee");
    const inputLat = document.getElementById("inputLat");
    const inputLon = document.getElementById("inputLon");
    const payloadType = document.getElementById("payloadType");

    const missionLabel = document.getElementById("missionLabel");
    const payloadLabel = document.getElementById("payloadLabel");
    const currentStage = document.getElementById("currentStage");
    const simulationResult = document.getElementById("simulationResult");
    const simStatusBadge = document.getElementById("simStatusBadge");
    const systemMessage = document.getElementById("systemMessage");
    const decisionText = document.getElementById("decisionText");
    const summaryText = document.getElementById("summaryText");

    const reqApogeeText = document.getElementById("reqApogeeText");
    const reqFallText = document.getElementById("reqFallText");

    const latVal = document.getElementById("latVal");
    const lonVal = document.getElementById("lonVal");

    const pressureVal = document.getElementById("pressureVal");
    const tempVal = document.getElementById("tempVal");
    const humVal = document.getElementById("humVal");
    const accVal = document.getElementById("accVal");

    const stageCards = {
      espera: document.getElementById("stageEspera"),
      ascenso: document.getElementById("stageAscenso"),
      desacople: document.getElementById("stageDesacople"),
      descenso: document.getElementById("stageDescenso"),
      aterrizaje: document.getElementById("stageAterrizaje"),
    };

    function resetStages() {
      Object.values(stageCards).forEach(card => {
        card.className = "stage-card rounded-xl border border-slate-700 bg-slate-900/40 px-4 py-3 text-center text-sm";
      });
    }

    function activateStage(stageKey, colorClasses) {
      resetStages();
      stageCards[stageKey].className = `stage-card rounded-xl border px-4 py-3 text-center text-sm font-medium ${colorClasses}`;
    }

    function updateTelemetryDemo() {
      pressureVal.innerHTML = `100850.00 <span class="text-sm text-slate-400">Pa</span>`;
      tempVal.innerHTML = `29.40 <span class="text-sm text-slate-400">°C</span>`;
      humVal.innerHTML = `53.80 <span class="text-sm text-slate-400">%RH</span>`;
      accVal.innerHTML = `1.35 <span class="text-sm text-slate-400">G</span>`;
    }

    runButton.addEventListener("click", () => {
      missionLabel.textContent = missionName.value || "Misión demo";
      payloadLabel.textContent = payloadType.value;
      reqApogeeText.textContent = `${Number(minApogee.value || 100).toFixed(2)} m`;
      reqFallText.textContent = `${Number(maxFallSpeed.value || 8).toFixed(2)} m/s`;
      latVal.innerHTML = `${Number(inputLat.value || 0).toFixed(6)} <span class="text-sm text-slate-400">°</span>`;
      lonVal.innerHTML = `${Number(inputLon.value || 0).toFixed(6)} <span class="text-sm text-slate-400">°</span>`;

      simulationResult.textContent = "Simulación exitosa";
      simulationResult.className = "mt-2 text-xl font-semibold text-emerald-300";

      decisionText.textContent = "Funciona";
      decisionText.className = "mt-2 text-2xl font-semibold text-emerald-300";

      summaryText.textContent = "La simulación frontend indica que la misión funciona correctamente dentro del escenario visual demo.";
      systemMessage.textContent = "El sistema cargó los parámetros y marcó la misión como válida en modo demostración.";
      simStatusBadge.textContent = "Simulación completada";
      simStatusBadge.className = "px-3 py-1 rounded-full border border-emerald-500/40 bg-emerald-500/10 text-emerald-300 text-xs";

      updateTelemetryDemo();

      currentStage.textContent = "Ascenso";
      activateStage("ascenso", "border-cyan-500/50 bg-cyan-500/10 text-cyan-300");

      setTimeout(() => {
        currentStage.textContent = "Desacople";
        activateStage("desacople", "border-violet-500/50 bg-violet-500/10 text-violet-300");
      }, 900);

      setTimeout(() => {
        currentStage.textContent = "Descenso";
        activateStage("descenso", "border-amber-500/50 bg-amber-500/10 text-amber-300");
      }, 1800);

      setTimeout(() => {
        currentStage.textContent = "Aterrizaje";
        activateStage("aterrizaje", "border-emerald-500/50 bg-emerald-500/10 text-emerald-300");
      }, 2700);
    });
  </script>
</body>
</html>
@endsection