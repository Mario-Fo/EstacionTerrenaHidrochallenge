<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LecturaSensor;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Throwable;

class LecturaSensorController extends Controller
{
    public function store(Request $request)
    {
        $payload = $request->json()->all();
        if ($payload === [] && $request->all() !== []) {
            $payload = $request->all();
        }

        $lecturas = $this->normalizePayload($payload);

        if ($lecturas === []) {
            return response()->json([
                'ok' => false,
                'message' => 'El payload debe traer al menos una lectura valida.',
            ], 422);
        }

        $validator = Validator::make(['lecturas' => $lecturas], [
            'lecturas' => ['required', 'array', 'min:1'],
            'lecturas.*.id' => ['required', 'string', 'max:50'],
            'lecturas.*.pres' => ['required', 'numeric'],
            'lecturas.*.temp' => ['required', 'numeric'],
            'lecturas.*.hum' => ['required', 'numeric'],
            'lecturas.*.lat' => ['required', 'numeric'],
            'lecturas.*.long' => ['required', 'numeric'],
            'lecturas.*.alt' => ['required', 'numeric'],
            'lecturas.*.accX' => ['required', 'numeric'],
            'lecturas.*.accY' => ['required', 'numeric'],
            'lecturas.*.accZ' => ['required', 'numeric'],
            'lecturas.*.RPM' => ['nullable', 'integer'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'ok' => false,
                'message' => 'Payload invalido.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $today = Carbon::today()->toDateString();
        $now = now();
        $rows = [];

        foreach ($lecturas as $lectura) {
            $rows[] = [
                'id_sensor' => $lectura['id'],
                'pres' => $lectura['pres'],
                'temp' => $lectura['temp'],
                'hum' => $lectura['hum'],
                'lat' => $lectura['lat'],
                'lon' => $lectura['long'],
                'alt' => $lectura['alt'],
                'accx' => $lectura['accX'],
                'accy' => $lectura['accY'],
                'accz' => $lectura['accZ'],
                'rpm' => $lectura['RPM'],
                'fecha_dmy' => $today,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        try {
            LecturaSensor::insert($rows);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'ok' => false,
                'message' => 'No se pudieron guardar las lecturas.',
            ], 500);
        }

        return response()->json([
            'ok' => true,
            'message' => 'Lecturas guardadas correctamente.',
            'guardadas' => count($rows),
        ], 201);
    }

    public function ultima(Request $request)
    {
        $local = $this->getLatestLocalSample();

        // Si local viene fresca, no necesitamos ir al upstream.
        if ($local !== null && $this->isRecentSample($local['created_at'] ?? null, 2)) {
            return response()->json(['ok' => true, 'data' => $local]);
        }

        $upstream = $this->fetchUpstreamLatest($request);
        if ($upstream !== null) {
            if ($local === null) {
                return response()->json(['ok' => true, 'data' => $upstream]);
            }

            $newest = $this->pickNewestSample($local, $upstream);
            return response()->json(['ok' => true, 'data' => $newest]);
        }

        return response()->json(['ok' => true, 'data' => $local]);
    }

    private function getLatestLocalSample(): ?array
    {
        try {
            $r = LecturaSensor::latest('id_db')->first();
        } catch (Throwable $e) {
            report($e);
            return null;
        }

        if (!$r) {
            return null;
        }

        return $this->normalizeTelemetrySample([
            'id_db' => $r->id_db,
            'id' => $r->id_sensor,
            'pres' => $r->pres,
            'temp' => $r->temp,
            'hum' => $r->hum,
            'lat' => $r->lat,
            'long' => $r->lon,
            'alt' => $r->alt,
            'accX' => $r->accx,
            'accY' => $r->accy,
            'accZ' => $r->accz,
            'RPM' => $r->rpm,
            'fecha_dmy' => $r->fecha_dmy,
            'created_at' => $r->created_at?->toISOString(),
        ]);
    }

    private function fetchUpstreamLatest(Request $request): ?array
    {
        $upstreamUrl = trim((string) config('services.telemetry.upstream_latest_url', ''));

        if ($upstreamUrl === '' || $this->isSelfTelemetryEndpoint($request, $upstreamUrl)) {
            return null;
        }

        try {
            $response = Http::acceptJson()
                ->connectTimeout(0.2)
                ->timeout(0.35)
                ->get($upstreamUrl);
        } catch (Throwable $e) {
            return null;
        }

        if (!$response->successful()) {
            return null;
        }

        $json = $response->json();
        if (!is_array($json) || !($json['ok'] ?? false) || !is_array($json['data'] ?? null)) {
            return null;
        }

        return $this->normalizeTelemetrySample($json['data']);
    }

    private function isSelfTelemetryEndpoint(Request $request, string $upstreamUrl): bool
    {
        $upstream = parse_url($upstreamUrl);
        if (!is_array($upstream)) {
            return false;
        }

        $requestPort = (int) ($request->getPort() ?: 80);
        $upstreamPort = (int) ($upstream['port'] ?? (($upstream['scheme'] ?? 'http') === 'https' ? 443 : 80));
        $requestPath = '/' . ltrim($request->path(), '/');
        $upstreamPath = '/' . ltrim((string) ($upstream['path'] ?? ''), '/');

        return $requestPort === $upstreamPort && $requestPath === $upstreamPath;
    }

    private function pickNewestSample(array $a, array $b): array
    {
        $ta = $this->sampleTimestamp($a['created_at'] ?? null);
        $tb = $this->sampleTimestamp($b['created_at'] ?? null);

        if ($ta === null) {
            return $b;
        }

        if ($tb === null) {
            return $a;
        }

        return $tb->greaterThan($ta) ? $b : $a;
    }

    private function isRecentSample($createdAt, int $seconds): bool
    {
        $ts = $this->sampleTimestamp($createdAt);
        if ($ts === null) {
            return false;
        }

        return $ts->greaterThanOrEqualTo(now()->subSeconds($seconds));
    }

    private function sampleTimestamp($value): ?Carbon
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (Throwable $e) {
            return null;
        }
    }

    private function normalizePayload($payload): array
    {
        if (!is_array($payload) || $payload === []) {
            return [];
        }

        if (!array_is_list($payload)) {
            if (isset($payload['lecturas']) && is_array($payload['lecturas'])) {
                $payload = $payload['lecturas'];
            } else {
                $payload = [$payload];
            }
        }

        $normalized = [];

        foreach ($payload as $lectura) {
            if (!is_array($lectura)) {
                continue;
            }

            $sensorId = $lectura['id'] ?? $lectura['code'] ?? null;
            if (is_scalar($sensorId)) {
                $sensorId = (string) $sensorId;
            } else {
                $sensorId = null;
            }

            $normalized[] = [
                'id' => $sensorId,
                'pres' => $lectura['pres'] ?? null,
                'temp' => $lectura['temp'] ?? null,
                'hum' => $lectura['hum'] ?? null,
                'lat' => $lectura['lat'] ?? null,
                'long' => $lectura['long'] ?? $lectura['lon'] ?? null,
                'alt' => $lectura['alt'] ?? null,
                'accX' => $lectura['accX'] ?? $lectura['accx'] ?? null,
                'accY' => $lectura['accY'] ?? $lectura['accy'] ?? null,
                'accZ' => $lectura['accZ'] ?? $lectura['accz'] ?? null,
                'RPM' => $lectura['RPM'] ?? $lectura['rpm'] ?? 0,
            ];
        }

        return $normalized;
    }

    private function normalizeTelemetrySample(array $sample): array
    {
        return [
            'id_db' => isset($sample['id_db']) && is_numeric($sample['id_db']) ? (int) $sample['id_db'] : null,
            'id' => $sample['id'] ?? $sample['id_sensor'] ?? $sample['code'] ?? null,
            'pres' => $this->toFloat($sample['pres'] ?? null),
            'temp' => $this->toFloat($sample['temp'] ?? null),
            'hum' => $this->toFloat($sample['hum'] ?? null),
            'lat' => $this->toFloat($sample['lat'] ?? null),
            'long' => $this->toFloat($sample['long'] ?? $sample['lon'] ?? null),
            'alt' => $this->toFloat($sample['alt'] ?? null),
            'accX' => $this->toFloat($sample['accX'] ?? $sample['accx'] ?? null),
            'accY' => $this->toFloat($sample['accY'] ?? $sample['accy'] ?? null),
            'accZ' => $this->toFloat($sample['accZ'] ?? $sample['accz'] ?? null),
            'RPM' => isset($sample['RPM']) ? (int) $sample['RPM'] : (isset($sample['rpm']) ? (int) $sample['rpm'] : 0),
            'fecha_dmy' => $sample['fecha_dmy'] ?? null,
            'created_at' => $sample['created_at'] ?? null,
        ];
    }

    private function toFloat($value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }
}
