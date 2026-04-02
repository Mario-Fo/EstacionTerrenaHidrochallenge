@extends('layouts.app')
@section('title', 'Configuración')
@section('content')
@php
  $altitudeThreshold = old('altitude_threshold', $requirements['altitude_threshold'] ?? 100);
  $airTimeThreshold = old('air_time_threshold', $requirements['air_time_threshold'] ?? 45);
  $fallSpeedThreshold = old('fall_speed_threshold', $requirements['fall_speed_threshold'] ?? 8);
@endphp

<main id="main" class="flex-1">
  <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">
    <div class="mb-6">
      <h1 class="text-2xl font-semibold tracking-wide">Configuracion</h1>
      <p class="text-slate-400 text-sm">
        Ajusta los umbrales de altitud, tiempo en el aire y velocidad de caida para el dashboard principal.
      </p>
    </div>

    @if (session('success'))
      <div class="mb-4 rounded-xl border border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
        {{ session('success') }}
      </div>
    @endif

    @if ($errors->any())
      <div class="mb-4 rounded-xl border border-rose-500/40 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
        <p class="font-medium">No se pudieron guardar los cambios.</p>
        <ul class="mt-1 list-disc list-inside">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form id="configForm" method="POST" action="{{ route('config.update') }}">
      @csrf

      <section class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <aside class="lg:col-span-3">
          <div class="rounded-2xl border border-slate-800 bg-slate-950/40 backdrop-blur p-3">
            <p class="px-3 pt-2 pb-3 text-xs uppercase tracking-wider text-slate-500">Secciones</p>
            <nav class="space-y-1">
              <button data-tab="sensores" type="button" class="tabBtn w-full text-left px-3 py-2 rounded-xl border border-slate-800 bg-slate-900/50 text-slate-100 hover:bg-slate-900 transition">
                Requisitos
                <span class="block text-xs text-slate-400 mt-0.5">Umbrales del dashboard</span>
              </button>
            </nav>

            <div class="mt-3 p-3 rounded-xl border border-slate-800 bg-slate-900/15">
              <p class="text-xs text-slate-400">Estado</p>
              <p id="statusText" class="mt-1 text-sm text-slate-200">Todo guardado</p>
            </div>
          </div>
        </aside>

        <div class="lg:col-span-9 space-y-6">
          <div class="rounded-2xl border border-slate-800 bg-slate-950/40 backdrop-blur p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex gap-2">
              <button
                id="btnDiscard"
                type="button"
                class="rounded-xl border border-slate-800 bg-slate-900/20 px-4 py-2 text-sm text-slate-200 hover:bg-slate-900/40 transition"
              >
                Descartar
              </button>
              <button
                id="btnSave"
                type="submit"
                class="rounded-xl border border-slate-800 bg-slate-900/60 px-4 py-2 text-sm text-slate-100 hover:bg-slate-900 transition"
              >
                Guardar cambios
              </button>
            </div>
          </div>



          <section id="tab-sensores" class="tabPanel rounded-2xl border border-slate-800 bg-slate-950/40 backdrop-blur overflow-hidden">
            <div class="p-4 border-b border-slate-800">
              <h2 class="text-lg font-semibold">Requisitos</h2>
              <p class="text-sm text-slate-400">Cuando un valor en dashboard supera su umbral, cambia a verde.</p>
            </div>

            <div class="p-4 grid grid-cols-1 md:grid-cols-12 gap-4">
              <div class="md:col-span-4 rounded-2xl border border-slate-800 bg-slate-900/20 p-4">
                <h3 class="font-semibold">Altitud</h3>
                <p class="text-xs text-slate-400 mt-1">El dashboard marca verde cuando la altitud supera este valor.</p>

                <div class="mt-3">
                  <label for="altitude_threshold" class="text-xs text-slate-400">Seteado en (m):</label>
                  <input
                    id="altitude_threshold"
                    name="altitude_threshold"
                    type="number"
                    step="0.1"
                    min="0"
                    value="{{ $altitudeThreshold }}"
                    class="mt-1 w-full rounded-xl border border-slate-800 bg-slate-900/40 px-3 py-2 text-sm text-slate-100"
                  />
                </div>
              </div>

              <div class="md:col-span-4 rounded-2xl border border-slate-800 bg-slate-900/20 p-4">
                <h3 class="font-semibold">Tiempo en el aire</h3>
                <p class="text-xs text-slate-400 mt-1">El dashboard marca verde cuando el tiempo supera este valor.</p>

                <div class="mt-3">
                  <label for="air_time_threshold" class="text-xs text-slate-400">Seteado en (s):</label>
                  <input
                    id="air_time_threshold"
                    name="air_time_threshold"
                    type="number"
                    step="0.1"
                    min="0"
                    value="{{ $airTimeThreshold }}"
                    class="mt-1 w-full rounded-xl border border-slate-800 bg-slate-900/40 px-3 py-2 text-sm text-slate-100"
                  />
                </div>
              </div>

              <div class="md:col-span-4 rounded-2xl border border-slate-800 bg-slate-900/20 p-4">
                <h3 class="font-semibold">Velocidad de caida</h3>
                <p class="text-xs text-slate-400 mt-1">El dashboard marca verde cuando la velocidad esta por debajo o igual a este valor.</p>

                <div class="mt-3">
                  <label for="fall_speed_threshold" class="text-xs text-slate-400">Seteado como maximo (m/s):</label>
                  <input
                    id="fall_speed_threshold"
                    name="fall_speed_threshold"
                    type="number"
                    step="0.1"
                    min="0"
                    value="{{ $fallSpeedThreshold }}"
                    class="mt-1 w-full rounded-xl border border-slate-800 bg-slate-900/40 px-3 py-2 text-sm text-slate-100"
                  />
                </div>
              </div>
            </div>
          </section>
        </div>
      </section>
    </form>
  </div>
</main>

<script>
  (function () {
    const $ = (id) => document.getElementById(id);
    const form = $("configForm");
    const statusText = $("statusText");
    const tabButtons = Array.from(document.querySelectorAll(".tabBtn"));
    const panels = {
      sensores: $("tab-sensores"),
    };

    function setStatus(dirty) {
      statusText.textContent = dirty ? "Cambios sin guardar" : "Todo guardado";
      statusText.className = dirty
        ? "mt-1 text-sm text-amber-200"
        : "mt-1 text-sm text-emerald-200";
    }

    function setActiveTab(name) {
      tabButtons.forEach((btn) => {
        const isActive = btn.dataset.tab === name;
        btn.className =
          "tabBtn w-full text-left px-3 py-2 rounded-xl border border-slate-800 transition " +
          (isActive
            ? "bg-slate-900/50 text-slate-100 hover:bg-slate-900"
            : "bg-slate-900/20 text-slate-200 hover:bg-slate-900/40");
      });

      Object.entries(panels).forEach(([key, panel]) => {
        if (!panel) return;
        panel.classList.toggle("hidden", key !== name);
      });
    }

    function snapshotFormData() {
      const data = {};
      new FormData(form).forEach((value, key) => {
        data[key] = String(value);
      });
      return data;
    }

    function isDirty(initialData) {
      const currentData = snapshotFormData();
      return Object.keys(initialData).some((key) => currentData[key] !== initialData[key]);
    }

    const initialData = snapshotFormData();
    setActiveTab("sensores");
    setStatus(false);

    tabButtons.forEach((btn) => {
      btn.addEventListener("click", () => setActiveTab(btn.dataset.tab));
    });

    form.querySelectorAll("input, select, textarea").forEach((field) => {
      field.addEventListener("input", () => setStatus(isDirty(initialData)));
      field.addEventListener("change", () => setStatus(isDirty(initialData)));
    });

    $("btnDiscard").addEventListener("click", () => {
      form.reset();
      setStatus(false);
    });
  })();
</script>
@endsection
