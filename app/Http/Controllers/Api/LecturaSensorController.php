<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LecturaSensor;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class LecturaSensorController extends Controller
{
    public function store(Request $request)
    {
        $payload = $request->json()->all();

        if (!is_array($payload) || $payload === []) {
            return response()->json([
                'ok' => false,
                'message' => 'El payload debe ser un arreglo JSON con al menos una lectura.',
            ], 422);
        }

        $validator = Validator::make(['lecturas' => $payload], [
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
            'lecturas.*.RPM' => ['required', 'integer'],
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

        foreach ($payload as $lectura) {
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

        LecturaSensor::insert($rows);

        return response()->json([
            'ok' => true,
            'message' => 'Lecturas guardadas correctamente.',
            'guardadas' => count($rows),
        ], 201);
    }

    public function ultima()
{
    $r = \App\Models\LecturaSensor::latest('id_db')->first();

    if (!$r) {
        return response()->json(['ok' => true, 'data' => null]);
    }

    return response()->json([
        'ok' => true,
        'data' => [
            'id'   => $r->id_sensor,
            'pres' => (float) $r->pres,
            'temp' => (float) $r->temp,
            'hum'  => (float) $r->hum,
            'lat'  => (float) $r->lat,
            'long' => (float) $r->lon,
            'alt'  => (float) $r->alt,
            'accX' => (float) $r->accx,
            'accY' => (float) $r->accy,
            'accZ' => (float) $r->accz,
            'RPM'  => (int) $r->rpm,
            'fecha_dmy' => $r->fecha_dmy,
        ],
    ]);
}

}
