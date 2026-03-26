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

    @include('partials.header')

    <main id="main" class="pt-16">
        @yield('config')
    </main>

    @include('partials.footer')

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</body>
</html>
