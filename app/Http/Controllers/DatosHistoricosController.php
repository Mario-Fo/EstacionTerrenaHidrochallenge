<?php

namespace App\Http\Controllers;

use App\Models\LecturaSensor;
use Illuminate\View\View;

class DatosHistoricosController extends Controller
{
    public function index(): View
    {
        $historicalData = LecturaSensor::query()
            ->select([
                'id_sensor',
                'pres',
                'lat',
                'lon',
                'rpm',
                'accx',
                'accy',
                'accz',
                'created_at',
            ])
            ->whereNotNull('id_sensor')
            ->orderBy('created_at')
            ->get()
            ->map(function (LecturaSensor $row): array {
                $ax = is_numeric($row->accx) ? (float) $row->accx : null;
                $ay = is_numeric($row->accy) ? (float) $row->accy : null;
                $az = is_numeric($row->accz) ? (float) $row->accz : null;

                $accel = null;
                if ($ax !== null && $ay !== null && $az !== null) {
                    $accel = sqrt(($ax * $ax) + ($ay * $ay) + ($az * $az));
                }

                return [
                    'ts' => optional($row->created_at)?->toISOString(),
                    'mission' => (string) $row->id_sensor,
                    'pres' => is_numeric($row->pres) ? (float) $row->pres : null,
                    'lat' => is_numeric($row->lat) ? (float) $row->lat : null,
                    'lon' => is_numeric($row->lon) ? (float) $row->lon : null,
                    'rpm' => is_numeric($row->rpm) ? (int) $row->rpm : null,
                    'accel' => $accel,
                ];
            })
            ->filter(fn (array $sample): bool => !empty($sample['ts']))
            ->values();

        return view('Datos.datosh', [
            'historicalData' => $historicalData,
        ]);
    }
}
