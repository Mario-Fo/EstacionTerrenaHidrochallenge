<?php

namespace App\Http\Controllers;

use App\Support\MissionRequirements;
use Illuminate\Http\Request;

class ConfigController extends Controller
{
    public function edit()
    {
        return view('Config.config', [
            'requirements' => MissionRequirements::all(),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'altitude_threshold' => ['required', 'numeric', 'min:0'],
            'air_time_threshold' => ['required', 'numeric', 'min:0'],
            'fall_speed_threshold' => ['required', 'numeric', 'min:0'],
        ]);

        MissionRequirements::save($validated);

        return redirect()
            ->route('config')
            ->with('success', 'Cambios guardados correctamente.');
    }
}
