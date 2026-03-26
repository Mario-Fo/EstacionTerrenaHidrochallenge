<?php

namespace App\Http\Controllers;

use App\Support\MissionRequirements;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard.dash', [
            'requirements' => MissionRequirements::all(),
        ]);
    }
}
