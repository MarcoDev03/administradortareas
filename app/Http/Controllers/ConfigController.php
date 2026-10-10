<?php

namespace App\Http\Controllers;

use App\Models\ConfigEstilosEstadosPrioridadPeriodicidad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ConfigController extends Controller
{
    /**
     * Muestra la vista de configuración cargando los estados desde la base de datos.
     */
    public function index()
    {
        $estados = ConfigEstilosEstadosPrioridadPeriodicidad::where(function ($q) {
            $q->where('tipo', 'estado')->orWhereNull('tipo');
        })->orderBy('id')->get();

        $prioridades = ConfigEstilosEstadosPrioridadPeriodicidad::where('tipo', 'prioridad')
            ->orderBy('id')
            ->get();

        $periodicidades = ConfigEstilosEstadosPrioridadPeriodicidad::where('tipo', 'periodicidad')
            ->orderBy('id')
            ->get();

        return view('config.index', compact('estados', 'prioridades', 'periodicidades'));
    }

    /**
     * Actualiza y guarda los estados e iconos en la base de datos y en la carpeta public.
     */
    public function update(Request $request)
    {
        return $this->saveConfigItems(
            $request, 
            'estados', 
            'icons/estados', 
            'estado', 
            'Estados e iconos guardados exitosamente.', 
            'estados'
        );
    }

    /**
     * Actualiza y guarda las prioridades e iconos en la base de datos y en la carpeta public.
     */
    public function updatePrioridades(Request $request)
    {
        return $this->saveConfigItems(
            $request, 
            'prioridades', 
            'icons/prioridades', 
            'prioridad', 
            'Prioridades e iconos guardados exitosamente.', 
            'prioridad'
        );
    }

    /**
     * Actualiza y guarda las periodicidades e iconos en la base de datos y en la carpeta public.
     */
    public function updatePeriodicidades(Request $request)
    {
        return $this->saveConfigItems(
            $request, 
            'periodicidades', 
            'icons/periodicidades', 
            'periodicidad', 
            'Tareas periódicas y frecuencias guardadas exitosamente.', 
            'periodicidad'
        );
    }

    /**
     * Helper reutilizable para validar y guardar configuraciones con sus iconos.
     */
    protected function saveConfigItems(Request $request, string $fieldKey, string $folder, string $prefix, string $successMessage, string $tabName)
    {
        $request->validate([
            $fieldKey => ['required', 'array'],
            "{$fieldKey}.*.id" => ['required', 'exists:config_estilos_estados_prioridad_periodicidad,id'],
            "{$fieldKey}.*.nombre" => ['required', 'string', 'max:100'],
            "{$fieldKey}.*.color" => ['required', 'string', 'max:25'],
            "{$fieldKey}.*.icono" => ['nullable', 'file', 'mimes:png,jpg,jpeg,svg,webp,gif,ico', 'max:3072'],
            "{$fieldKey}.*.eliminar_icono" => ['nullable'],
        ]);

        foreach ($request->input($fieldKey) as $id => $itemData) {
            $model = ConfigEstilosEstadosPrioridadPeriodicidad::find($itemData['id']);
            if ($model) {
                $color = trim($itemData['color']);
                if (!str_starts_with($color, '#')) {
                    $color = '#' . $color;
                }

                $updateData = [
                    'nombre' => trim($itemData['nombre']),
                    'color' => $color,
                ];

                $hasNewIcon = $request->hasFile("{$fieldKey}.{$itemData['id']}.icono");
                $shouldDelete = !empty($itemData['eliminar_icono']) && in_array((string)$itemData['eliminar_icono'], ['1', 'true'], true);

                // Procesar carga de nuevo icono
                if ($hasNewIcon) {
                    $file = $request->file("{$fieldKey}.{$itemData['id']}.icono");
                    if ($file->isValid()) {
                        $destinationPath = public_path($folder);
                        if (!File::isDirectory($destinationPath)) {
                            File::makeDirectory($destinationPath, 0755, true, true);
                        }

                        // Eliminar imagen anterior si existe físicamente y no está compartida
                        if ($model->ruta_icono) {
                            $isShared = ConfigEstilosEstadosPrioridadPeriodicidad::where('id', '!=', $model->id)
                                ->where('ruta_icono', $model->ruta_icono)
                                ->exists();
                            if (!$isShared && File::exists(public_path($model->ruta_icono))) {
                                File::delete(public_path($model->ruta_icono));
                            }
                        }

                        $clave = $model->clave ?: Str::slug($itemData['nombre'], '_');
                        $extension = strtolower($file->getClientOriginalExtension());
                        $nombreDefinido = "{$prefix}_{$clave}.{$extension}";

                        // Eliminar si ya existía un archivo con el mismo nombre y extensión
                        $targetFullPath = $destinationPath . DIRECTORY_SEPARATOR . $nombreDefinido;
                        if (File::exists($targetFullPath)) {
                            File::delete($targetFullPath);
                        }

                        $file->move($destinationPath, $nombreDefinido);
                        $updateData['ruta_icono'] = "{$folder}/" . $nombreDefinido;
                    }
                } elseif ($shouldDelete) {
                    // Si se solicitó eliminar el icono y no se subió uno nuevo
                    if ($model->ruta_icono) {
                        $isShared = ConfigEstilosEstadosPrioridadPeriodicidad::where('id', '!=', $model->id)
                            ->where('ruta_icono', $model->ruta_icono)
                            ->exists();
                        if (!$isShared && File::exists(public_path($model->ruta_icono))) {
                            File::delete(public_path($model->ruta_icono));
                        }
                    }
                    $updateData['ruta_icono'] = null;
                }

                $model->update($updateData);
            }
        }

        return back()->with('success', $successMessage)->with('active_tab', $tabName);
    }

    /**
     * Elimina el icono personalizado de un elemento específico.
     */
    public function destroyIcon($id)
    {
        $model = ConfigEstilosEstadosPrioridadPeriodicidad::findOrFail($id);
        if ($model->ruta_icono) {
            $isShared = ConfigEstilosEstadosPrioridadPeriodicidad::where('id', '!=', $model->id)
                ->where('ruta_icono', $model->ruta_icono)
                ->exists();
            if (!$isShared && File::exists(public_path($model->ruta_icono))) {
                File::delete(public_path($model->ruta_icono));
            }
            $model->update(['ruta_icono' => null]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Icono personalizado eliminado correctamente.',
            'color' => $model->color,
        ]);
    }
}
