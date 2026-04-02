@extends ('layouts.app')
@section('title', 'Simulación Bravo II')
@section('content')

    <div class="flex min-h-[calc(100vh-140px)] bg-slate-950 text-slate-100 rounded-2xl overflow-hidden">
        <!-- SIDEBAR -->
        <aside class="w-full max-w-sm bg-slate-900/95 border-r border-slate-800 p-6 overflow-y-auto">
            <div class="mb-6">
                <h2 class="text-2xl font-bold tracking-tight">🎛️ Centro de Mando</h2>
                <p class="text-slate-400 text-sm mt-1">Configuración de simulación</p>
            </div>

            <form id="simForm" class="space-y-6">
                @csrf

                <!-- 1. Entorno y Despegue -->
                <section class="bg-slate-950/60 border border-slate-800 rounded-2xl p-4">
                    <h3 class="text-lg font-semibold mb-4">1. Entorno y Despegue</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm text-slate-300 mb-1">Presión Inicial (psi)</label>
                            <input type="range" name="P_inicial_psi" min="60" max="150" step="1" value="90" class="w-full">
                            <div class="text-right text-xs text-slate-400"><span id="P_inicial_psi_val">90</span> psi</div>
                        </div>

                        <div>
                            <label class="block text-sm text-slate-300 mb-1">Ángulo de Lanzamiento (°)</label>
                            <input type="range" name="Angulo_Lanzamiento" min="60" max="90" step="1" value="90" class="w-full">
                            <div class="text-right text-xs text-slate-400"><span id="Angulo_Lanzamiento_val">90</span> °</div>
                        </div>

                        <div>
                            <label class="block text-sm text-slate-300 mb-1">Viento Lateral (m/s)</label>
                            <input type="range" name="Viento_X" min="0" max="20" step="0.5" value="5" class="w-full">
                            <div class="text-right text-xs text-slate-400"><span id="Viento_X_val">5</span> m/s</div>
                        </div>

                        <div>
                            <label class="block text-sm text-slate-300 mb-1">Densidad del Aire (kg/m³)</label>
                            <input type="number" name="rho_aire" min="0.8" max="1.5" step="0.001" value="1.225"
                                   class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 outline-none focus:border-blue-500">
                        </div>
                    </div>
                </section>

                <!-- 2. Propulsión -->
                <section class="bg-slate-950/60 border border-slate-800 rounded-2xl p-4">
                    <h3 class="text-lg font-semibold mb-4">2. Propulsión (Agua)</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm text-slate-300 mb-1">% Agua Etapa 1</label>
                            <input type="range" name="Pct_Agua_E1" min="10" max="60" step="1" value="40" class="w-full">
                            <div class="text-right text-xs text-slate-400"><span id="Pct_Agua_E1_val">40</span> %</div>
                        </div>

                        <div>
                            <label class="block text-sm text-slate-300 mb-1">% Agua Etapa 2</label>
                            <input type="range" name="Pct_Agua_E2" min="10" max="60" step="1" value="30" class="w-full">
                            <div class="text-right text-xs text-slate-400"><span id="Pct_Agua_E2_val">30</span> %</div>
                        </div>

                        <label class="flex items-center justify-between bg-slate-900 border border-slate-700 rounded-xl px-3 py-3">
                            <span class="text-sm">🧼 Activar Aditivo de Espuma (Jabón)</span>
                            <input type="checkbox" name="usar_jabon" checked class="h-5 w-5">
                        </label>
                    </div>
                </section>

                <!-- 3. Masas -->
                <section class="bg-slate-950/60 border border-slate-800 rounded-2xl p-4">
                    <h3 class="text-lg font-semibold mb-4">3. Masas Estructurales (kg)</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm text-slate-300 mb-1">Carga Útil (Payload)</label>
                            <input type="number" name="M_CargaUtil" min="0.01" max="0.2" step="0.0001" value="0.075"
                                   class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 outline-none focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm text-slate-300 mb-1">Masa Seca E1 (Booster)</label>
                            <input type="number" name="M_Seca_E1" min="0.05" max="0.3" step="0.0001" value="0.105"
                                   class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 outline-none focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm text-slate-300 mb-1">Masa Seca E2 (Sustainer)</label>
                            <input type="number" name="M_Seca_E2" min="0.05" max="0.4" step="0.0001" value="0.1873"
                                   class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 outline-none focus:border-blue-500">
                        </div>
                    </div>
                </section>

                <!-- 4. Aerodinámica -->
                <section class="bg-slate-950/60 border border-slate-800 rounded-2xl p-4">
                    <h3 class="text-lg font-semibold mb-4">4. Aerodinámica y Recuperación</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm text-slate-300 mb-1">Arrastre (Cd) Etapas Unidas</label>
                            <input type="range" name="Cd_E1" min="0.2" max="1.0" step="0.05" value="0.6" class="w-full">
                            <div class="text-right text-xs text-slate-400"><span id="Cd_E1_val">0.6</span></div>
                        </div>

                        <div>
                            <label class="block text-sm text-slate-300 mb-1">Arrastre (Cd) Etapa 2</label>
                            <input type="range" name="Cd_E2" min="0.1" max="0.8" step="0.05" value="0.4" class="w-full">
                            <div class="text-right text-xs text-slate-400"><span id="Cd_E2_val">0.4</span></div>
                        </div>

                        <div>
                            <label class="block text-sm text-slate-300 mb-1">Vel. de Caída Autogiro (m/s)</label>
                            <input type="number" name="V_descenso_meta" min="2" max="15" step="0.5" value="8"
                                   class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 outline-none focus:border-blue-500">
                        </div>
                    </div>
                </section>

                <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-500 transition rounded-2xl py-3 font-semibold text-white shadow-lg shadow-blue-900/30">
                    Ejecutar simulación
                </button>
            </form>
        </aside>

        <!-- MAIN -->
        <main class="flex-1 p-6 md:p-8 overflow-y-auto">
            <div class="mb-8">
                <h1 class="text-4xl md:text-5xl font-bold tracking-tight">🚀 Proyecto Bravo II</h1>
                <p class="text-slate-400 text-lg mt-2">Centro de Simulación Termodinámica y Control de Vuelo</p>
                <div class="h-px bg-slate-800 mt-6"></div>
            </div>

            <!-- Métricas -->
            <section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 border-l-4 border-l-blue-500">
                    <p class="text-sm text-slate-400">Apogeo Alcanzado</p>
                    <p id="metric_apogeo" class="text-3xl font-bold mt-2">--</p>
                    <p id="metric_apogeo_status" class="text-sm mt-2 text-slate-400">Esperando simulación</p>
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 border-l-4 border-l-blue-500">
                    <p class="text-sm text-slate-400">Deriva Estimada (X)</p>
                    <p id="metric_deriva" class="text-3xl font-bold mt-2">--</p>
                    <p class="text-sm mt-2 text-slate-400">Desplazamiento horizontal</p>
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 border-l-4 border-l-blue-500">
                    <p class="text-sm text-slate-400">Velocidad Máxima</p>
                    <p id="metric_velocidad" class="text-3xl font-bold mt-2">--</p>
                    <p id="metric_mach" class="text-sm mt-2 text-slate-400">Mach --</p>
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 border-l-4 border-l-blue-500">
                    <p class="text-sm text-slate-400">GLOW (Gross Lift-Off Weight)</p>
                    <p id="metric_masa" class="text-3xl font-bold mt-2">--</p>
                    <p class="text-sm mt-2 text-slate-400">Masa total de despegue</p>
                </div>
            </section>

            <!-- Tabs -->
            <section class="bg-slate-900 border border-slate-800 rounded-3xl p-4 md:p-6">
                <div class="flex gap-3 border-b border-slate-800 mb-6">
                    <button class="tab-btn px-4 py-3 rounded-t-xl text-sm md:text-base bg-slate-800 text-white"
                            data-tab="telemetria">
                        📊 Análisis de Telemetría
                    </button>
                    <button class="tab-btn px-4 py-3 rounded-t-xl text-sm md:text-base text-slate-300 hover:bg-slate-800/70"
                            data-tab="trayectoria">
                        🗺️ Mapa de Trayectoria 2D
                    </button>
                </div>

                <div id="tab-telemetria" class="tab-panel">
                    <div id="graficaTelemetria" class="w-full h-[760px]"></div>
                </div>

                <div id="tab-trayectoria" class="tab-panel hidden">
                    <div id="graficaTrayectoria" class="w-full h-[620px]"></div>
                </div>
            </section>

            <!-- Estado -->
            <div id="estadoBox" class="mt-6 hidden bg-slate-900 border border-slate-800 rounded-2xl p-4 text-slate-300"></div>
        </main>
    </div>
</div>
    <script src="https://cdn.plot.ly/plotly-2.35.2.min.js"></script>
    <script>
        const form = document.getElementById('simForm');
        const estadoBox = document.getElementById('estadoBox');

        // Mostrar valor de sliders
        document.querySelectorAll('input[type="range"]').forEach(input => {
            const span = document.getElementById(input.name + '_val');
            if (span) {
                input.addEventListener('input', () => span.textContent = input.value);
            }
        });

        // Tabs
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.tab-btn').forEach(b => {
                    b.classList.remove('bg-slate-800', 'text-white');
                    b.classList.add('text-slate-300');
                });

                document.querySelectorAll('.tab-panel').forEach(panel => {
                    panel.classList.add('hidden');
                });

                btn.classList.add('bg-slate-800', 'text-white');
                btn.classList.remove('text-slate-300');

                document.getElementById('tab-' + btn.dataset.tab).classList.remove('hidden');
            });
        });

        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            estadoBox.classList.remove('hidden');
            estadoBox.innerHTML = 'Ejecutando simulación...';

            const formData = new FormData(form);

            const payload = {
                P_inicial_psi: parseFloat(formData.get('P_inicial_psi')),
                Angulo_Lanzamiento: parseFloat(formData.get('Angulo_Lanzamiento')),
                Viento_X: parseFloat(formData.get('Viento_X')),
                rho_aire: parseFloat(formData.get('rho_aire')),
                Pct_Agua_E1: parseFloat(formData.get('Pct_Agua_E1')),
                Pct_Agua_E2: parseFloat(formData.get('Pct_Agua_E2')),
                usar_jabon: formData.get('usar_jabon') ? true : false,
                M_CargaUtil: parseFloat(formData.get('M_CargaUtil')),
                M_Seca_E1: parseFloat(formData.get('M_Seca_E1')),
                M_Seca_E2: parseFloat(formData.get('M_Seca_E2')),
                Cd_E1: parseFloat(formData.get('Cd_E1')),
                Cd_E2: parseFloat(formData.get('Cd_E2')),
                V_descenso_meta: parseFloat(formData.get('V_descenso_meta'))
            };
            const csrfToken = formData.get('_token');

            try {
                const response = await fetch('{{ route('simulacion.run') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json().catch(() => ({}));

                if (!response.ok || data.ok === false) {
                    const validationErrors = data.errors
                        ? Object.values(data.errors).flat().join(' ')
                        : null;
                    const pythonDetail = data.python_body?.detail || data.python_body?.message || null;
                    const message = data.detail
                        || data.message
                        || validationErrors
                        || pythonDetail
                        || `Error HTTP ${response.status}`;
                    throw new Error(message);
                }

                const r = data.resultado;
                actualizarMetricas(r);
                renderGraficas(r);

                estadoBox.innerHTML = 'Simulación completada correctamente.';
            } catch (error) {
                estadoBox.innerHTML = 'Error: ' + error.message;
                console.error(error);
            }
        });

        function actualizarMetricas(r) {
            const apogeo = r.metricas.apogeo;
            const deriva = r.metricas.deriva;
            const velMax = r.metricas.velocidad_max;
            const masa = r.metricas.masa_despegue;
            const mach = velMax / 343.0;

            document.getElementById('metric_apogeo').textContent = apogeo.toFixed(4) + ' m';
            document.getElementById('metric_deriva').textContent = deriva.toFixed(4) + ' m';
            document.getElementById('metric_velocidad').textContent = velMax.toFixed(4) + ' m/s';
            document.getElementById('metric_masa').textContent = masa.toFixed(4) + ' kg';
            document.getElementById('metric_mach').textContent = 'Mach ' + mach.toFixed(4);

            const status = document.getElementById('metric_apogeo_status');
            if (apogeo >= 100) {
                status.textContent = 'Óptimo';
                status.className = 'text-sm mt-2 text-emerald-400';
            } else {
                status.textContent = 'Subóptimo';
                status.className = 'text-sm mt-2 text-rose-400';
            }
        }

        function renderGraficas(r) {
            const t = r.series.t;
            const y = r.series.y;
            const velocidad = r.series.velocidad;
            const aceleracion = r.series.aceleracion;
            const x = r.series.x;
            const maxSerie = serie => serie.reduce((max, value) => Math.max(max, Number.isFinite(value) ? value : 0), 0);

            const t_sep1 = r.eventos.t_sep1;
            const t_sep2 = r.eventos.t_sep2;
            const t_apog = r.eventos.t_apog;
            const idx1 = r.eventos.idx_sep1;
            const idx2 = r.eventos.idx_sep2;
            const idxApog = r.eventos.idx_apog;
            const yMaxAlt = Math.max(maxSerie(y), 1);
            const yMaxVel = Math.max(maxSerie(velocidad), 1);
            const yMaxAcc = Math.max(maxSerie(aceleracion), 1);

            Plotly.newPlot('graficaTelemetria', [
                {
                    x: t, y: y, type: 'scatter', mode: 'lines',
                    name: 'Altitud',
                    line: {color: '#1f77b4', width: 3},
                    fill: 'tozeroy',
                    fillcolor: 'rgba(31,119,180,0.10)',
                    xaxis: 'x',
                    yaxis: 'y'
                },
                {
                    x: t, y: velocidad, type: 'scatter', mode: 'lines',
                    name: 'Velocidad',
                    line: {color: '#d62728', width: 2.5},
                    xaxis: 'x2',
                    yaxis: 'y2'
                },
                {
                    x: t, y: aceleracion, type: 'scatter', mode: 'lines',
                    name: 'Aceleración',
                    line: {color: '#ff7f0e', width: 2.5},
                    xaxis: 'x3',
                    yaxis: 'y3'
                }
            ], {
                paper_bgcolor: '#0f172a',
                plot_bgcolor: '#0f172a',
                font: {color: '#e2e8f0'},
                showlegend: false,
                height: 750,
                margin: {l: 50, r: 20, t: 40, b: 40},

                grid: {rows: 3, columns: 1, pattern: 'independent'},

                xaxis: {title: 'Tiempo (s)', gridcolor: '#334155'},
                yaxis: {title: 'Altitud (m)', gridcolor: '#334155'},

                xaxis2: {title: 'Tiempo (s)', gridcolor: '#334155'},
                yaxis2: {title: 'Velocidad (m/s)', gridcolor: '#334155'},

                xaxis3: {title: 'Tiempo (s)', gridcolor: '#334155'},
                yaxis3: {title: 'Aceleración (m/s²)', gridcolor: '#334155'},

                shapes: [
                    verticalLineShapeForAxis(t_sep1, 'x', 'y', yMaxAlt),
                    verticalLineShapeForAxis(t_sep2, 'x', 'y', yMaxAlt),
                    verticalLineShapeForAxis(t_apog, 'x', 'y', yMaxAlt),
                    verticalLineShapeForAxis(t_sep1, 'x2', 'y2', yMaxVel),
                    verticalLineShapeForAxis(t_sep2, 'x2', 'y2', yMaxVel),
                    verticalLineShapeForAxis(t_apog, 'x2', 'y2', yMaxVel),
                    verticalLineShapeForAxis(t_sep1, 'x3', 'y3', yMaxAcc),
                    verticalLineShapeForAxis(t_sep2, 'x3', 'y3', yMaxAcc),
                    verticalLineShapeForAxis(t_apog, 'x3', 'y3', yMaxAcc)
                ],
                annotations: [
                    annotationLineForAxis(t_sep1, 'MECO 1', 'x', 'y', yMaxAlt),
                    annotationLineForAxis(t_sep2, 'MECO 2', 'x', 'y', yMaxAlt),
                    annotationLineForAxis(t_apog, 'APOGEO', 'x', 'y', yMaxAlt),
                    annotationLineForAxis(t_sep1, 'MECO 1', 'x2', 'y2', yMaxVel),
                    annotationLineForAxis(t_sep2, 'MECO 2', 'x2', 'y2', yMaxVel),
                    annotationLineForAxis(t_apog, 'APOGEO', 'x2', 'y2', yMaxVel),
                    annotationLineForAxis(t_sep1, 'MECO 1', 'x3', 'y3', yMaxAcc),
                    annotationLineForAxis(t_sep2, 'MECO 2', 'x3', 'y3', yMaxAcc),
                    annotationLineForAxis(t_apog, 'APOGEO', 'x3', 'y3', yMaxAcc)
                ]
            }, {responsive: true});

            Plotly.newPlot('graficaTrayectoria', [
                {
                    x: x,
                    y: y,
                    type: 'scatter',
                    mode: 'lines',
                    name: 'Perfil de Vuelo',
                    line: {color: '#22c55e', width: 4}
                },
                {
                    x: [x[idx1]],
                    y: [y[idx1]],
                    type: 'scatter',
                    mode: 'markers',
                    name: 'Separación E1',
                    marker: {color: 'white', line: {color: 'red', width: 2}, size: 12}
                },
                {
                    x: [x[idx2]],
                    y: [y[idx2]],
                    type: 'scatter',
                    mode: 'markers',
                    name: 'Fin Empuje E2',
                    marker: {color: 'white', line: {color: 'green', width: 2}, size: 12}
                },
                {
                    x: [x[idxApog]],
                    y: [y[idxApog]],
                    type: 'scatter',
                    mode: 'markers',
                    name: 'Apogeo',
                    marker: {color: '#d62728', symbol: 'star', size: 16}
                },
                {
                    x: [x[x.length - 1]],
                    y: [0],
                    type: 'scatter',
                    mode: 'markers',
                    name: 'Punto de Impacto',
                    marker: {color: 'white', line: {color: 'blue', width: 2}, symbol: 'x', size: 12}
                }
            ], {
                paper_bgcolor: '#0f172a',
                plot_bgcolor: '#0f172a',
                font: {color: '#e2e8f0'},
                height: 600,
                margin: {l: 50, r: 20, t: 40, b: 50},
                xaxis: {
                    title: 'Deriva Horizontal (m)',
                    gridcolor: '#334155'
                },
                yaxis: {
                    title: 'Altitud (m)',
                    gridcolor: '#334155',
                    scaleanchor: 'x',
                    scaleratio: 1
                },
                legend: {
                    bgcolor: 'rgba(15,23,42,0.7)',
                    bordercolor: '#334155',
                    borderwidth: 1
                }
            }, {responsive: true});
        }

        function verticalLineShapeForAxis(x, axisX, axisY, yTop) {
            return {
                type: 'line',
                xref: axisX,
                yref: axisY,
                x0: x,
                x1: x,
                y0: 0,
                y1: yTop,
                line: {color: '#94a3b8', width: 1, dash: 'dash'}
            };
        }

        function annotationLineForAxis(x, text, axisX, axisY, yTop) {
            return {
                x: x,
                y: yTop,
                xref: axisX,
                yref: axisY,
                text: text,
                showarrow: true,
                arrowhead: 2,
                ax: 0,
                ay: -22,
                font: {color: '#cbd5e1'}
            };
        }
    </script>
@endsection

