<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $prioridades = [
            [
                'nombre' => 'Baja',
                'color' => '#64748b',
                'clave' => 'baja',
                'tipo' => 'prioridad',
                'ruta_icono' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Media',
                'color' => '#f59e0b',
                'clave' => 'media',
                'tipo' => 'prioridad',
                'ruta_icono' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Alta',
                'color' => '#ef4444',
                'clave' => 'alta',
                'tipo' => 'prioridad',
                'ruta_icono' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        $periodicidades = [
            [
                'nombre' => 'Diaria',
                'color' => '#06b6d4',
                'clave' => 'diaria',
                'tipo' => 'periodicidad',
                'ruta_icono' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Semanal',
                'color' => '#3b82f6',
                'clave' => 'semanal',
                'tipo' => 'periodicidad',
                'ruta_icono' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Mensual',
                'color' => '#8b5cf6',
                'clave' => 'mensual',
                'tipo' => 'periodicidad',
                'ruta_icono' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Anual',
                'color' => '#10b981',
                'clave' => 'anual',
                'tipo' => 'periodicidad',
                'ruta_icono' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach (array_merge($prioridades, $periodicidades) as $item) {
            $exists = DB::table('config_estilos_estados_prioridad_periodicidad')
                ->where('tipo', $item['tipo'])
                ->where('clave', $item['clave'])
                ->exists();

            if (!$exists) {
                DB::table('config_estilos_estados_prioridad_periodicidad')->insert($item);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('config_estilos_estados_prioridad_periodicidad')
            ->whereIn('tipo', ['prioridad', 'periodicidad'])
            ->delete();
    }
};
