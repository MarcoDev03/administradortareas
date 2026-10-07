<?php

namespace App\Http\Controllers;

use App\Models\ConfigEstilosEstadosPrioridadPeriodicidad;
use Illuminate\Http\Request;

class ConfigController extends Controller
{
    /**
     * Muestra la vista de configuración cargando los estados desde la base de datos.
     */
    public function index()
    {
        $estados = ConfigEstilosEstadosPrioridadPeriodicidad::orderBy('id')->get();

        return view('config.index', compact('estados'));
    }

    /**
     * Actualiza y guarda los estados en la base de datos.
     */
    public function update(Request $request)
    {
        $request->validate([
            'estados' => ['required', 'array'],
            'estados.*.id' => ['required', 'exists:config_estilos_estados_prioridad_periodicidad,id'],
            'estados.*.nombre' => ['required', 'string', 'max:100'],
            'estados.*.color' => ['required', 'string', 'max:25'],
        ]);

        foreach ($request->input('estados') as $estadoData) {
            $estado = ConfigEstilosEstadosPrioridadPeriodicidad::find($estadoData['id']);
            if ($estado) {
                $color = trim($estadoData['color']);
                if (!str_starts_with($color, '#')) {
                    $color = '#' . $color;
                }

                $estado->update([
                    'nombre' => trim($estadoData['nombre']),
                    'color' => $color,
                ]);
            }
        }

        return back()->with('success', 'Estados actualizados y guardados exitosamente en la base de datos.');
    }
}
