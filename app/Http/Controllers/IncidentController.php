<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IncidentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'project_id' => ['nullable', 'exists:projects,id'],
            'titulo' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'prioridad' => ['required', 'string', 'in:baja,media,alta,urgente'],
            'estado' => ['required', 'string', 'in:abierta,en_proceso,resuelta'],
        ]);

        Incident::create([
            'project_id' => $request->project_id ?: null,
            'user_id' => Auth::id(),
            'titulo' => trim($request->titulo),
            'descripcion' => $request->descripcion ? trim($request->descripcion) : null,
            'prioridad' => $request->prioridad,
            'estado' => $request->estado,
        ]);

        return back()->with('success', 'Incidencia reportada exitosamente.');
    }

    public function update(Request $request, Incident $incident)
    {
        $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'prioridad' => ['required', 'string', 'in:baja,media,alta,urgente'],
            'estado' => ['required', 'string', 'in:abierta,en_proceso,resuelta'],
        ]);

        $incident->update([
            'titulo' => trim($request->titulo),
            'descripcion' => $request->descripcion ? trim($request->descripcion) : null,
            'prioridad' => $request->prioridad,
            'estado' => $request->estado,
        ]);

        return back()->with('success', 'Incidencia actualizada exitosamente.');
    }

    public function updateStatus(Request $request, Incident $incident)
    {
        $request->validate([
            'estado' => ['required', 'string', 'in:abierta,en_proceso,resuelta'],
        ]);

        $incident->update(['estado' => $request->estado]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'estado' => $request->estado]);
        }

        return back()->with('success', 'Estado de incidencia actualizado.');
    }

    public function destroy(Incident $incident)
    {
        $incident->delete();
        return back()->with('success', 'Incidencia eliminada exitosamente.');
    }
}
