<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecurringSubtask extends Model
{
    use HasFactory;

    protected $fillable = [
        'recurring_task_id',
        'title',
    ];

    public function recurringTask(): BelongsTo
    {
        return $this->belongsTo(RecurringTask::class);
    }
}
