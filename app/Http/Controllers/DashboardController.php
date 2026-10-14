<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Module;
use App\Models\Task;
use App\Models\Incident;
use App\Models\User;
use App\Models\ConfigEstilosEstadosPrioridadPeriodicidad;
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
        $activeView = $request->query('view', 'resumen');
        if ($activeView === 'lista_tareas') {
            $activeView = 'checklist';
        }

        // Tareas según rol y proyecto activo (TODAS — para el calendario y como base)
        $tasksQuery = Task::with(['project', 'module', 'assignedUsers', 'subtasks', 'creator', 'recurringTask'])->withCount('comments');

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

        // Usuarios asignados o relacionados con el proyecto activo (miembros del proyecto y usuarios asignados en tareas)
        $projectUsers = collect();
        if ($activeProject) {
            $projectUsers = $activeProject->members
                ->concat($tasks->flatMap(fn ($t) => $t->assignedUsers))
                ->unique('id')
                ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
                ->values();
        }

        $selectedUserId = $request->query('user_id');
        if ($selectedUserId && !$projectUsers->contains('id', (int)$selectedUserId)) {
            $selectedUserId = null;
        }

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

        // Tareas vencidas: pendientes con fecha previa calculadas con la lógica existente isOverdue()
        $overdueTasks = $tasks->filter(fn ($task) => $task->isOverdue())->values();
        $overdueCount = $overdueTasks->count();

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

        // Tareas Periódicas
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

        // -------------------------------------------------------------
        // Papelera de tareas eliminadas (Retención de 30 días)
        // -------------------------------------------------------------
        \App\Services\TaskTrashService::purgeExpiredTasks(30);

        $trashFilter = $request->query('trash_filter', 'todas');

        $trashedBaseQuery = Task::onlyTrashed()
            ->with(['project', 'module', 'assignedUsers', 'subtasks', 'creator'])
            ->whereHas('assignedUsers', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            })
            ->whereIn('project_id', $visibleProjects->pluck('id'))
            ->where('deleted_at', '>=', Carbon::now()->subDays(30));

        $allTrashCount = (clone $trashedBaseQuery)->count();

        $trashedTasksQuery = clone $trashedBaseQuery;

        if ($trashFilter === 'dia' || $trashFilter === 'hoy') {
            $trashedTasksQuery->where('deleted_at', '>=', Carbon::today()->startOfDay());
        } elseif ($trashFilter === 'semana') {
            $trashedTasksQuery->where('deleted_at', '>=', Carbon::now()->startOfWeek());
        } elseif ($trashFilter === 'mes') {
            $trashedTasksQuery->where('deleted_at', '>=', Carbon::now()->startOfMonth());
        }

        $trashedTasks = $trashedTasksQuery->orderBy('deleted_at', 'desc')->get();
        $trashedTasksCount = $allTrashCount;

        // Estados configurados desde la base de datos (colores e iconos)
        $estadosConfig = ConfigEstilosEstadosPrioridadPeriodicidad::where('tipo', 'estado')
            ->orWhereNull('tipo')
            ->orderBy('id')
            ->get();

        $defaultColores = [
            'no_iniciado' => '#64748b',
            'en_proceso'  => '#3b82f6',
            'en_revision' => '#f59e0b',
            'rechazado'   => '#ef4444',
            'finalizado'  => '#10b981',
        ];

        $configColores = $estadosConfig->mapWithKeys(function ($item) {
            $key = $item->clave ?: \Illuminate\Support\Str::slug($item->nombre, '_');
            return [$key => $item->color];
        })->toArray();
        $estadoColores = array_merge($defaultColores, $configColores);

        $estadoIconos = $estadosConfig->mapWithKeys(function ($item) {
            $key = $item->clave ?: \Illuminate\Support\Str::slug($item->nombre, '_');
            return [$key => $item->ruta_icono];
        })->toArray();

        // Prioridades configuradas desde la base de datos
        $prioridadesConfig = ConfigEstilosEstadosPrioridadPeriodicidad::where('tipo', 'prioridad')
            ->orderBy('id')
            ->get();

        $defaultPrioridadColores = [
            'baja'  => '#64748b',
            'media' => '#f59e0b',
            'alta'  => '#ef4444',
        ];

        $prioridadColores = array_merge($defaultPrioridadColores, $prioridadesConfig->mapWithKeys(function ($item) {
            $key = $item->clave ?: \Illuminate\Support\Str::slug($item->nombre, '_');
            return [$key => $item->color];
        })->toArray());

        $prioridadNombres = $prioridadesConfig->mapWithKeys(function ($item) {
            $key = $item->clave ?: \Illuminate\Support\Str::slug($item->nombre, '_');
            return [$key => $item->nombre];
        })->toArray();

        // Periodicidades / Temporalidades configuradas desde la base de datos
        $periodicidadesConfig = ConfigEstilosEstadosPrioridadPeriodicidad::where('tipo', 'periodicidad')
            ->orderBy('id')
            ->get();

        $defaultPeriodicidadColores = [
            'diaria'  => '#06b6d4',
            'semanal' => '#3b82f6',
            'mensual' => '#8b5cf6',
            'anual'   => '#10b981',
        ];

        $periodicidadColores = array_merge($defaultPeriodicidadColores, $periodicidadesConfig->mapWithKeys(function ($item) {
            $key = $item->clave ?: \Illuminate\Support\Str::slug($item->nombre, '_');
            return [$key => $item->color];
        })->toArray());

        $periodicidadNombres = $periodicidadesConfig->mapWithKeys(function ($item) {
            $key = $item->clave ?: \Illuminate\Support\Str::slug($item->nombre, '_');
            return [$key => $item->nombre];
        })->toArray();

        // Matriz mensual de seguimiento con checks (CheckList)
        $matrixMonth = $request->query('month', Carbon::now()->format('Y-m'));
        try {
            $matrixCarbon = Carbon::createFromFormat('Y-m', $matrixMonth)->startOfMonth();
        } catch (\Exception $e) {
            $matrixCarbon = Carbon::now()->startOfMonth();
            $matrixMonth = $matrixCarbon->format('Y-m');
        }

        $matrixProjIds = $activeProject ? [$activeProject->id] : $visibleProjects->pluck('id')->toArray();

        // Sincronizar instancias del mes para la vista CheckList
        if ($activeView === 'checklist') {
            \App\Services\RecurringTaskService::syncRecurringTasks($matrixCarbon, $matrixProjIds);
        }

        $matrixData = $this->buildTaskMatrix(
            $activeProject,
            $visibleProjects,
            $user,
            $selectedUserId,
            $matrixCarbon,
            $activeModuleId
        );

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
            'overdueTasks',
            'overdueCount',
            'taskFilter',
            'modules',
            'incidents',
            'allUsers',
            'recurringTasks',
            'trashedTasks',
            'trashedTasksCount',
            'allTrashCount',
            'trashFilter',
            'estadoColores',
            'estadoIconos',
            'estadosConfig',
            'prioridadesConfig',
            'prioridadColores',
            'prioridadNombres',
            'periodicidadesConfig',
            'periodicidadColores',
            'periodicidadNombres',
            'projectUsers',
            'selectedUserId',
            'matrixMonth',
            'matrixData'
        ));

    }

    /**
     * Construye la matriz mensual de seguimiento de tareas con checks por fecha
     */
    protected function buildTaskMatrix($activeProject, $visibleProjects, $user, $selectedUserId, Carbon $matrixCarbon, $activeModuleId = 'all')
    {
        $daysInMonth = $matrixCarbon->daysInMonth;
        $startOfMonth = $matrixCarbon->copy()->startOfMonth();
        $endOfMonth = $matrixCarbon->copy()->endOfMonth();

        $spanishDaysShort = [
            1 => 'lun',
            2 => 'mar',
            3 => 'mié',
            4 => 'jue',
            5 => 'vie',
            6 => 'sáb',
            7 => 'dom',
        ];

        $days = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $cDate = $matrixCarbon->copy()->day($d);
            $dayOfWeek = $cDate->dayOfWeekIso; // 1 = Monday ... 7 = Sunday
            $weekIndex = (int) ceil($d / 7);

            $days[$d] = [
                'day' => $d,
                'date' => $cDate->format('Y-m-d'),
                'carbon' => $cDate,
                'dayName' => $spanishDaysShort[$dayOfWeek] ?? 'lun',
                'isToday' => $cDate->isToday(),
                'isWeekend' => in_array($dayOfWeek, [6, 7]),
                'weekIndex' => $weekIndex,
            ];
        }

        // Definición de semanas con paleta de colores suaves diferenciados (inspirados en diseñosreferencia)
        $weekThemes = [
            1 => [
                'name' => 'SEMANA 1',
                'color' => '#f43f5e',
                'textColor' => 'text-[#f43f5e] dark:text-[#fb7185]',
                'bgHeader' => 'bg-rose-50/70 dark:bg-rose-950/20',
                'border' => 'border-rose-400 dark:border-rose-500',
                'checkBg' => 'bg-[#f43f5e] text-white border-[#f43f5e] shadow-xs hover:bg-[#e11d48]',
                'checkPending' => 'border-rose-300 dark:border-rose-800 bg-white dark:bg-slate-800/80 hover:border-rose-400',
            ],
            2 => [
                'name' => 'SEMANA 2',
                'color' => '#8b5cf6',
                'textColor' => 'text-[#8b5cf6] dark:text-[#a78bfa]',
                'bgHeader' => 'bg-purple-50/70 dark:bg-purple-950/20',
                'border' => 'border-purple-400 dark:border-purple-500',
                'checkBg' => 'bg-[#8b5cf6] text-white border-[#8b5cf6] shadow-xs hover:bg-[#7c3aed]',
                'checkPending' => 'border-purple-300 dark:border-purple-800 bg-white dark:bg-slate-800/80 hover:border-purple-400',
            ],
            3 => [
                'name' => 'SEMANA 3',
                'color' => '#3b82f6',
                'textColor' => 'text-[#3b82f6] dark:text-[#60a5fa]',
                'bgHeader' => 'bg-blue-50/70 dark:bg-blue-950/20',
                'border' => 'border-blue-400 dark:border-blue-500',
                'checkBg' => 'bg-[#3b82f6] text-white border-[#3b82f6] shadow-xs hover:bg-[#2563eb]',
                'checkPending' => 'border-blue-300 dark:border-blue-800 bg-white dark:bg-slate-800/80 hover:border-blue-400',
            ],
            4 => [
                'name' => 'SEMANA 4',
                'color' => '#f59e0b',
                'textColor' => 'text-[#f59e0b] dark:text-[#fbbf24]',
                'bgHeader' => 'bg-amber-50/70 dark:bg-amber-950/20',
                'border' => 'border-amber-400 dark:border-amber-500',
                'checkBg' => 'bg-[#f59e0b] text-white border-[#f59e0b] shadow-xs hover:bg-[#d97706]',
                'checkPending' => 'border-amber-300 dark:border-amber-800 bg-white dark:bg-slate-800/80 hover:border-amber-400',
            ],
            5 => [
                'name' => 'SEMANA 5',
                'color' => '#10b981',
                'textColor' => 'text-[#10b981] dark:text-[#34d399]',
                'bgHeader' => 'bg-emerald-50/70 dark:bg-emerald-950/20',
                'border' => 'border-emerald-400 dark:border-emerald-500',
                'checkBg' => 'bg-[#10b981] text-white border-[#10b981] shadow-xs hover:bg-[#059669]',
                'checkPending' => 'border-emerald-300 dark:border-emerald-800 bg-white dark:bg-slate-800/80 hover:border-emerald-400',
            ],
        ];

        $weeks = [];
        foreach ($days as $dayInfo) {
            $wIdx = $dayInfo['weekIndex'];
            if (!isset($weeks[$wIdx])) {
                $theme = $weekThemes[$wIdx] ?? $weekThemes[5];
                $weeks[$wIdx] = [
                    'index' => $wIdx,
                    'name' => $theme['name'],
                    'daysCount' => 0,
                    'color' => $theme['color'],
                    'textColor' => $theme['textColor'],
                    'bgHeader' => $theme['bgHeader'],
                    'border' => $theme['border'],
                    'checkBg' => $theme['checkBg'],
                    'checkPending' => $theme['checkPending'],
                ];
            }
            $weeks[$wIdx]['daysCount']++;
        }

        // Proyectos a consultar
        $projIds = $activeProject ? [$activeProject->id] : $visibleProjects->pluck('id')->toArray();

        // 1. Reglas de tareas periódicas
        $rulesQuery = \App\Models\RecurringTask::with(['assignedUsers', 'module', 'project'])
            ->whereIn('project_id', $projIds)
            ->where('is_active', true);

        if ($activeModuleId !== 'all' && !empty($activeModuleId)) {
            $rulesQuery->where('module_id', $activeModuleId);
        }

        if ($user->isHelper()) {
            $rulesQuery->whereHas('assignedUsers', fn($q) => $q->where('users.id', $user->id));
        } elseif ($selectedUserId) {
            $rulesQuery->whereHas('assignedUsers', fn($q) => $q->where('users.id', (int)$selectedUserId));
        }

        $rules = $rulesQuery->orderBy('title')->get();

        // Ocurrencias ya generadas para las tareas periódicas en el mes
        $ruleInstances = \App\Models\Task::whereIn('project_id', $projIds)
            ->whereNotNull('recurring_task_id')
            ->whereBetween('start_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->get();

        // Indexar por regla y fecha para acceso O(1) instantáneo
        $ruleInstancesMap = [];
        foreach ($ruleInstances as $inst) {
            $dStr = $inst->start_date instanceof \DateTimeInterface
                ? $inst->start_date->format('Y-m-d')
                : substr((string)$inst->start_date, 0, 10);
            $ruleInstancesMap[$inst->recurring_task_id . '_' . $dStr] = $inst;
        }

        // 2. Tareas normales / espontáneas (no recurrentes)
        $sMonthStr = $startOfMonth->toDateString();
        $eMonthStr = $endOfMonth->toDateString();

        $standaloneQuery = \App\Models\Task::with(['assignedUsers', 'module', 'project'])
            ->whereIn('project_id', $projIds)
            ->whereNull('recurring_task_id')
            ->where(function ($q) use ($sMonthStr, $eMonthStr) {
                $q->whereBetween('start_date', [$sMonthStr, $eMonthStr])
                  ->orWhere(function ($q2) use ($sMonthStr, $eMonthStr) {
                      $q2->whereNull('start_date')
                         ->whereBetween('end_date', [$sMonthStr, $eMonthStr]);
                  });
            });

        if ($activeModuleId !== 'all' && !empty($activeModuleId)) {
            $standaloneQuery->where('module_id', $activeModuleId);
        }

        if ($user->isHelper()) {
            $standaloneQuery->whereHas('assignedUsers', fn($q) => $q->where('users.id', $user->id));
        } elseif ($selectedUserId) {
            $standaloneQuery->whereHas('assignedUsers', fn($q) => $q->where('users.id', (int)$selectedUserId));
        }

        $standaloneTasks = $standaloneQuery->orderBy('title')->get();

        $rows = [];
        $totalScheduledAll = 0;
        $totalCompletedAll = 0;

        // Procesar Tareas Periódicas
        foreach ($rules as $rule) {
            $ruleStartDate = \Carbon\Carbon::parse($rule->start_date);
            $rowCells = [];
            $rowScheduled = 0;
            $rowCompleted = 0;

            foreach ($days as $d => $dayInfo) {
                $cDate = $dayInfo['carbon'];
                $dateStr = $dayInfo['date'];
                $wIdx = $dayInfo['weekIndex'];
                $isScheduled = false;

                switch ($rule->recurrence) {
                    case 'diaria':
                        $isScheduled = $cDate->gte($ruleStartDate);
                        break;
                    case 'semanal':
                        $isScheduled = $cDate->gte($ruleStartDate) && ($cDate->dayOfWeek === $ruleStartDate->dayOfWeek);
                        break;
                    case 'mensual':
                        $targetDay = min($ruleStartDate->day, $daysInMonth);
                        $isScheduled = $cDate->gte($ruleStartDate) && ($cDate->day === $targetDay);
                        break;
                    case 'anual':
                        $targetDay = min($ruleStartDate->day, $daysInMonth);
                        $isScheduled = $cDate->gte($ruleStartDate) && ($cDate->month === $ruleStartDate->month) && ($cDate->day === $targetDay);
                        break;
                }

                if ($isScheduled) {
                    $key = $rule->id . '_' . $dateStr;
                    $inst = $ruleInstancesMap[$key] ?? null;
                    if (!$inst) {
                        $inst = \App\Services\RecurringTaskService::ensureInstanceExists($rule, $cDate);
                        $ruleInstancesMap[$key] = $inst;
                    }

                    $isDone = ($inst->status === 'finalizado');
                    if ($isDone) {
                        $rowCompleted++;
                        $totalCompletedAll++;
                    }
                    $rowScheduled++;
                    $totalScheduledAll++;

                    $needsReview = (bool) ($inst->needs_review || $rule->needs_review);
                    $isPendingApproval = ($needsReview && $inst->status === 'en_revision');
                    $canApproveReject = ($isPendingApproval && $user->hasFullAccess());
                    $isHelperRestricted = ($isPendingApproval && !$user->hasFullAccess());
                    $todayStr = Carbon::today('America/La_Paz')->toDateString();
                    $isFuture = ($dateStr > $todayStr);

                    $canToggle = true;
                    if ($isFuture) {
                        $canToggle = false;
                    } elseif ($isPendingApproval) {
                        $canToggle = false;
                    } elseif ($isDone && $needsReview && !$user->hasFullAccess()) {
                        $canToggle = false;
                    }

                    $rowCells[$d] = [
                        'scheduled' => true,
                        'task_id' => $inst->id,
                        'is_completed' => $isDone,
                        'status' => $inst->status,
                        'needs_review' => $needsReview,
                        'is_pending_approval' => $isPendingApproval,
                        'can_approve_reject' => $canApproveReject,
                        'is_helper_restricted' => $isHelperRestricted,
                        'is_future' => $isFuture,
                        'date' => $dateStr,
                        'day' => $d,
                        'weekIndex' => $wIdx,
                        'can_toggle' => $canToggle,
                    ];
                } else {
                    $rowCells[$d] = [
                        'scheduled' => false,
                        'day' => $d,
                        'weekIndex' => $wIdx,
                    ];
                }
            }

            $rows[] = [
                'id' => 'rec_' . $rule->id,
                'type' => 'periodica',
                'rule_id' => $rule->id,
                'title' => $rule->title,
                'description' => $rule->description,
                'priority' => $rule->priority,
                'recurrence' => $rule->recurrence,
                'module' => $rule->module,
                'assigned_users' => $rule->assignedUsers,
                'total_scheduled' => $rowScheduled,
                'total_completed' => $rowCompleted,
                'progress_percent' => $rowScheduled > 0 ? round(($rowCompleted / $rowScheduled) * 100) : 0,
                'cells' => $rowCells,
            ];
        }

        // Procesar Tareas Espontáneas (un único check en su fecha correspondiente)
        foreach ($standaloneTasks as $task) {
            $targetDateStr = null;
            if ($task->start_date) {
                $sStr = $task->start_date instanceof \DateTimeInterface
                    ? $task->start_date->format('Y-m-d')
                    : substr((string)$task->start_date, 0, 10);
                if ($sStr >= $sMonthStr && $sStr <= $eMonthStr) {
                    $targetDateStr = $sStr;
                }
            }
            if (!$targetDateStr && $task->end_date) {
                $eStr = $task->end_date instanceof \DateTimeInterface
                    ? $task->end_date->format('Y-m-d')
                    : substr((string)$task->end_date, 0, 10);
                if ($eStr >= $sMonthStr && $eStr <= $eMonthStr) {
                    $targetDateStr = $eStr;
                }
            }

            if (!$targetDateStr) {
                continue;
            }

            $targetDay = (int) substr($targetDateStr, 8, 2);
            $rowCells = [];
            $rowScheduled = 0;
            $rowCompleted = 0;

            foreach ($days as $d => $dayInfo) {
                $dateStr = $dayInfo['date'];
                $wIdx = $dayInfo['weekIndex'];

                if ($d === $targetDay) {
                    $isDone = ($task->status === 'finalizado');
                    if ($isDone) {
                        $rowCompleted++;
                        $totalCompletedAll++;
                    }
                    $rowScheduled++;
                    $totalScheduledAll++;

                    $needsReview = (bool) $task->needs_review;
                    $isPendingApproval = ($needsReview && $task->status === 'en_revision');
                    $canApproveReject = ($isPendingApproval && $user->hasFullAccess());
                    $isHelperRestricted = ($isPendingApproval && !$user->hasFullAccess());
                    $todayStr = Carbon::today('America/La_Paz')->toDateString();
                    $isFuture = ($dateStr > $todayStr);

                    $canToggle = true;
                    if ($isFuture) {
                        $canToggle = false;
                    } elseif ($isPendingApproval) {
                        $canToggle = false;
                    } elseif ($isDone && $needsReview && !$user->hasFullAccess()) {
                        $canToggle = false;
                    }

                    $rowCells[$d] = [
                        'scheduled' => true,
                        'task_id' => $task->id,
                        'is_completed' => $isDone,
                        'status' => $task->status,
                        'needs_review' => $needsReview,
                        'is_pending_approval' => $isPendingApproval,
                        'can_approve_reject' => $canApproveReject,
                        'is_helper_restricted' => $isHelperRestricted,
                        'is_future' => $isFuture,
                        'date' => $dateStr,
                        'day' => $d,
                        'weekIndex' => $wIdx,
                        'can_toggle' => $canToggle,
                    ];
                } else {
                    $rowCells[$d] = [
                        'scheduled' => false,
                        'day' => $d,
                        'weekIndex' => $wIdx,
                    ];
                }
            }

            $rows[] = [
                'id' => 'task_' . $task->id,
                'type' => 'tarea',
                'task_id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'priority' => $task->priority,
                'recurrence' => 'espontanea',
                'module' => $task->module,
                'assigned_users' => $task->assignedUsers,
                'total_scheduled' => $rowScheduled,
                'total_completed' => $rowCompleted,
                'progress_percent' => $rowScheduled > 0 ? round(($rowCompleted / $rowScheduled) * 100) : 0,
                'cells' => $rowCells,
            ];
        }

        $spanishMonths = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];

        return [
            'month' => $matrixCarbon->format('Y-m'),
            'monthNum' => $matrixCarbon->month,
            'year' => $matrixCarbon->year,
            'monthLabel' => ($spanishMonths[$matrixCarbon->month] ?? '') . ' ' . $matrixCarbon->year,
            'prevMonth' => $matrixCarbon->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $matrixCarbon->copy()->addMonth()->format('Y-m'),
            'currentMonth' => \Carbon\Carbon::now()->format('Y-m'),
            'isCurrentMonth' => $matrixCarbon->isCurrentMonth() && $matrixCarbon->isCurrentYear(),
            'days' => $days,
            'weeks' => $weeks,
            'rows' => $rows,
            'totalTasks' => count($rows),
            'totalScheduled' => $totalScheduledAll,
            'totalCompleted' => $totalCompletedAll,
            'overallProgress' => $totalScheduledAll > 0 ? round(($totalCompletedAll / $totalScheduledAll) * 100) : 0,
        ];
    }
}
