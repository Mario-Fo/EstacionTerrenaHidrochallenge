<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>

  <style>
    .sr-only-focusable:active, .sr-only-focusable:focus {
      position: static !important;
      width: auto !important;
      height: auto !important;
      margin: 0 !important;
      overflow: visible !important;
      clip: auto !important;
      white-space: normal !important;
    }

    .telemetry-scroll {
      scrollbar-width: thin;
      scrollbar-color: transparent transparent;
      scrollbar-gutter: stable;
    }

    .telemetry-scroll::-webkit-scrollbar {
      width: 10px;
      height: 10px;
      background: transparent;
    }

    .telemetry-scroll::-webkit-scrollbar-track {
      background: transparent;
    }

    .telemetry-scroll::-webkit-scrollbar-thumb {
      background: transparent;
      border: 2px solid transparent;
      border-radius: 999px;
    }
  </style>

  <link
  rel="stylesheet"
  href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
/>
    </head>
<body class="bg-slate-950 text-slate-100 min-h-screen">
  <a href="#main" class="sr-only sr-only-focusable px-3 py-2 bg-slate-800 rounded-md m-2 inline-block">
    Saltar al contenido principal
  </a>

  <div class="min-h-screen flex flex-col">

    <!-- HEADER -->
    <header class="ssticky top-0 z-[2000] border-b border-slate-800 bg-slate-950/80 backdrop-blur">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="h-16 flex items-center justify-between">
          <div class="flex items-center gap-3">

            <!-- Logo placeholder circular -->
            <div class="h-10 w-10 rounded-full bg-slate-800/70 border border-slate-700 flex items-center justify-center">
              <span class="text-xs text-slate-300">Logo</span>
            </div>

            <div class="flex flex-col leading-tight">
              <span class="text-sm text-slate-400">Panel de misión</span>
              <span class="text-lg font-semibold tracking-wide">HYDRONAUTAS</span>
            </div>
          </div>

          <nav class="hidden sm:flex items-center gap-8">
            <a href="#" class="text-sm text-slate-300 hover:text-white transition">Dashboard</a>
            <a href="#" class="text-sm text-slate-300 hover:text-white transition">Guardar CSV</a>
            <a href="#" class="text-sm text-slate-300 hover:text-white transition">Configuración</a>
            <a href="#" class="text-sm text-slate-300 hover:text-white transition">Datos Historicos</a> 
          </nav>
          <!-- Header menu (solo nombre) -->
          <nav class="hidden sm:flex items-center gap-2">
            <span class="px-3 py-1.5 rounded-full border border-slate-800 bg-slate-900/40 text-slate-200 text-sm">
              Instituto Tecnológico de Matamoros
            </span>
          </nav>
        </div>
      </div>
    </header>

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
                <h1 class="text-lg font-semibold">Etapa de misión</h1>
                <span class="text-xs px-2.5 py-1 rounded-full border border-slate-700 bg-slate-950/60 text-slate-300">
                  Tiempo real (simulado)
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
                      <span>Progreso de misión</span>
                      <span id="missionProgressLabel" class="text-slate-200">0%</span>
                    </div>
                    <div class="h-2 w-full rounded-full bg-slate-800 overflow-hidden">
                      <div id="missionProgressBar" class="h-full w-0 bg-emerald-400"></div>
                    </div>
                  </div>
                  <div class="mt-4 rounded-lg border border-slate-800 bg-slate-900/40 p-3">
                    <p class="text-xs text-slate-400">Tiempo de misión</p>
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
                  <p class="mt-2 text-sm text-slate-400">
                    Fases: <span class="text-slate-200">espera</span>, ascenso, desacople, descenso, aterrizaje
                  </p>
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
                <!-- Apogeo mínimo 100m -->
                <div id="cardApogee" class="rounded-xl border border-slate-800 bg-slate-950/40 p-4">
                  <div class="flex items-start justify-between gap-3">
                    <div>
                      <p class="text-sm text-slate-400">Altura (mín. 100 m)</p>
                      <p class="mt-2 text-2xl font-semibold">
                        <span id="apogeeVal"></span>
                        <span class="text-sm font-medium text-slate-400">m</span>
                      </p>
                    </div>
                    <span id="apogeeBadge" class="text-[11px] px-2.5 py-1 rounded-full border border-slate-700 bg-slate-950/60 text-slate-300">
                      —
                    </span>
                  </div>
                </div>

                <!-- Velocidad máxima 8 m/s -->
                <div id="cardFall" class="rounded-xl border border-slate-800 bg-slate-950/40 p-4">
                  <div class="flex items-start justify-between gap-3">
                    <div>
                      <p class="text-sm text-slate-400">Velocidad de caída (máx. 8 m/s)</p>
                      <p class="mt-2 text-2xl font-semibold">
                        <span id="fallVal"></span>
                        <span class="text-sm font-medium text-slate-400">m/s</span>
                      </p>
                    </div>
                    <span id="fallBadge" class="text-[11px] px-2.5 py-1 rounded-full border border-slate-700 bg-slate-950/60 text-slate-300">
                      —
                    </span>
                  </div>
                </div>
                  <div id="cardAireTime" class="rounded-xl border border-slate-800 bg-slate-950/40 p-4">
                  <div class="flex items-start justify-between gap-3">
                    <div>
                      <p class="text-sm text-slate-400">Tiempo en el aire</p>
                      <p class="mt-2 text-2xl font-semibold">
                        <span id="aireTimeVal"></span>
                        <span class="text-sm font-medium text-slate-400">s</span>
                      </p>
                    </div>
                    <span id="aireTimeBadge" class="text-[11px] px-2.5 py-1 rounded-full border border-slate-700 bg-slate-950/60 text-slate-300">
                      —
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
              <article class="rounded-2xl border border-slate-800 bg-slate-900/30 p-5">
                <header class="flex items-center justify-between gap-3 flex-wrap">
                  <h2 class="text-lg font-semibold">Altura vs Tiempo</h2>
                  <div class="flex items-center gap-2">
                    <span class="text-xs px-2.5 py-1 rounded-full border border-slate-700 bg-slate-950/60 text-slate-300">
                      X: Tiempo (s)
                    </span>
                    <span class="text-xs px-2.5 py-1 rounded-full border border-slate-700 bg-slate-950/60 text-slate-300">
                      Y: Altura (m)
                    </span>
                  </div>
                </header>

                <div class="mt-4 rounded-xl border border-slate-800 bg-slate-950/40 p-4">
                  <svg viewBox="0 0 900 260" class="w-full h-[220px]" role="img" aria-label="Grafica estatica de altura vs tiempo">
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
                      <text x="18" y="25">Altura</text>
                      <text x="830" y="252">Tiempo</text>
                      <text x="30" y="224">0</text>
                      <text x="20" y="184">30</text>
                      <text x="20" y="144">60</text>
                      <text x="20" y="104">90</text>
                      <text x="14" y="64">120</text>
                    </g>

                    <path
                      d="M60 220 L140 205 L220 185 L300 155 L380 120 L460 95 L540 80 L620 70 L700 66 L780 64 L860 63"
                      fill="none"
                      stroke="currentColor"
                      class="text-emerald-400"
                      stroke-width="3"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                    />
                    <g fill="currentColor" class="text-emerald-400">
                      <circle cx="60" cy="220" r="4"/>
                      <circle cx="220" cy="185" r="4"/>
                      <circle cx="380" cy="120" r="4"/>
                      <circle cx="540" cy="80" r="4"/>
                      <circle cx="700" cy="66" r="4"/>
                      <circle cx="860" cy="63" r="4"/>
                    </g>
                  </svg>

                  <p class="mt-3 text-xs text-slate-400">
                    Placeholder estatico. Con telemetria real, esta grafica puede actualizarse por intervalo o WebSocket.
                  </p>
                </div>
              </article>

              <article class="rounded-2xl border border-slate-800 bg-slate-900/30 p-5">
                <header class="flex items-center justify-between">
                  <h2 class="text-lg font-semibold">Aceleracion vs Tiempo</h2>
                  <span class="text-xs px-2 py-1 rounded-full bg-slate-800 text-slate-300">-2G a +2G</span>
                </header>

                <div class="mt-4 rounded-xl border border-slate-800 bg-slate-950/40 p-4">
                  <svg viewBox="0 0 900 260" class="w-full h-[240px] sm:h-[260px] xl:h-[280px]" aria-label="Aceleracion vs tiempo">
                    <g stroke="currentColor" class="text-slate-700" opacity="0.4">
                      <line x1="60" y1="20" x2="60" y2="220"/>
                      <line x1="60" y1="120" x2="860" y2="120"/>
                    </g>

                    <g fill="currentColor" class="text-slate-400" font-size="12">
                      <text x="20" y="25">+2G</text>
                      <text x="30" y="125">0</text>
                      <text x="20" y="220">-2G</text>
                      <text x="830" y="250">Tiempo</text>
                    </g>

                    <path
                      d="M60 120 L140 100 L220 80 L300 110 L380 140 L460 150 L540 130 L620 120 L700 115 L780 118 L860 120"
                      fill="none"
                      stroke="currentColor"
                      class="text-amber-400"
                      stroke-width="3"
                      stroke-linecap="round"
                    />
                  </svg>
                </div>
              </article>

              <article class="rounded-2xl border border-slate-800 bg-slate-900/30 p-5">
                <header class="flex items-center justify-between">
                  <h2 class="text-lg font-semibold">Velocidad Vertical vs Tiempo</h2>
                  <span class="text-xs px-2 py-1 rounded-full bg-slate-800 text-slate-300">m/s</span>
                </header>

                <div class="mt-4 rounded-xl border border-slate-800 bg-slate-950/40 p-4">
                  <svg viewBox="0 0 900 260" class="w-full h-[240px] sm:h-[260px] xl:h-[280px]" aria-label="Velocidad vertical vs tiempo">
                    <g stroke="currentColor" class="text-slate-700" opacity="0.4">
                      <line x1="60" y1="20" x2="60" y2="220"/>
                      <line x1="60" y1="220" x2="860" y2="220"/>
                    </g>

                    <g fill="currentColor" class="text-slate-400" font-size="12">
                      <text x="20" y="25">200</text>
                      <text x="20" y="120">100</text>
                      <text x="30" y="220">0</text>
                      <text x="830" y="250">Tiempo</text>
                    </g>

                    <path
                      d="M60 220 L140 190 L220 150 L300 110 L380 80 L460 60 L540 70 L620 120 L700 160 L780 200 L860 220"
                      fill="none"
                      stroke="currentColor"
                      class="text-sky-400"
                      stroke-width="3"
                      stroke-linecap="round"
                    />
                  </svg>
                </div>
              </article>
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
                      <span id="aVal">0.98</span>
                      <span class="text-sm font-medium text-slate-400">g</span>
                    </p>
                  </div>

                  <div class="rounded-xl border border-slate-800 bg-slate-950/40 p-4">
                    <p class="text-sm text-slate-400">Aceleracion en Y</p>
                    <p class="mt-2 text-2xl font-semibold">
                      <span id="aVal">0.98</span>
                      <span class="text-sm font-medium text-slate-400">g</span>
                    </p>
                  </div>

                  <div class="rounded-xl border border-slate-800 bg-slate-950/40 p-4">
                    <p class="text-sm text-slate-400">Aceleracion en Z</p>
                    <p class="mt-2 text-2xl font-semibold">
                      <span id="aVal">0.98</span>
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
    <h2 class="text-lg font-semibold">Ubicación (GPS)</h2>
  </header>

  <div id="map" class="mt-4 h-72 w-full rounded-xl border border-slate-800 overflow-hidden"></div>

  <p class="mt-3 text-xs text-slate-400">
    Coordenadas actuales:
    <span id="coordsLabel" class="text-slate-200 tabular-nums">—</span>
  </p>
</section>
        </div>
      </main>
    </div>
  </div>
      <!-- FOOTER -->
       <footer class="border-t border-slate-800 bg-slate-950 mt-10">
  <div class="max-w-7xl mx-auto px-6 py-10">

    <!-- Parte superior: Equipo e Institución -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">

      <!-- Equipo -->
      <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6">
        
        <!-- Logo del equipo -->
        <div class="h-20 w-20 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center">
          <span class="text-xs text-slate-400 text-center">Logo<br>Equipo</span>
        </div>

        <!-- Nombre del equipo -->
        <div>
          <p class="text-sm text-slate-400 uppercase tracking-wider">Equipo</p>
          <h3 class="text-xl font-semibold text-white">
            HYDRONAUTAS MX
          </h3>
        </div>
      </div>

      <!-- Institución -->
      <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6 lg:justify-end">
        
        <!-- Logo institución -->
        <div class="h-20 w-20 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center">
          <span class="text-xs text-slate-400 text-center">Logo<br>ITM</span>
        </div>

        <div class="text-center sm:text-left lg:text-right">
          <p class="text-sm text-slate-400 uppercase tracking-wider">Institución</p>
          <h3 class="text-xl font-semibold text-white">
            Instituto Tecnológico de Matamoros
          </h3>
        </div>
      </div>
    </div>

    <!-- Separador -->
    <div class="border-t border-slate-800 my-8"></div>

    <!-- Integrantes -->
    <div>
      <h4 class="text-lg font-semibold mb-6 text-center lg:text-left">
        Integrantes del Proyecto
      </h4>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6 text-sm">

        <div class="rounded-xl border border-slate-800 bg-slate-900/40 p-4">
          <p class="font-semibold text-white">Ana Laura Vasquez Solis</p>
          <p class="text-slate-400 mt-1">Líder del equipo</p>
        </div>

        <div class="rounded-xl border border-slate-800 bg-slate-900/40 p-4">
          <p class="font-semibold text-white">Mario Alberto Flores Montellano</p>
          <p class="text-slate-400 mt-1">Estación terrena</p>
        </div>

        <div class="rounded-xl border border-slate-800 bg-slate-900/40 p-4">
          <p class="font-semibold text-white">Jose Carlos de la Garza Paz</p>
          <p class="text-slate-400 mt-1">Recuperación</p>
        </div>

        <div class="rounded-xl border border-slate-800 bg-slate-900/40 p-4">
          <p class="font-semibold text-white">Cintia Yllescas </p>
          <p class="text-slate-400 mt-1">Equipo de lanzamiento</p>
        </div>

        <div class="rounded-xl border border-slate-800 bg-slate-900/40 p-4">
          <p class="font-semibold text-white">Participante 5</p>
          <p class="text-slate-400 mt-1">Equipo de lanzamiento</p>
        </div>

      </div>
    </div>

    <!-- Copyright -->
    <div class="border-t border-slate-800 mt-8 pt-6 text-center text-xs text-slate-500">
      © 2026 HYDRONAUTAS — Sistema de Monitoreo y Control de Misión
    </div>

  </div>
</footer>
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <!-- Vanilla JS -->
  <script>
    (function () {
      // Sidebar open/close
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

      // Folder toggle
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

      // Formatting helpers
      function fmt2(n) { return Number(n).toFixed(2); }
      function fmt6(n) { return Number(n).toFixed(6); }
      function formatMissionTime(seconds) {
        const total = Math.max(0, Math.floor(Number(seconds) || 0));
        const minutes = String(Math.floor(total / 60)).padStart(2, "0");
        const secs = String(total % 60).padStart(2, "0");
        return `T+${minutes}:${secs}`;
      }

      // === STATIC telemetry (replace later) ===
      const telemetry = {
        pressure_pa: 101325,
        temperature_c: 28.5,
        humidity_rh: 55.2,
        lat_deg: 25.839818,
        lon_deg: -97.454501,
        accel_ms2: 0.98,

        // requisitos (ejemplo)
        apogee_m: 98.00, // prueba con 97, 99, 100, 120
        fall_ms: 8.00,    // prueba con 7, 8, 9, 12
        air_time_s: 45   // prueba con 15, 16, 17
      };

      // paint telemetry
      document.getElementById("pVal").textContent = fmt2(telemetry.pressure_pa);
      document.getElementById("tVal").textContent = fmt2(telemetry.temperature_c);
      document.getElementById("hVal").textContent = fmt2(telemetry.humidity_rh);
      document.getElementById("latVal").textContent = fmt6(telemetry.lat_deg);
      document.getElementById("lonVal").textContent = fmt6(telemetry.lon_deg);
      document.getElementById("aVal").textContent = fmt2(telemetry.accel_ms2);

      document.getElementById("apogeeVal").textContent = fmt2(telemetry.apogee_m);
      document.getElementById("fallVal").textContent = fmt2(telemetry.fall_ms);
      document.getElementById("aireTimeVal").textContent = fmt2(telemetry.air_time_s);
      const missionTimeEl = document.getElementById("missionTime");
      if (missionTimeEl) missionTimeEl.textContent = formatMissionTime(telemetry.air_time_s);

// ====== MAPA (Leaflet + OpenStreetMap) ======
let map, marker, tileLayer;
let lastLat = null, lastLon = null;

function initLeafletMap(lat, lon) {
  // Mapa (ligero)
  map = L.map("map", {
    zoomControl: true,
    attributionControl: true
  }).setView([lat, lon], 16);

  // Tiles OSM (por defecto, suficiente y simple)
  tileLayer = L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    maxZoom: 19,
    updateWhenIdle: true,      // reduce trabajo
    keepBuffer: 1,             // menos tiles en memoria
    crossOrigin: true
  }).addTo(map);

  // Marcador
  marker = L.marker([lat, lon]).addTo(map);

  updateCoordsLabel(lat, lon);
  lastLat = lat;
  lastLon = lon;
}

function updateCoordsLabel(lat, lon) {
  const el = document.getElementById("coordsLabel");
  if (el) el.textContent = `${lat.toFixed(6)}, ${lon.toFixed(6)}`;
}

function updateLeafletMarker(lat, lon) {
  if (!map || !marker) return;

  // Evita updates si no cambió de verdad (reduce red y renders)
  if (lastLat === lat && lastLon === lon) return;

  marker.setLatLng([lat, lon]);

  // Para minimizar movimiento/carga: solo recentrar si el marcador se sale del viewport
  const bounds = map.getBounds();
  if (!bounds.contains([lat, lon])) {
    map.setView([lat, lon], map.getZoom(), { animate: false });
  }

  updateCoordsLabel(lat, lon);
  lastLat = lat;
  lastLon = lon;
}

// Lee lat/lon desde tu UI (latVal, lonVal) y actualiza
function readLatLonFromDOM() {
  const lat = parseFloat(document.getElementById("latVal")?.textContent || "0");
  const lon = parseFloat(document.getElementById("lonVal")?.textContent || "0");
  return { lat, lon };
}

// Inicializa cuando el DOM esté listo
document.addEventListener("DOMContentLoaded", () => {
  const { lat, lon } = readLatLonFromDOM();
  initLeafletMap(lat, lon);

  // Observa cambios en latVal/lonVal (si los actualizas por telemetría)
  const latEl = document.getElementById("latVal");
  const lonEl = document.getElementById("lonVal");

  const observer = new MutationObserver(() => {
    const { lat, lon } = readLatLonFromDOM();
    updateLeafletMarker(lat, lon);
  });

  if (latEl) observer.observe(latEl, { childList: true, characterData: true, subtree: true });
  if (lonEl) observer.observe(lonEl, { childList: true, characterData: true, subtree: true });
});
      // === Reglas de requisitos ===
      // Apogeo: mínimo 100 m
      //   OK: >= 100
      //   CERCA: >= 98  (100 - 2)
      //   FUERA: < 98
      // Velocidad caída: máximo 8 m/s
      //   OK: <= 8
      //   CERCA: <= 10 (8 + 2)
      //   FUERA: > 10
      const APOGEE_MIN = 100;
      const FALL_MAX = 8;
      const AIRE_TIME_MAX = 15;

      function applyStatus(cardEl, badgeEl, status, label) {
        // Reset card
        cardEl.classList.remove(
          "border-emerald-500/60", "bg-emerald-500/10",
          "border-amber-500/60", "bg-amber-500/10",
          "border-rose-500/60", "bg-rose-500/10"
        );
        // Reset badge
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

      function statusByMaxTime(value, maxOk, nearMargin) {
        if (value >= maxOk) return "ok";
        if (value >= (maxOk - nearMargin)) return "near";
        return "bad";
      }

      // Apply requirements colors
      const apogeeCard = document.getElementById("cardApogee");
      const apogeeBadge = document.getElementById("apogeeBadge");
      const fallCard = document.getElementById("cardFall");
      const fallBadge = document.getElementById("fallBadge");
      const aireTimeCard = document.getElementById("cardAireTime");
      const aireTimeBadge = document.getElementById("aireTimeBadge");

      const apogeeStatus = statusByMin(telemetry.apogee_m, APOGEE_MIN, 5);
      const fallStatus = statusByMax(telemetry.fall_ms, FALL_MAX, 2);
      const aireTimeStatus = statusByMaxTime(telemetry.air_time_s, AIRE_TIME_MAX, 5);
      const stages = ["espera", "ascenso", "desacople", "descenso", "aterrizaje"];
      const stageNow = (document.getElementById("missionStage")?.textContent || "").trim().toLowerCase();
      const stageIndex = Math.max(0, stages.indexOf(stageNow));
      const missionProgress = Math.round((stageIndex / (stages.length - 1)) * 100);
      const missionProgressLabel = document.getElementById("missionProgressLabel");
      const missionProgressBar = document.getElementById("missionProgressBar");
      if (missionProgressLabel) missionProgressLabel.textContent = `${missionProgress}%`;
      if (missionProgressBar) missionProgressBar.style.width = `${missionProgress}%`;
      const stageChips = document.querySelectorAll("[data-stage-chip]");
      stageChips.forEach((chip) => {
        const isActive = chip.getAttribute("data-stage-chip") === stageNow;
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

      // Responsive sidebar behavior
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
