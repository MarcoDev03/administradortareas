<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigEstilosEstadosPrioridadPeriodicidad extends Model
{
    use HasFactory;

    protected $table = 'config_estilos_estados_prioridad_periodicidad';

    protected $fillable = [
        'nombre',
        'color',
        'ruta_icono',
        'clave',
        'tipo',
    ];
}
