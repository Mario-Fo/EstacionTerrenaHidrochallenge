    
    <!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>HYDRONAUTAS | @yield('title', 'Bravo II')</title>

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
    <!-- HEADER -->
    <header class="ssticky top-0 z-[2000] border-b border-slate-800 bg-slate-950/80 backdrop-blur">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="h-16 flex items-center justify-between">
          <div class="flex items-center gap-3">

            <!-- Logo placeholder circular -->
            <div class="h-12 w-12 aspect-square overflow-hidden shrink-0 rounded-full border border-slate-700 bg-slate-800">
                    <img src="{{ asset('images/image.png') }}" alt="Logo" class="block h-full w-full rounded-full object-cover" />
            </div>

            <div class="flex flex-col leading-tight">
              <span class="text-sm text-slate-400">Panel de misión</span>
              <span class="text-lg font-semibold tracking-wide">HYDRONAUTAS</span>
            </div>
          </div>

          <nav class="hidden sm:flex items-center gap-8">
            <a href="{{ route('dashboard') }}" class="text-sm text-slate-300 hover:text-white transition">Dashboard</a>
            <a href="{{ route('comparacion') }}" class="text-sm text-slate-300 hover:text-white transition">Comparacion</a>
            <a href="{{ route('config') }}" class="text-sm text-slate-300 hover:text-white transition">Configuración</a>
            <a href="{{ route('datosh') }}" class="text-sm text-slate-300 hover:text-white transition">Datos Historicos</a> 
            <a href="{{ route('simulacion') }}" class="text-sm text-slate-300 hover:text-white transition">Simulaciones</a>
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