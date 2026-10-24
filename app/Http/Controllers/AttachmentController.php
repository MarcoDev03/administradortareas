<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Incident;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    protected function getAttachable(string $type, int $id)
    {
        $normalized = strtolower($type);
        if ($normalized === 'task') {
            return Task::find($id);
        }
        if ($normalized === 'incident') {
            return Incident::find($id);
        }
        return null;
    }

    protected function userCanAccess($attachable): bool
    {
        $user = Auth::user();
        if (!$user || !$attachable) {
            return false;
        }

        if ($user->hasFullAccess()) {
            return true;
        }

        if ($attachable instanceof Task) {
            if ($attachable->created_by === $user->id) {
                return true;
            }
            if ($attachable->assignedUsers()->where('users.id', $user->id)->exists()) {
                return true;
            }
            if ($attachable->project && $attachable->project->members()->where('users.id', $user->id)->exists()) {
                return true;
            }
            return false;
        }

        if ($attachable instanceof Incident) {
            if ($attachable->user_id === $user->id || $attachable->assigned_user_id === $user->id) {
                return true;
            }
            if ($attachable->project && $attachable->project->members()->where('users.id', $user->id)->exists()) {
                return true;
            }
            if (is_null($attachable->project_id)) {
                return true;
            }
            return false;
        }

        return false;
    }

    public function index(Request $request)
    {
        $request->validate([
            'attachable_type' => ['required', 'string', 'in:task,incident,Task,Incident'],
            'attachable_id' => ['required', 'integer'],
        ]);

        $attachable = $this->getAttachable($request->attachable_type, (int)$request->attachable_id);
        if (!$attachable || !$this->userCanAccess($attachable)) {
            return response()->json(['error' => 'No autorizado para ver adjuntos.'], 403);
        }

        $user = Auth::user();
        $attachments = $attachable->attachments()
            ->with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($att) use ($user) {
                return [
                    'id' => $att->id,
                    'original_name' => $att->original_name,
                    'file_size' => $att->formatted_size,
                    'mime_type' => $att->mime_type,
                    'user_name' => $att->user ? $att->user->name : 'Usuario',
                    'created_at' => $att->created_at ? $att->created_at->format('d/m/Y H:i') : '',
                    'download_url' => route('attachments.download', $att->id),
                    'can_delete' => $user->hasFullAccess() || $att->user_id === $user->id,
                ];
            });

        return response()->json([
            'success' => true,
            'attachments' => $attachments,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'attachable_type' => ['required', 'string', 'in:task,incident,Task,Incident'],
            'attachable_id' => ['required', 'integer'],
            'file' => [
                'required',
                'file',
                'max:10240', // 10 MB máx
                'mimes:pdf,doc,docx,xls,xlsx,csv,txt,zip,rar,png,jpg,jpeg,webp',
            ],
        ], [
            'file.max' => 'El archivo no debe superar los 10 MB.',
            'file.mimes' => 'Formato no permitido. Se admiten documentos (PDF, Word, Excel, CSV, TXT, ZIP, RAR) e imágenes.',
        ]);

        $attachable = $this->getAttachable($request->attachable_type, (int)$request->attachable_id);
        if (!$attachable || !$this->userCanAccess($attachable)) {
            return response()->json(['error' => 'No tienes permisos para adjuntar archivos a este elemento.'], 403);
        }

        $uploadedFile = $request->file('file');
        $originalName = $uploadedFile->getClientOriginalName();
        $mimeType = $uploadedFile->getMimeType();
        $fileSize = $uploadedFile->getSize();

        // Almacenamiento seguro en disco local privado
        $path = $uploadedFile->store('attachments', 'local');

        $attachment = $attachable->attachments()->create([
            'user_id' => Auth::id(),
            'original_name' => $originalName,
            'file_path' => $path,
            'mime_type' => $mimeType,
            'file_size' => $fileSize,
        ]);

        $attachment->load('user:id,name');

        return response()->json([
            'success' => true,
            'message' => 'Archivo adjuntado exitosamente.',
            'attachment' => [
                'id' => $attachment->id,
                'original_name' => $attachment->original_name,
                'file_size' => $attachment->formatted_size,
                'mime_type' => $attachment->mime_type,
                'user_name' => $attachment->user ? $attachment->user->name : Auth::user()->name,
                'created_at' => $attachment->created_at ? $attachment->created_at->format('d/m/Y H:i') : '',
                'download_url' => route('attachments.download', $attachment->id),
                'can_delete' => true,
            ],
        ]);
    }

    public function download(Attachment $attachment)
    {
        $attachable = $attachment->attachable;
        if (!$attachable || !$this->userCanAccess($attachable)) {
            abort(403, 'No tienes permisos para descargar este archivo.');
        }

        if (!Storage::disk('local')->exists($attachment->file_path)) {
            abort(404, 'El archivo no fue encontrado en el servidor.');
        }

        return Storage::disk('local')->download($attachment->file_path, $attachment->original_name);
    }

    public function destroy(Attachment $attachment)
    {
        $user = Auth::user();
        if (!$user->hasFullAccess() && $attachment->user_id !== $user->id) {
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['error' => 'No tienes permisos para eliminar este archivo.'], 403);
            }
            return back()->with('error', 'No tienes permisos para eliminar este archivo.');
        }

        if (Storage::disk('local')->exists($attachment->file_path)) {
            Storage::disk('local')->delete($attachment->file_path);
        }

        $attachment->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Archivo eliminado.']);
        }

        return back()->with('success', 'Archivo eliminado exitosamente.');
    }
}
