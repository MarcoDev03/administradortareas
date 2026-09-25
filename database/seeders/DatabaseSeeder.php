<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Project;
use App\Models\Module;
use App\Models\Task;
use App\Models\Subtask;
use App\Models\Incident;
use App\Models\ProjectLink;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Usuarios demo
        $admin = User::create([
            'name' => 'Administrador Principal',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
            'role' => 'Admin',
        ]);

        $dev = User::create([
            'name' => 'Carlos Desarrollador',
            'email' => 'dev@admin.com',
            'password' => Hash::make('password'),
            'role' => 'Desarrollador',
        ]);

        $helper = User::create([
            'name' => 'Ana Ayudante',
            'email' => 'ayudante@admin.com',
            'password' => Hash::make('password'),
            'role' => 'Ayudante',
        ]);

        // 2. Proyecto Demo
        $project = Project::create([
            'name' => 'Gestor de Tareas y Kanban',
            'created_by' => $admin->id,
        ]);

        // Asignar miembros al proyecto
        $project->members()->attach([$admin->id, $dev->id, $helper->id]);

        // 3. Enlaces del proyecto
        $link = ProjectLink::create([
            'project_id' => $project->id,
            'name' => 'Repositorio GitHub',
            'url' => 'https://github.com',
        ]);
        $link->assignedUsers()->attach([$admin->id, $dev->id]);

        // 4. Módulos
        $modFrontend = Module::create([
            'project_id' => $project->id,
            'name' => 'Frontend Blade & UI',
        ]);

        $modBackend = Module::create([
            'project_id' => $project->id,
            'name' => 'Backend & API Laravel',
        ]);

        // 5. Tareas iniciales
        $task1 = Task::create([
            'project_id' => $project->id,
            'module_id' => $modFrontend->id,
            'created_by' => $admin->id,
            'title' => 'Diseñar plantillas Blade adaptativas',
            'description' => 'Convertir el diseño HTML/CSS a vistas Blade modulares utilizando Tailwind CSS.',
            'status' => 'en_proceso',
            'priority' => 'alta',
            'start_date' => now()->format('Y-m-d'),
            'start_time' => '09:00',
            'needs_review' => false,
        ]);
        $task1->assignedUsers()->attach([$dev->id, $helper->id]);

        Subtask::create([
            'task_id' => $task1->id,
            'title' => 'Crear layout principal con menú lateral',
            'status' => 'finalizado',
        ]);
        Subtask::create([
            'task_id' => $task1->id,
            'title' => 'Integrar vista de calendario',
            'status' => 'no_iniciado',
        ]);

        $task2 = Task::create([
            'project_id' => $project->id,
            'module_id' => $modBackend->id,
            'created_by' => $admin->id,
            'title' => 'Implementar autenticación y permisos por rol',
            'description' => 'Configurar controladores para inicio de sesión y restricciones según el rol del usuario.',
            'status' => 'en_revision',
            'priority' => 'urgente',
            'start_date' => now()->format('Y-m-d'),
            'start_time' => '14:00',
            'needs_review' => true,
        ]);
        $task2->assignedUsers()->attach([$dev->id]);

        // 6. Incidencia inicial
        Incident::create([
            'project_id' => $project->id,
            'user_id' => $helper->id,
            'titulo' => 'Ajuste de estilos en móviles',
            'descripcion' => 'Revisar la animación del sidebar en pantallas pequeñas.',
            'prioridad' => 'media',
            'estado' => 'abierta',
        ]);
    }
}
