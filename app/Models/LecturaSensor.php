<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LecturaSensor extends Model
{
    protected $table = 'lecturas_sensores';

    protected $fillable = [
        'id_sensor',
        'pres',
        'temp',
        'hum',
        'lat',
        'lon',
        'alt',
        'accx',
        'accy',
        'accz',
        'rpm',
        'fecha_dmy',
    ];
}
