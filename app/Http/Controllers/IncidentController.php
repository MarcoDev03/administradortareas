<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class IncidentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'project_id' => ['nullable', 'exists:projects,id'],
            'assigned_user_id' => ['nullable', 'exists:users,id'],
            'titulo' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'prioridad' => ['required', 'string', 'in:baja,media,alta,urgente'],
            'estado' => ['required', 'string', 'in:abierta,en_proceso,resuelta'],
            'attachment_file' => [
                'nullable',
                'file',
                'max:10240',
                'mimes:pdf,doc,docx,xls,xlsx,csv,txt,zip,rar,png,jpg,jpeg,webp',
            ],
            'attachment_files.*' => [
                'file',
                'max:10240',
                'mimes:pdf,doc,docx,xls,xlsx,csv,txt,zip,rar,png,jpg,jpeg,webp',
            ],
        ], [
            'attachment_file.max' => 'El archivo adjunto no debe superar los 10 MB.',
            'attachment_file.mimes' => 'Formato no permitido. Se admiten documentos (PDF, Word, Excel, CSV, TXT, ZIP, RAR) e imágenes.',
            'attachment_files.*.max' => 'Los archivos adjuntos no deben superar los 10 MB.',
            'attachment_files.*.mimes' => 'Formato no permitido. Se admiten documentos (PDF, Word, Excel, CSV, TXT, ZIP, RAR) e imágenes.',
        ]);

        $storedPaths = [];
        $incident = null;

        try {
            DB::transaction(function () use ($request, &$storedPaths, &$incident) {
                $incident = Incident::create([
                    'project_id' => $request->project_id ?: null,
                    'user_id' => Auth::id(),
                    'assigned_user_id' => $request->assigned_user_id ?: null,
                    'titulo' => trim($request->titulo),
                    'descripcion' => $request->descripcion ? trim($request->descripcion) : null,
                    'prioridad' => $request->prioridad,
                    'estado' => $request->estado,
                ]);

                $files = [];
                if ($request->hasFile('attachment_file')) {
                    $files[] = $request->file('attachment_file');
                }
                if ($request->hasFile('attachment_files')) {
                    foreach ($request->file('attachment_files') as $f) {
                        if ($f) $files[] = $f;
                    }
                }
                if ($request->hasFile('file') && !in_array($request->file('file'), $files)) {
                    $files[] = $request->file('file');
                }

                foreach ($files as $file) {
                    $path = $file->store('attachments', 'local');
                    $storedPaths[] = $path;

                    $incident->attachments()->create([
                        'user_id' => Auth::id(),
                        'original_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'mime_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                    ]);
                }
            });
        } catch (\Throwable $e) {
            foreach ($storedPaths as $p) {
                if (Storage::disk('local')->exists($p)) {
                    Storage::disk('local')->delete($p);
                }
            }
            throw $e;
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Incidencia reportada exitosamente.',
                'incident' => $incident->load(['project', 'reporter', 'assignedUser', 'attachments']),
            ]);
        }

        return back()->with('success', 'Incidencia reportada exitosamente.');
    }

    public function update(Request $request, Incident $incident)
    {
        $user = Auth::user();
        if (!$user->hasFullAccess() && $incident->user_id !== $user->id && $incident->assigned_user_id !== $user->id) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => 'No tienes permisos para editar esta incidencia.'], 403);
            }
            return back()->with('error', 'No tienes permisos para editar esta incidencia.');
        }

        $request->validate([
            'project_id' => ['nullable', 'exists:projects,id'],
            'assigned_user_id' => ['nullable', 'exists:users,id'],
            'titulo' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'prioridad' => ['required', 'string', 'in:baja,media,alta,urgente'],
            'estado' => ['required', 'string', 'in:abierta,en_proceso,resuelta'],
            'remove_attachment_ids' => ['nullable', 'array'],
            'remove_attachment_ids.*' => ['integer'],
            'attachment_file' => [
                'nullable',
                'file',
                'max:10240',
                'mimes:pdf,doc,docx,xls,xlsx,csv,txt,zip,rar,png,jpg,jpeg,webp',
            ],
            'attachment_files.*' => [
                'file',
                'max:10240',
                'mimes:pdf,doc,docx,xls,xlsx,csv,txt,zip,rar,png,jpg,jpeg,webp',
            ],
        ], [
            'attachment_file.max' => 'El archivo adjunto no debe superar los 10 MB.',
            'attachment_file.mimes' => 'Formato no permitido. Se admiten documentos (PDF, Word, Excel, CSV, TXT, ZIP, RAR) e imágenes.',
            'attachment_files.*.max' => 'Los archivos adjuntos no deben superar los 10 MB.',
            'attachment_files.*.mimes' => 'Formato no permitido. Se admiten documentos (PDF, Word, Excel, CSV, TXT, ZIP, RAR) e imágenes.',
        ]);

        $storedPaths = [];

        try {
            DB::transaction(function () use ($request, $incident, &$storedPaths) {
                $incident->update([
                    'project_id' => $request->has('project_id') ? ($request->project_id ?: null) : $incident->project_id,
                    'assigned_user_id' => $request->has('assigned_user_id') ? ($request->assigned_user_id ?: null) : $incident->assigned_user_id,
                    'titulo' => trim($request->titulo),
                    'descripcion' => $request->descripcion ? trim($request->descripcion) : null,
                    'prioridad' => $request->prioridad,
                    'estado' => $request->estado,
                ]);

                // 1. Eliminar archivos existentes marcados para eliminación
                if ($request->has('remove_attachment_ids')) {
                    $removeIds = (array) $request->input('remove_attachment_ids', []);
                    $attachmentsToRemove = $incident->attachments()->whereIn('id', $removeIds)->get();
                    foreach ($attachmentsToRemove as $att) {
                        if (Storage::disk('local')->exists($att->file_path)) {
                            Storage::disk('local')->delete($att->file_path);
                        }
                        $att->delete();
                    }
                }

                // 2. Subir y asociar nuevos archivos adjuntos
                $files = [];
                if ($request->hasFile('attachment_file')) {
                    $files[] = $request->file('attachment_file');
                }
                if ($request->hasFile('attachment_files')) {
                    foreach ($request->file('attachment_files') as $f) {
                        if ($f) $files[] = $f;
                    }
                }
                if ($request->hasFile('file') && !in_array($request->file('file'), $files)) {
                    $files[] = $request->file('file');
                }

                foreach ($files as $file) {
                    $path = $file->store('attachments', 'local');
                    $storedPaths[] = $path;

                    $incident->attachments()->create([
                        'user_id' => Auth::id(),
                        'original_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'mime_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                    ]);
                }
            });
        } catch (\Throwable $e) {
            foreach ($storedPaths as $p) {
                if (Storage::disk('local')->exists($p)) {
                    Storage::disk('local')->delete($p);
                }
            }
            throw $e;
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Incidencia actualizada exitosamente.',
                'incident' => $incident->load(['project', 'reporter', 'assignedUser', 'attachments']),
            ]);
        }

        return back()->with('success', 'Incidencia actualizada exitosamente.');
    }

    public function updateStatus(Request $request, Incident $incident)
    {
        $request->validate([
            'estado' => ['required', 'string', 'in:abierta,en_proceso,resuelta'],
        ]);

        $incident->update(['estado' => $request->estado]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'id' => $incident->id,
                'estado' => $request->estado,
                'message' => 'Estado de incidencia actualizado.',
            ]);
        }

        return back()->with('success', 'Estado de incidencia actualizado.');
    }

    public function destroy(Incident $incident)
    {
        $user = Auth::user();
        if (!$user->hasFullAccess() && $incident->user_id !== $user->id) {
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['error' => 'No tienes permisos para eliminar esta incidencia.'], 403);
            }
            return back()->with('error', 'No tienes permisos para eliminar esta incidencia.');
        }

        $id = $incident->id;
        foreach ($incident->attachments as $att) {
            if (Storage::disk('local')->exists($att->file_path)) {
                Storage::disk('local')->delete($att->file_path);
            }
        }
        $incident->attachments()->delete();
        $incident->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'id' => $id,
                'message' => 'Incidencia eliminada exitosamente.',
            ]);
        }

        return back()->with('success', 'Incidencia eliminada exitosamente.');
    }
}
