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
     * missing task instances for the current month.
     * Runs dynamically when a user accesses the dashboard without requiring cron/schedule.
     */
    public static function syncRecurringTasks(): void
    {
        if (!Schema::hasTable('recurring_tasks') || !Schema::hasTable('tasks')) {
            return;
        }

        $activeRules = RecurringTask::with(['assignedUsers', 'subtasks'])
            ->where('is_active', true)
            ->get();

        if ($activeRules->isEmpty()) {
            return;
        }

        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

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
                        if (self::ensureInstanceExists($rule, $cursor)) {
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
                        if (self::ensureInstanceExists($rule, $cursor)) {
                            $wasGenerated = true;
                        }
                        $cursor->addWeek();
                    }
                    break;

                case 'mensual':
                    $dayOfMonth = $ruleStartDate->day;
                    $monthCursor = $now->copy()->startOfYear();
                    $endOfYear = $now->copy()->endOfYear();

                    while ($monthCursor->lte($endOfYear)) {
                        $daysInMonth = $monthCursor->daysInMonth;
                        $targetDay = min($dayOfMonth, $daysInMonth);
                        $targetDate = $monthCursor->copy()->day($targetDay);

                        if ($targetDate->gte($ruleStartDate) && $targetDate->lte($endOfYear)) {
                            if (self::ensureInstanceExists($rule, $targetDate)) {
                                $wasGenerated = true;
                            }
                        }
                        $monthCursor->addMonth();
                    }
                    break;

                case 'anual':
                    $targetMonth = $ruleStartDate->month;
                    $dayOfMonth = $ruleStartDate->day;
                    $monthCursor = $now->copy()->month($targetMonth)->startOfMonth();
                    $daysInMonth = $monthCursor->daysInMonth;
                    $targetDay = min($dayOfMonth, $daysInMonth);
                    $targetDate = $monthCursor->copy()->day($targetDay);
                    $endOfYear = $now->copy()->endOfYear();

                    if ($targetDate->gte($ruleStartDate) && $targetDate->lte($endOfYear)) {
                        if (self::ensureInstanceExists($rule, $targetDate)) {
                            $wasGenerated = true;
                        }
                    }
                    break;
            }

            if ($wasGenerated) {
                $rule->update(['last_generated_at' => $now]);
            }
        }
    }

    private static function ensureInstanceExists(RecurringTask $rule, Carbon $date): bool
    {
        $dateStr = $date->toDateString();

        // Check if an instance already exists for this rule and date
        $exists = Task::where('recurring_task_id', $rule->id)
            ->whereDate('start_date', $dateStr)
            ->exists();

        if (!$exists) {
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

            return true;
        }

        return false;
    }
}
