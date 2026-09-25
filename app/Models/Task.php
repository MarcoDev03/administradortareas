<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'module_id',
        'created_by',
        'title',
        'description',
        'status',
        'priority',
        'start_date',
        'start_time',
        'end_date',
        'end_time',
        'needs_review',
        'recurrence',
        'parent_task_id',
        'recurring_task_id',
        'completed_at',
    ];

    protected $casts = [
        'needs_review' => 'boolean',
        'start_date' => 'date:Y-m-d',
        'end_date' => 'date:Y-m-d',
        'completed_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recurringTask(): BelongsTo
    {
        return $this->belongsTo(RecurringTask::class, 'recurring_task_id');
    }

    public function parentTask(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_task_id');
    }

    public function childTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'parent_task_id');
    }

    public function assignedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_user')->withTimestamps();
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(Subtask::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class)->latest();
    }


    public function isOverdue(): bool
    {
        if ($this->status === 'finalizado') {
            return false;
        }

        $now = Carbon::now();
        $today = Carbon::today();

        if ($this->end_date) {
            $endDate = Carbon::parse($this->end_date);
            if ($endDate->lt($today)) {
                return true;
            }
            if ($endDate->isToday() && $this->end_time) {
                $endTime = Carbon::parse($this->end_date->format('Y-m-d') . ' ' . $this->end_time);
                if ($now->gt($endTime)) {
                    return true;
                }
            }
        } elseif ($this->start_date) {
            $startDate = Carbon::parse($this->start_date);
            if ($startDate->lt($today)) {
                return true;
            }
            if ($startDate->isToday() && $this->start_time) {
                $startTime = Carbon::parse($this->start_date->format('Y-m-d') . ' ' . $this->start_time);
                if ($now->gt($startTime)) {
                    return true;
                }
            }
        }

        return false;
    }
}
