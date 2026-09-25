<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Module;
use App\Models\Task;
use App\Models\Incident;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Autogenerar instancias de tareas periódicas/recurrentes del mes actual al conectarse
        \App\Services\RecurringTaskService::syncRecurringTasks();

        // Proyectos a los que pertenece el usuario autenticado
        $visibleProjects = $user->memberProjects()->with(['creator', 'members', 'links.assignedUsers'])->get();

        // Proyecto activo
        $activeProjectId = $request->query('project_id');
        $activeProject = null;

        if ($activeProjectId) {
            $activeProject = $visibleProjects->firstWhere('id', $activeProjectId);
        }

        if (!$activeProject && $visibleProjects->count() > 0) {
            $activeProject = $visibleProjects->first();
        }

        // Módulo activo
        $activeModuleId = $request->query('module_id', 'all');

        // Vista activa
        $activeView = $request->query('view', 'calendario');

        // Tareas según rol y proyecto activo (TODAS — para el calendario y como base)
        $tasksQuery = Task::with(['project', 'module', 'assignedUsers', 'subtasks', 'creator'])->withCount('comments');

        if ($activeProject) {
            $tasksQuery->where('project_id', $activeProject->id);
        } else {
            $tasksQuery->whereIn('project_id', $visibleProjects->pluck('id'));
        }

        if ($activeModuleId !== 'all' && !empty($activeModuleId)) {
            $tasksQuery->where('module_id', $activeModuleId);
        }

        // Si el rol es Ayudante, solo ve sus tareas asignadas
        if ($user->isHelper()) {
            $tasksQuery->whereHas('assignedUsers', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        }

        $tasks = $tasksQuery->orderBy('created_at', 'desc')->get();

        $today = Carbon::today();
        $thisMonth = Carbon::now()->month;
        $thisYear = Carbon::now()->year;
        $taskFilter = $request->query('task_filter', 'hoy');

        // Tareas de hoy: programadas para hoy, completadas hoy, o pendientes vencidas
        $todayTasks = $tasks->filter(function ($task) use ($today) {
            // 1. Tareas finalizadas hoy
            if ($task->status === 'finalizado') {
                return $task->completed_at && Carbon::parse($task->completed_at)->isToday();
            }

            // 2. Tareas vencidas (pendientes con fecha de inicio o fin previa a hoy)
            if ($task->isOverdue()) {
                return true;
            }

            // 3. Tareas programadas para hoy (start_date o end_date es hoy)
            $startDateToday = $task->start_date && Carbon::parse($task->start_date)->isToday();
            $endDateToday = $task->end_date && Carbon::parse($task->end_date)->isToday();

            if ($startDateToday || $endDateToday) {
                return true;
            }

            // 4. Tareas sin fecha explícita creadas hoy
            if (!$task->start_date && !$task->end_date && $task->created_at && $task->created_at->isToday()) {
                return true;
            }

            return false;
        })->values();

        // Tareas del mes presente: tareas agendadas, finalizadas o creadas (sin fecha definida) en el mes actual
        $monthTasks = $tasks->filter(function ($task) use ($thisMonth, $thisYear) {
            $hasExplicitDates = $task->start_date || $task->end_date;

            if ($hasExplicitDates) {
                $inStart = $task->start_date && Carbon::parse($task->start_date)->month === $thisMonth && Carbon::parse($task->start_date)->year === $thisYear;
                $inEnd = $task->end_date && Carbon::parse($task->end_date)->month === $thisMonth && Carbon::parse($task->end_date)->year === $thisYear;
                $inCompleted = $task->completed_at && $task->completed_at->month === $thisMonth && $task->completed_at->year === $thisYear;

                return $inStart || $inEnd || $inCompleted;
            } else {
                $inCreated = $task->created_at && $task->created_at->month === $thisMonth && $task->created_at->year === $thisYear;
                $inCompleted = $task->completed_at && $task->completed_at->month === $thisMonth && $task->completed_at->year === $thisYear;

                return $inCreated || $inCompleted;
            }
        })->values();

        $filteredTasks = $taskFilter === 'mes' ? $monthTasks : $todayTasks;

        // Módulos del proyecto activo (o de todos los proyectos visibles)
        $modules = $activeProject
            ? $activeProject->modules
            : Module::whereIn('project_id', $visibleProjects->pluck('id'))->get();

        // Incidencias del proyecto activo o visibles
        $incidentsQuery = Incident::with(['project', 'reporter']);
        if ($activeProject) {
            $incidentsQuery->where('project_id', $activeProject->id);
        } else {
            $incidentsQuery->whereIn('project_id', $visibleProjects->pluck('id'));
        }
        $incidents = $incidentsQuery->orderBy('created_at', 'desc')->get();

        // Todos los usuarios (para modales de asignación y miembros)
        $allUsers = User::all();

        // Reglas de Tareas Periódicas
        $recurringTasksQuery = \App\Models\RecurringTask::with(['project', 'module', 'assignedUsers', 'subtasks', 'creator']);
        if ($activeProject) {
            $recurringTasksQuery->where('project_id', $activeProject->id);
        } else {
            $recurringTasksQuery->whereIn('project_id', $visibleProjects->pluck('id'));
        }
        if ($activeModuleId !== 'all' && !empty($activeModuleId)) {
            $recurringTasksQuery->where('module_id', $activeModuleId);
        }
        if ($user->isHelper()) {
            $recurringTasksQuery->whereHas('assignedUsers', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        }
        $recurringTasks = $recurringTasksQuery->orderBy('created_at', 'desc')->get();

        return view('dashboard.index', compact(
            'user',
            'visibleProjects',
            'activeProject',
            'activeModuleId',
            'activeView',
            'tasks',
            'filteredTasks',
            'todayTasks',
            'monthTasks',
            'taskFilter',
            'modules',
            'incidents',
            'allUsers',
            'recurringTasks'
        ));

    }
}
