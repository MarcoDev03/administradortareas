<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('config_estilos_estados_prioridad_periodicidad', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('color');
            $table->string('clave')->nullable();
            $table->string('tipo')->nullable()->default('estado');
            $table->timestamps();
        });

        // Insertar los 5 estados iniciales mencionados
        DB::table('config_estilos_estados_prioridad_periodicidad')->insert([
            [
                'nombre' => 'No iniciado',
                'color' => '#64748b',
                'clave' => 'no_iniciado',
                'tipo' => 'estado',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'En proceso',
                'color' => '#3b82f6',
                'clave' => 'en_proceso',
                'tipo' => 'estado',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'En revisión',
                'color' => '#f59e0b',
                'clave' => 'en_revision',
                'tipo' => 'estado',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Rechazado',
                'color' => '#ef4444',
                'clave' => 'rechazado',
                'tipo' => 'estado',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Finalizado',
                'color' => '#10b981',
                'clave' => 'finalizado',
                'tipo' => 'estado',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('config_estilos_estados_prioridad_periodicidad');
    }
};
