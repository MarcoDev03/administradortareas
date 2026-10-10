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
        Schema::table('config_estilos_estados_prioridad_periodicidad', function (Blueprint $table) {
            if (!Schema::hasColumn('config_estilos_estados_prioridad_periodicidad', 'ruta_icono')) {
                $table->string('ruta_icono')->nullable()->after('color');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('config_estilos_estados_prioridad_periodicidad', function (Blueprint $table) {
            if (Schema::hasColumn('config_estilos_estados_prioridad_periodicidad', 'ruta_icono')) {
                $table->dropColumn('ruta_icono');
            }
        });
    }
};
