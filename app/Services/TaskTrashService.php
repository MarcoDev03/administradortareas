<?php

namespace App\Services;

use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TaskTrashService
{
    /**
     * Purges tasks from trash that have exceeded the 30-day retention period.
     * Permanently deletes them and their relationships (pivot users, subtasks, comments).
     */
    public static function purgeExpiredTasks(int $days = 30): int
    {
        if (!Schema::hasTable('tasks') || !Schema::hasColumn('tasks', 'deleted_at')) {
            return 0;
        }

        $cutoffDate = Carbon::now()->subDays($days);

        $expiredTasks = Task::onlyTrashed()
            ->where('deleted_at', '<', $cutoffDate)
            ->get();

        $count = 0;

        foreach ($expiredTasks as $task) {
            DB::transaction(function () use ($task) {
                $task->assignedUsers()->detach();
                $task->subtasks()->delete();
                $task->comments()->delete();
                $task->forceDelete();
            });
            $count++;
        }

        return $count;
    }
}
