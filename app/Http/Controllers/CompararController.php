<?php

namespace App\Http\Controllers;

use App\Models\LecturaSensor;
use Illuminate\View\View;

class CompararController extends Controller
{
    public function index(): View
    {
        $missionData = LecturaSensor::query()
            ->select([
                'id_sensor',
                'alt',
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
                    'altitude' => is_numeric($row->alt) ? (float) $row->alt : null,
                    'rpm' => is_numeric($row->rpm) ? (int) $row->rpm : null,
                    'fall_speed' => null,
                    'mission_time' => null,
                    'accel' => $accel,
                    'notes' => '',
                ];
            })
            ->filter(fn (array $sample): bool => !empty($sample['ts']))
            ->values();

        return view('Comparacion.comp', [
            'missionData' => $missionData,
        ]);
    }
}
