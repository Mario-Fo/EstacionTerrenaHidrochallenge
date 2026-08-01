<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lecturas_sensores', function (Blueprint $table) {
            $table->id('id_db');
            $table->string('id_sensor', 50)->index();
            $table->double('pres');
            $table->double('temp');
            $table->double('hum');
            $table->double('lat');
            $table->double('lon');
            $table->double('alt');
            $table->double('accx');
            $table->double('accy');
            $table->double('accz');
            $table->integer('rpm')->nullable()->default(0);
            $table->date('fecha_dmy');
            $table->timestamps();

            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lecturas_sensores');
    }
};
