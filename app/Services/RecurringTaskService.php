<?php

namespace App\Services;

use App\Models\RecurringTask;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class RecurringTaskService
{
    /**
     * Checks all active recurring task rules (is_active = true) and automatically generates
     * missing task instances for the target (or current) month.
     * Runs dynamically when a user accesses the dashboard or matrix without requiring cron/schedule.
     */
    public static function syncRecurringTasks(?Carbon $targetMonth = null, ?array $projectIds = null): void
    {
        if (!Schema::hasTable('recurring_tasks') || !Schema::hasTable('tasks')) {
            return;
        }

        $rulesQuery = RecurringTask::with(['assignedUsers', 'subtasks'])
            ->where('is_active', true);

        if (!empty($projectIds)) {
            $rulesQuery->whereIn('project_id', $projectIds);
        }

        $activeRules = $rulesQuery->get();

        if ($activeRules->isEmpty()) {
            return;
        }

        $now = Carbon::now();
        $target = $targetMonth ? $targetMonth->copy() : $now->copy();
        $startOfMonth = $target->copy()->startOfMonth();
        $endOfMonth = $target->copy()->endOfMonth();
        $sMonthStr = $startOfMonth->toDateString();
        $eMonthStr = $endOfMonth->toDateString();

        // Cargar en UNA SOLA consulta todas las instancias existentes de este mes para estas reglas
        $existingInstances = Task::whereIn('recurring_task_id', $activeRules->pluck('id'))
            ->whereBetween('start_date', [$sMonthStr, $eMonthStr])
            ->get(['id', 'recurring_task_id', 'start_date', 'status']);

        // Mapa en memoria para comprobación O(1) instantánea sin round-trips a la base de datos
        $existingMap = [];
        foreach ($existingInstances as $tInst) {
            $dStr = $tInst->start_date instanceof \DateTimeInterface
                ? $tInst->start_date->format('Y-m-d')
                : substr((string)$tInst->start_date, 0, 10);
            $existingMap[$tInst->recurring_task_id . '_' . $dStr] = true;
        }

        foreach ($activeRules as $rule) {
            if (!$rule->start_date) {
                continue;
            }

            $ruleStartDate = Carbon::parse($rule->start_date);
            $wasGenerated = false;

            switch ($rule->recurrence) {
                case 'diaria':
                    $cursor = $startOfMonth->copy();
                    if ($cursor->lt($ruleStartDate)) {
                        $cursor = $ruleStartDate->copy();
                    }

                    while ($cursor->lte($endOfMonth)) {
                        $dateStr = $cursor->toDateString();
                        $key = $rule->id . '_' . $dateStr;
                        if (!isset($existingMap[$key])) {
                            self::createInstance($rule, $cursor);
                            $existingMap[$key] = true;
                            $wasGenerated = true;
                        }
                        $cursor->addDay();
                    }
                    break;

                case 'semanal':
                    $dayOfWeek = $ruleStartDate->dayOfWeek;
                    $cursor = $startOfMonth->copy();
                    if ($cursor->lt($ruleStartDate)) {
                        $cursor = $ruleStartDate->copy();
                    }

                    while ($cursor->dayOfWeek !== $dayOfWeek && $cursor->lte($endOfMonth)) {
                        $cursor->addDay();
                    }

                    while ($cursor->lte($endOfMonth)) {
                        $dateStr = $cursor->toDateString();
                        $key = $rule->id . '_' . $dateStr;
                        if (!isset($existingMap[$key])) {
                            self::createInstance($rule, $cursor);
                            $existingMap[$key] = true;
                            $wasGenerated = true;
                        }
                        $cursor->addWeek();
                    }
                    break;

                case 'mensual':
                    $dayOfMonth = $ruleStartDate->day;
                    $daysInMonth = $startOfMonth->daysInMonth;
                    $targetDay = min($dayOfMonth, $daysInMonth);
                    $targetDate = $startOfMonth->copy()->day($targetDay);

                    if ($targetDate->gte($ruleStartDate) && $targetDate->lte($endOfMonth)) {
                        $dateStr = $targetDate->toDateString();
                        $key = $rule->id . '_' . $dateStr;
                        if (!isset($existingMap[$key])) {
                            self::createInstance($rule, $targetDate);
                            $existingMap[$key] = true;
                            $wasGenerated = true;
                        }
                    }
                    break;

                case 'anual':
                    $targetMonthNum = $ruleStartDate->month;
                    if ($startOfMonth->month === $targetMonthNum) {
                        $dayOfMonth = $ruleStartDate->day;
                        $daysInMonth = $startOfMonth->daysInMonth;
                        $targetDay = min($dayOfMonth, $daysInMonth);
                        $targetDate = $startOfMonth->copy()->day($targetDay);

                        if ($targetDate->gte($ruleStartDate) && $targetDate->lte($endOfMonth)) {
                            $dateStr = $targetDate->toDateString();
                            $key = $rule->id . '_' . $dateStr;
                            if (!isset($existingMap[$key])) {
                                self::createInstance($rule, $targetDate);
                                $existingMap[$key] = true;
                                $wasGenerated = true;
                            }
                        }
                    }
                    break;
            }

            if ($wasGenerated) {
                $rule->update(['last_generated_at' => $now]);
            }
        }
    }

    public static function createInstance(RecurringTask $rule, Carbon $date): Task
    {
        $dateStr = $date->toDateString();

        $instance = Task::create([
            'project_id' => $rule->project_id,
            'module_id' => $rule->module_id,
            'created_by' => $rule->created_by,
            'recurring_task_id' => $rule->id,
            'title' => $rule->title,
            'description' => $rule->description,
            'status' => 'no_iniciado',
            'priority' => $rule->priority,
            'start_date' => $dateStr,
            'start_time' => $rule->start_time,
            'end_date' => $dateStr,
            'end_time' => $rule->end_time,
            'needs_review' => $rule->needs_review,
            'recurrence' => $rule->recurrence,
        ]);

        // Sync assigned users
        if ($rule->assignedUsers->isNotEmpty()) {
            $instance->assignedUsers()->sync($rule->assignedUsers->pluck('id'));
        }

        // Copy subtasks as templates
        foreach ($rule->subtasks as $st) {
            $instance->subtasks()->create([
                'title' => $st->title,
                'status' => 'no_iniciado',
            ]);
        }

        return $instance;
    }

    public static function ensureInstanceExists(RecurringTask $rule, Carbon $date): Task
    {
        $dateStr = $date->toDateString();

        // Check if an instance already exists for this rule and date
        $instance = Task::where('recurring_task_id', $rule->id)
            ->whereDate('start_date', $dateStr)
            ->first();

        if (!$instance) {
            $instance = self::createInstance($rule, $date);
        }

        return $instance;
    }
}
