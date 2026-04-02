<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SimulacionController extends Controller
{
    public function run(Request $request)
    {
        $validated = $request->validate([
            'P_inicial_psi' => ['required', 'numeric', 'between:60,150'],
            'Angulo_Lanzamiento' => ['required', 'numeric', 'between:60,90'],
            'Viento_X' => ['required', 'numeric', 'between:0,20'],
            'rho_aire' => ['required', 'numeric', 'between:0.8,1.5'],
            'Pct_Agua_E1' => ['required', 'numeric', 'between:10,60'],
            'Pct_Agua_E2' => ['required', 'numeric', 'between:10,60'],
            'usar_jabon' => ['required', 'boolean'],
            'M_CargaUtil' => ['required', 'numeric', 'between:0.01,0.2'],
            'M_Seca_E1' => ['required', 'numeric', 'between:0.05,0.3'],
            'M_Seca_E2' => ['required', 'numeric', 'between:0.05,0.4'],
            'Cd_E1' => ['required', 'numeric', 'between:0.2,1.0'],
            'Cd_E2' => ['required', 'numeric', 'between:0.1,0.8'],
            'V_descenso_meta' => ['required', 'numeric', 'between:2,15'],
        ]);

        $apiUrl = config('services.python_sim.url', 'http://127.0.0.1:8000/simular');

        try {
            $response = Http::timeout(30)->acceptJson()->post($apiUrl, $validated);
        } catch (ConnectionException $e) {
            return response()->json([
                'ok' => false,
                'message' => 'No se pudo conectar con la API Python.',
                'detail' => $e->getMessage(),
                'error' => $e->getMessage(),
            ], 503);
        }

        if (! $response->ok()) {
            $pythonBody = $response->json() ?? $response->body();
            $detail = data_get($pythonBody, 'detail');

            if (! $detail && is_string($pythonBody)) {
                $isHtmlBody = str_contains(strtolower($pythonBody), '<!doctype html');
                $detail = $isHtmlBody
                    ? 'El upstream devolvio HTML; revisa PY_SIM_API_URL (host/puerto).'
                    : mb_substr(trim(strip_tags($pythonBody)), 0, 220);
            }

            return response()->json([
                'ok' => false,
                'message' => 'La API Python respondio con error.',
                'detail' => $detail,
                'python_status' => $response->status(),
                'python_body' => $pythonBody,
            ], 502);
        }

        return response()->json($response->json());
    }
}
