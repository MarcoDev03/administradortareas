<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        Module::create([
            'project_id' => $request->project_id,
            'name' => trim($request->name),
        ]);

        return back()->with('success', 'Módulo creado exitosamente.');
    }

    public function update(Request $request, Module $module)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $module->update([
            'name' => trim($request->name),
        ]);

        return back()->with('success', 'Módulo actualizado exitosamente.');
    }

    public function destroy(Module $module)
    {
        $module->delete();
        return back()->with('success', 'Módulo eliminado exitosamente.');
    }
}
