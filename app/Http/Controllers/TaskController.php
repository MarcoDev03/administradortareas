<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Subtask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'module_id' => ['nullable', 'exists:modules,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:no_iniciado,en_proceso,en_revision,rechazado,finalizado'],
            'priority' => ['required', 'string', 'in:baja,media,alta,urgente'],
            'start_date' => ['nullable', 'date'],
            'start_time' => ['nullable'],
            'end_date' => ['nullable', 'date'],
            'end_time' => ['nullable'],
            'needs_review' => ['boolean'],
            'recurrence' => ['nullable', 'string', 'in:ninguna,diaria,semanal,mensual,anual'],
            'assigned_user_ids' => ['nullable', 'array'],
            'assigned_user_ids.*' => ['exists:users,id'],
            'subtasks' => ['nullable', 'array'],
            'subtasks.*.title' => ['required', 'string', 'max:255'],
            'subtasks.*.status' => ['nullable', 'string', 'in:no_iniciado,finalizado'],
            'subtasks.*.id' => ['nullable', 'integer'],
            'create_per_user' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($request) {
            $user = Auth::user();
            $assignedUsers = $request->input('assigned_user_ids', []);
            $isCreatePerUser = $request->boolean('create_per_user') && !empty($assignedUsers);

            if ($isCreatePerUser) {
                // Crear 1 tarea independiente para cada usuario asignado
                foreach ($assignedUsers as $userId) {
                    $completedAt = $request->status === 'finalizado' ? now() : null;

                    $task = Task::create([
                        'project_id' => $request->project_id,
                        'module_id' => $request->module_id ?: null,
                        'created_by' => $user->id,
                        'title' => trim($request->title),
                        'description' => $request->description ? trim($request->description) : null,
                        'status' => $request->status,
                        'priority' => $request->priority,
                        'start_date' => $request->start_date ?: null,
                        'start_time' => $request->start_time ?: null,
                        'end_date' => $request->end_date ?: null,
                        'end_time' => $request->end_time ?: null,
                        'needs_review' => $request->boolean('needs_review'),
                        'recurrence' => $request->input('recurrence', 'ninguna'),
                        'completed_at' => $completedAt,
                    ]);

                    $task->assignedUsers()->sync([$userId]);

                    if ($request->has('subtasks')) {
                        foreach ($request->subtasks as $sub) {
                            if (is_array($sub) && !empty(trim($sub['title'] ?? ''))) {
                                Subtask::create([
                                    'task_id' => $task->id,
                                    'title'   => trim($sub['title']),
                                    'status'  => $sub['status'] ?? 'no_iniciado',
                                ]);
                            }
                        }
                    }
                }
            } else {
                // Lógica estándar: 1 tarea asignada a los usuarios seleccionados
                $completedAt = $request->status === 'finalizado' ? now() : null;

                $task = Task::create([
                    'project_id' => $request->project_id,
                    'module_id' => $request->module_id ?: null,
                    'created_by' => $user->id,
                    'title' => trim($request->title),
                    'description' => $request->description ? trim($request->description) : null,
                    'status' => $request->status,
                    'priority' => $request->priority,
                    'start_date' => $request->start_date ?: null,
                    'start_time' => $request->start_time ?: null,
                    'end_date' => $request->end_date ?: null,
                    'end_time' => $request->end_time ?: null,
                    'needs_review' => $request->boolean('needs_review'),
                    'recurrence' => $request->input('recurrence', 'ninguna'),
                    'completed_at' => $completedAt,
                ]);

                if (!empty($assignedUsers)) {
                    $task->assignedUsers()->sync($assignedUsers);
                }

                if ($request->has('subtasks')) {
                    foreach ($request->subtasks as $sub) {
                        if (is_array($sub) && !empty(trim($sub['title'] ?? ''))) {
                            Subtask::create([
                                'task_id' => $task->id,
                                'title'   => trim($sub['title']),
                                'status'  => $sub['status'] ?? 'no_iniciado',
                            ]);
                        }
                    }
                }
            }
        });

        \App\Services\RecurringTaskService::syncRecurringTasks();

        $msg = $request->boolean('create_per_user') && count($request->input('assigned_user_ids', [])) > 1
            ? 'Tareas creadas exitosamente para cada usuario.'
            : 'Tarea creada exitosamente.';

        return back()->with('success', $msg);
    }

    public function update(Request $request, Task $task)
    {
        $request->validate([
            'module_id' => ['nullable', 'exists:modules,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:no_iniciado,en_proceso,en_revision,rechazado,finalizado'],
            'priority' => ['required', 'string', 'in:baja,media,alta,urgente'],
            'start_date' => ['nullable', 'date'],
            'start_time' => ['nullable'],
            'end_date' => ['nullable', 'date'],
            'end_time' => ['nullable'],
            'needs_review' => ['boolean'],
            'recurrence' => ['nullable', 'string', 'in:ninguna,diaria,semanal,mensual,anual'],
            'assigned_user_ids' => ['nullable', 'array'],
            'assigned_user_ids.*' => ['exists:users,id'],
            'subtasks' => ['nullable', 'array'],
            'subtasks.*.title' => ['required', 'string', 'max:255'],
            'subtasks.*.status' => ['nullable', 'string', 'in:no_iniciado,finalizado'],
            'subtasks.*.id' => ['nullable', 'integer'],
        ]);

        DB::transaction(function () use ($request, $task) {
            $newStatus = $request->status;
            $completedAt = $task->completed_at;

            if ($newStatus === 'finalizado') {
                if (!$completedAt) {
                    $completedAt = now();
                }
            } else {
                $completedAt = null;
            }

            $task->update([
                'module_id' => $request->module_id ?: null,
                'title' => trim($request->title),
                'description' => $request->description ? trim($request->description) : null,
                'status' => $newStatus,
                'priority' => $request->priority,
                'start_date' => $request->start_date ?: null,
                'start_time' => $request->start_time ?: null,
                'end_date' => $request->end_date ?: null,
                'end_time' => $request->end_time ?: null,
                'needs_review' => $request->boolean('needs_review'),
                'recurrence' => $request->input('recurrence', $task->recurrence ?: 'ninguna'),
                'completed_at' => $completedAt,
            ]);

            $task->assignedUsers()->sync($request->input('assigned_user_ids', []));

            // Sync subtasks: delete removed ones, update existing, create new
            if ($request->has('subtasks')) {
                $submittedIds = collect($request->subtasks)
                    ->filter(fn($s) => !empty($s['id'] ?? null))
                    ->pluck('id')
                    ->map('intval')
                    ->toArray();

                // Delete subtasks not present in the submitted list
                $task->subtasks()->whereNotIn('id', $submittedIds)->delete();

                foreach ($request->subtasks as $sub) {
                    if (!is_array($sub) || empty(trim($sub['title'] ?? ''))) continue;

                    if (!empty($sub['id'])) {
                        // Update existing subtask
                        $task->subtasks()->where('id', $sub['id'])->update([
                            'title'  => trim($sub['title']),
                            'status' => $sub['status'] ?? 'no_iniciado',
                        ]);
                    } else {
                        // Create new subtask
                        Subtask::create([
                            'task_id' => $task->id,
                            'title'   => trim($sub['title']),
                            'status'  => $sub['status'] ?? 'no_iniciado',
                        ]);
                    }
                }
            } else {
                // No subtasks submitted — delete all
                $task->subtasks()->delete();
            }
        });

        \App\Services\RecurringTaskService::syncRecurringTasks();

        return back()->with('success', 'Tarea actualizada exitosamente.');
    }

    public function updateStatus(Request $request, Task $task)
    {
        $request->validate([
            'status' => ['required', 'string', 'in:no_iniciado,en_proceso,en_revision,rechazado,finalizado'],
        ]);

        $user = Auth::user();
        $newStatus = $request->status;

        // Reglas de revisión según rol
        if ($task->needs_review && in_array($newStatus, ['finalizado', 'rechazado']) && !$user->hasFullAccess()) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Solo Admin o Desarrollador pueden aprobar o rechazar esta tarea.'], 403);
            }
            return back()->with('error', 'Solo Admin o Desarrollador pueden aprobar o rechazar esta tarea.');
        }

        $completedAt = $task->completed_at;
        if ($newStatus === 'finalizado') {
            if (!$completedAt) {
                $completedAt = now();
            }
        } else {
            $completedAt = null;
        }

        $task->update([
            'status' => $newStatus,
            'completed_at' => $completedAt,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'status' => $newStatus]);
        }

        return back()->with('success', 'Estado de la tarea actualizado.');
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return back()->with('success', 'Tarea eliminada exitosamente.');
    }
}
