<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use App\Models\Incident;
use App\Models\RecurringTask;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class SpecificFixesVerificationTest extends TestCase
{
    protected $adminUser;
    protected $devUser;
    protected $helperUser;
    protected $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::firstOrCreate(
            ['email' => 'admin_fixes@example.com'],
            ['name' => 'Admin Fixes', 'password' => Hash::make('secret'), 'role' => 'Admin']
        );

        $this->devUser = User::firstOrCreate(
            ['email' => 'dev_fixes@example.com'],
            ['name' => 'Dev Fixes', 'password' => Hash::make('secret'), 'role' => 'Desarrollador']
        );

        $this->helperUser = User::firstOrCreate(
            ['email' => 'helper_fixes@example.com'],
            ['name' => 'Helper Fixes', 'password' => Hash::make('secret'), 'role' => 'Ayudante']
        );

        $this->project = Project::firstOrCreate(
            ['name' => 'Proyecto Fixes Test'],
            ['description' => 'Test project']
        );

        $this->project->members()->syncWithoutDetaching([
            $this->adminUser->id,
            $this->devUser->id,
            $this->helperUser->id,
        ]);
    }

    public function test_task_deletion_removes_it_from_active_tasks_and_stats()
    {
        $task = Task::create([
            'project_id' => $this->project->id,
            'created_by' => $this->adminUser->id,
            'title' => 'Tarea a eliminar prueba',
            'status' => 'en_proceso',
            'priority' => 'media',
            'start_date' => Carbon::today()->toDateString(),
        ]);
        $task->assignedUsers()->sync([$this->adminUser->id]);

        $this->actingAs($this->adminUser);

        // Verify task appears on dashboard
        $response1 = $this->get(route('dashboard', ['project_id' => $this->project->id, 'view' => 'resumen', 'user_id' => $this->adminUser->id]));
        $response1->assertStatus(200);
        $tasksBefore = $response1->viewData('tasks');
        $this->assertTrue($tasksBefore->contains('id', $task->id));

        // Delete task
        $delResponse = $this->delete(route('tasks.destroy', $task->id));
        $delResponse->assertRedirect();

        // Verify task is soft deleted
        $this->assertSoftDeleted('tasks', ['id' => $task->id]);

        // Verify task no longer appears in active tasks or stats
        $response2 = $this->get(route('dashboard', ['project_id' => $this->project->id, 'view' => 'resumen', 'user_id' => $this->adminUser->id]));
        $response2->assertStatus(200);
        $tasksAfter = $response2->viewData('tasks');
        $this->assertFalse($tasksAfter->contains('id', $task->id));

        // Clean up
        $task->forceDelete();
    }

    public function test_default_stats_show_logged_in_user_and_can_filter_all()
    {
        $this->actingAs($this->adminUser);

        // Access dashboard without user_id query param
        $responseDefault = $this->get(route('dashboard', ['project_id' => $this->project->id, 'view' => 'resumen']));
        $responseDefault->assertStatus(200);

        // Should default to logged in user ID
        $selectedUserId = $responseDefault->viewData('selectedUserId');
        $this->assertEquals((int)$this->adminUser->id, (int)$selectedUserId);

        // When requesting all users
        $responseAll = $this->get(route('dashboard', ['project_id' => $this->project->id, 'view' => 'resumen', 'user_id' => 'all']));
        $responseAll->assertStatus(200);
        $selectedUserIdAll = $responseAll->viewData('selectedUserId');
        $this->assertEquals('all', $selectedUserIdAll);
    }

    public function test_incident_deletion_for_any_status()
    {
        $this->actingAs($this->adminUser);

        $statuses = ['abierta', 'en_proceso', 'resuelta'];

        foreach ($statuses as $st) {
            $incident = Incident::create([
                'project_id' => $this->project->id,
                'user_id' => $this->adminUser->id,
                'titulo' => "Incidencia test {$st}",
                'descripcion' => "Descripcion {$st}",
                'prioridad' => 'media',
                'estado' => $st,
            ]);

            // AJAX Delete
            $delRes = $this->deleteJson(route('incidents.destroy', $incident->id));
            $delRes->assertStatus(200);
            $delRes->assertJson(['success' => true]);

            $this->assertDatabaseMissing('incidents', ['id' => $incident->id]);
        }

        // Test helper permissions: cannot delete other's incident, can delete own
        $adminIncident = Incident::create([
            'project_id' => $this->project->id,
            'user_id' => $this->adminUser->id,
            'titulo' => 'Admin incident',
            'prioridad' => 'media',
            'estado' => 'abierta',
        ]);

        $this->actingAs($this->helperUser);
        $unauthRes = $this->deleteJson(route('incidents.destroy', $adminIncident->id));
        $unauthRes->assertStatus(403);

        $helperIncident = Incident::create([
            'project_id' => $this->project->id,
            'user_id' => $this->helperUser->id,
            'titulo' => 'Helper incident',
            'prioridad' => 'baja',
            'estado' => 'abierta',
        ]);
        $authRes = $this->deleteJson(route('incidents.destroy', $helperIncident->id));
        $authRes->assertStatus(200);
        $this->assertDatabaseMissing('incidents', ['id' => $helperIncident->id]);

        $adminIncident->delete();
    }

    public function test_empty_trash_permanently_deletes_all_trashed_tasks()
    {
        $this->actingAs($this->adminUser);

        // Create 2 tasks and trash them
        $t1 = Task::create([
            'project_id' => $this->project->id,
            'created_by' => $this->adminUser->id,
            'title' => 'Trash Task 1',
            'status' => 'no_iniciado',
            'priority' => 'baja',
        ]);
        $t2 = Task::create([
            'project_id' => $this->project->id,
            'created_by' => $this->adminUser->id,
            'title' => 'Trash Task 2',
            'status' => 'no_iniciado',
            'priority' => 'baja',
        ]);
        $t1->delete();
        $t2->delete();

        $this->assertSoftDeleted('tasks', ['id' => $t1->id]);
        $this->assertSoftDeleted('tasks', ['id' => $t2->id]);

        // Empty trash
        $res = $this->delete(route('tasks.emptyTrash', ['project_id' => $this->project->id]));
        $res->assertRedirect();

        $this->assertDatabaseMissing('tasks', ['id' => $t1->id]);
        $this->assertDatabaseMissing('tasks', ['id' => $t2->id]);
    }

    public function test_helper_cannot_empty_trash()
    {
        $this->actingAs($this->helperUser);

        $res = $this->delete(route('tasks.emptyTrash'));
        $res->assertStatus(403);
    }
}

