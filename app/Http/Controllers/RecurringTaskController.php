<?php

namespace App\Http\Controllers;

use App\Models\RecurringTask;
use App\Models\RecurringSubtask;
use App\Services\RecurringTaskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RecurringTaskController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'module_id' => ['nullable', 'exists:modules,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['required', 'string', 'in:baja,media,alta,urgente'],
            'recurrence' => ['required', 'string', 'in:diaria,semanal,mensual,anual'],
            'start_date' => ['required', 'date'],
            'start_time' => ['nullable'],
            'end_time' => ['nullable'],
            'needs_review' => ['boolean'],
            'assigned_user_ids' => ['nullable', 'array'],
            'assigned_user_ids.*' => ['exists:users,id'],
            'subtasks' => ['nullable', 'array'],
            'subtasks.*.title' => ['required', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($request) {
            $user = Auth::user();

            $rule = RecurringTask::create([
                'project_id' => $request->project_id,
                'module_id' => $request->module_id ?: null,
                'created_by' => $user->id,
                'title' => trim($request->title),
                'description' => $request->description ? trim($request->description) : null,
                'priority' => $request->priority,
                'recurrence' => $request->recurrence,
                'is_active' => true,
                'start_date' => $request->start_date,
                'start_time' => $request->start_time ?: null,
                'end_time' => $request->end_time ?: null,
                'needs_review' => $request->boolean('needs_review'),
            ]);

            if ($request->has('assigned_user_ids')) {
                $rule->assignedUsers()->sync($request->assigned_user_ids);
            }

            if ($request->has('subtasks')) {
                foreach ($request->subtasks as $sub) {
                    if (is_array($sub) && !empty(trim($sub['title'] ?? ''))) {
                        RecurringSubtask::create([
                            'recurring_task_id' => $rule->id,
                            'title' => trim($sub['title']),
                        ]);
                    }
                }
            }
        });

        RecurringTaskService::syncRecurringTasks();

        return back()->with('success', 'Regla de tarea periódica creada exitosamente.');
    }

    public function update(Request $request, RecurringTask $recurringTask)
    {
        $request->validate([
            'module_id' => ['nullable', 'exists:modules,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['required', 'string', 'in:baja,media,alta,urgente'],
            'recurrence' => ['required', 'string', 'in:diaria,semanal,mensual,anual'],
            'start_date' => ['required', 'date'],
            'start_time' => ['nullable'],
            'end_time' => ['nullable'],
            'needs_review' => ['boolean'],
            'assigned_user_ids' => ['nullable', 'array'],
            'assigned_user_ids.*' => ['exists:users,id'],
            'subtasks' => ['nullable', 'array'],
            'subtasks.*.title' => ['required', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($request, $recurringTask) {
            $recurringTask->update([
                'module_id' => $request->module_id ?: null,
                'title' => trim($request->title),
                'description' => $request->description ? trim($request->description) : null,
                'priority' => $request->priority,
                'recurrence' => $request->recurrence,
                'start_date' => $request->start_date,
                'start_time' => $request->start_time ?: null,
                'end_time' => $request->end_time ?: null,
                'needs_review' => $request->boolean('needs_review'),
            ]);

            $recurringTask->assignedUsers()->sync($request->input('assigned_user_ids', []));

            $recurringTask->subtasks()->delete();
            if ($request->has('subtasks')) {
                foreach ($request->subtasks as $sub) {
                    if (is_array($sub) && !empty(trim($sub['title'] ?? ''))) {
                        RecurringSubtask::create([
                            'recurring_task_id' => $recurringTask->id,
                            'title' => trim($sub['title']),
                        ]);
                    }
                }
            }
        });

        RecurringTaskService::syncRecurringTasks();

        return back()->with('success', 'Regla de tarea periódica actualizada.');
    }

    public function toggleActive(RecurringTask $recurringTask)
    {
        $recurringTask->update([
            'is_active' => !$recurringTask->is_active,
        ]);

        if ($recurringTask->is_active) {
            RecurringTaskService::syncRecurringTasks();
        }

        $msg = $recurringTask->is_active ? 'Tarea periódica reanudada.' : 'Tarea periódica detenida/pausada.';
        return back()->with('success', $msg);
    }

    public function destroy(RecurringTask $recurringTask)
    {
        $recurringTask->delete();
        return back()->with('success', 'Regla de tarea periódica eliminada.');
    }
}
