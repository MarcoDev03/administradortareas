<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecurringTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'module_id',
        'created_by',
        'title',
        'description',
        'priority',
        'recurrence',
        'is_active',
        'start_date',
        'start_time',
        'end_time',
        'needs_review',
        'last_generated_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'needs_review' => 'boolean',
        'start_date' => 'date:Y-m-d',
        'last_generated_at' => 'datetime',
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

    public function assignedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'recurring_task_user')->withTimestamps();
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(RecurringSubtask::class);
    }

    public function generatedTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'recurring_task_id');
    }
}
