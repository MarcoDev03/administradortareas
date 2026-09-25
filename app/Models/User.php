<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'clave',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'Admin';
    }

    public function isDeveloper(): bool
    {
        return $this->role === 'Desarrollador';
    }

    public function isHelper(): bool
    {
        return $this->role === 'Ayudante';
    }

    public function hasFullAccess(): bool
    {
        return $this->isAdmin() || $this->isDeveloper();
    }

    public function memberProjects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_user')->withTimestamps();
    }

    public function createdProjects(): HasMany
    {
        return $this->hasMany(Project::class, 'created_by');
    }

    public function assignedTasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_user')->withTimestamps();
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class, 'user_id');
    }

    public function assignedLinks(): BelongsToMany
    {
        return $this->belongsToMany(ProjectLink::class, 'project_link_user')->withTimestamps();
    }

    public function getInitialsAttribute(): string
    {
        if (!$this->name) return '?';
        $words = preg_split('/\s+/', trim($this->name));
        $initials = '';
        foreach (array_slice($words, 0, 2) as $w) {
            $initials .= mb_substr($w, 0, 1);
        }
        return mb_strtoupper($initials);
    }
}
