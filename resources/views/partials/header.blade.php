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