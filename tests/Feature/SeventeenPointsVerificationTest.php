<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\Incident;
use App\Models\Attachment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class SeventeenPointsVerificationTest extends TestCase
{
    protected $adminUser;
    protected $developerUser;
    protected $helperUser;
    protected $testProject;

    protected function setUp(): void
    {
        parent::setUp();

        // Create or locate test users
        $this->adminUser = User::firstOrCreate(
            ['email' => 'admin_test_17@example.com'],
            ['name' => 'Admin Test 17', 'password' => Hash::make('password123'), 'role' => 'Admin']
        );

        $this->developerUser = User::firstOrCreate(
            ['email' => 'dev_test_17@example.com'],
            ['name' => 'Dev Test 17', 'password' => Hash::make('password123'), 'role' => 'Desarrollador']
        );

        $this->helperUser = User::firstOrCreate(
            ['email' => 'helper_test_17@example.com'],
            ['name' => 'Helper Test 17', 'password' => Hash::make('password123'), 'role' => 'Ayudante']
        );

        $this->testProject = Project::firstOrCreate(
            ['name' => 'Proyecto Test 17'],
            ['description' => 'Test project description']
        );
        $this->testProject->members()->syncWithoutDetaching([
            $this->adminUser->id,
            $this->developerUser->id,
            $this->helperUser->id
        ]);
    }

    protected function tearDown(): void
    {
        // Clean up test data
        Attachment::where('user_id', $this->adminUser->id)->delete();
        Incident::where('titulo', 'like', '%Test 17%')->delete();
        Task::where('title', 'like', '%Test 17%')->delete();

        parent::tearDown();
    }

    /** Punto 1: Ayudante and Desarrollador cannot access Config; Admin can */
    public function test_point_1_config_access_restricted_to_admin(): void
    {
        // Helper must be forbidden
        $response = $this->actingAs($this->helperUser)->get(route('config.index'));
        $response->assertStatus(403);

        // Developer must also be forbidden
        $response = $this->actingAs($this->developerUser)->get(route('config.index'));
        $response->assertStatus(403);

        // Admin must be allowed
        $response = $this->actingAs($this->adminUser)->get(route('config.index'));
        $response->assertStatus(200);
    }

    /** Punto 2: Developer cannot manage users (create, edit, delete); only Admin can */
    public function test_point_2_developer_cannot_manage_users(): void
    {
        // Developer cannot store user
        $response = $this->actingAs($this->developerUser)->post(route('users.store'), [
            'name' => 'Unauthorized User',
            'email' => 'unauth@example.com',
            'password' => 'password123',
            'role' => 'Ayudante'
        ]);
        $response->assertStatus(403);

        // Developer cannot delete user
        $targetUser = User::create([
            'name' => 'User to delete',
            'email' => 'todelete@example.com',
            'password' => Hash::make('password123'),
            'role' => 'Ayudante'
        ]);

        $response = $this->actingAs($this->developerUser)->delete(route('users.destroy', $targetUser->id));
        $response->assertStatus(403);

        // Admin CAN delete user
        $response = $this->actingAs($this->adminUser)->delete(route('users.destroy', $targetUser->id));
        $response->assertRedirect();
        $this->assertDatabaseMissing('users', ['id' => $targetUser->id]);
    }

    /** Punto 3: Task date validation rejects end_date < start_date */
    public function test_point_3_task_date_validation(): void
    {
        // 1. Invalid: end_date before start_date
        $response = $this->actingAs($this->adminUser)->post(route('tasks.store'), [
            'title' => 'Test 17 Invalid Date Task',
            'project_id' => $this->testProject->id,
            'start_date' => '2026-10-25',
            'start_time' => '14:00',
            'end_date' => '2026-10-24',
            'end_time' => '10:00',
            'status' => 'no_iniciado',
            'priority' => 'media'
        ]);
        $response->assertSessionHasErrors(['end_date']);

        // 2. Valid: end_date >= start_date
        $response = $this->actingAs($this->adminUser)->post(route('tasks.store'), [
            'title' => 'Test 17 Valid Date Task',
            'project_id' => $this->testProject->id,
            'start_date' => '2026-10-25',
            'start_time' => '10:00',
            'end_date' => '2026-10-25',
            'end_time' => '12:00',
            'status' => 'no_iniciado',
            'priority' => 'media'
        ]);
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('tasks', ['title' => 'Test 17 Valid Date Task']);
    }

    /** Punto 5: Kanban status update returns JSON */
    public function test_point_5_kanban_status_update_json(): void
    {
        $task = Task::create([
            'title' => 'Test 17 Kanban Move Task',
            'project_id' => $this->testProject->id,
            'status' => 'no_iniciado',
            'priority' => 'media'
        ]);

        $response = $this->actingAs($this->adminUser)->patchJson("/tasks/{$task->id}/status", [
            'status' => 'en_proceso'
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'status' => 'en_proceso']);
    }

    /** Punto 7 & 8: Comments optimization & editing */
    public function test_point_7_and_8_comments_edit_and_permissions(): void
    {
        $task = Task::create([
            'title' => 'Test 17 Comment Task',
            'project_id' => $this->testProject->id,
            'status' => 'no_iniciado',
            'priority' => 'media'
        ]);

        // Helper creates a comment
        $comment = TaskComment::create([
            'task_id' => $task->id,
            'user_id' => $this->helperUser->id,
            'comment' => 'Original Helper Comment'
        ]);

        // Author (helper) CAN edit
        $response = $this->actingAs($this->helperUser)->patchJson("/comments/{$comment->id}", [
            'comment' => 'Edited by Author Helper'
        ]);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('task_comments', ['id' => $comment->id, 'comment' => 'Edited by Author Helper']);

        // Another user (Developer) CANNOT edit
        $response = $this->actingAs($this->developerUser)->patchJson("/comments/{$comment->id}", [
            'comment' => 'Unauthorized edit attempt'
        ]);
        $response->assertStatus(403);

        // Admin CAN edit
        $response = $this->actingAs($this->adminUser)->patchJson("/comments/{$comment->id}", [
            'comment' => 'Moderated by Admin'
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('task_comments', ['id' => $comment->id, 'comment' => 'Moderated by Admin']);
    }

    /** Punto 9, 10, 11: Incidents without project, assign, edit, delete, status AJAX */
    public function test_points_9_10_11_incidents_lifecycle(): void
    {
        // Create incident with project_id = null
        $response = $this->actingAs($this->adminUser)->post(route('incidents.store'), [
            'titulo' => 'Test 17 General Incident Without Project',
            'descripcion' => 'Incident description without project',
            'prioridad' => 'alta',
            'estado' => 'abierta',
            'project_id' => null,
            'assigned_user_id' => $this->developerUser->id
        ]);
        $response->assertSessionHasNoErrors();

        $incident = Incident::where('titulo', 'Test 17 General Incident Without Project')->first();
        $this->assertNotNull($incident);
        $this->assertNull($incident->project_id);
        $this->assertEquals($this->developerUser->id, $incident->assigned_user_id);

        // Verify it appears in dashboard query
        $dashboardResponse = $this->actingAs($this->adminUser)->get(route('dashboard', ['view' => 'incidencias']));
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('Test 17 General Incident Without Project');

        // Edit incident: change title, assigned user and priority
        $response = $this->actingAs($this->adminUser)->putJson("/incidents/{$incident->id}", [
            'titulo' => 'Test 17 Updated Incident',
            'descripcion' => 'Updated description',
            'prioridad' => 'urgente',
            'estado' => 'en_proceso',
            'project_id' => null,
            'assigned_user_id' => $this->helperUser->id
        ]);
        $response->assertStatus(200);
        $incident->refresh();
        $this->assertEquals('Test 17 Updated Incident', $incident->titulo);
        $this->assertEquals($this->helperUser->id, $incident->assigned_user_id);

        // Change status via AJAX (Punto 11)
        $response = $this->actingAs($this->adminUser)->patchJson("/incidents/{$incident->id}/status", [
            'estado' => 'resuelta'
        ]);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $incident->refresh();
        $this->assertEquals('resuelta', $incident->estado);

        // Delete incident (Punto 10)
        $response = $this->actingAs($this->adminUser)->deleteJson("/incidents/{$incident->id}");
        $response->assertStatus(200);
        $this->assertDatabaseMissing('incidents', ['id' => $incident->id]);
    }

    /** Punto 17: File attachments to tasks & incidents (upload, list, download, delete) */
    public function test_point_17_attachments_feature(): void
    {
        Storage::fake('local');

        $task = Task::create([
            'title' => 'Test 17 Attachment Task',
            'project_id' => $this->testProject->id,
            'status' => 'no_iniciado',
            'priority' => 'media'
        ]);

        $fakeFile = UploadedFile::fake()->create('documento_test.pdf', 150, 'application/pdf');

        // 1. Upload attachment
        $response = $this->actingAs($this->adminUser)->postJson(route('attachments.store'), [
            'attachable_type' => 'Task',
            'attachable_id' => $task->id,
            'file' => $fakeFile
        ]);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $attachmentId = $response->json('attachment.id');

        $this->assertDatabaseHas('attachments', [
            'id' => $attachmentId,
            'original_name' => 'documento_test.pdf',
            'attachable_type' => Task::class,
            'attachable_id' => $task->id
        ]);

        // 2. List attachments
        $response = $this->actingAs($this->adminUser)->getJson(route('attachments.index', [
            'attachable_type' => 'Task',
            'attachable_id' => $task->id
        ]));
        $response->assertStatus(200);
        $response->assertJsonFragment(['original_name' => 'documento_test.pdf']);

        // 3. Download attachment
        $response = $this->actingAs($this->adminUser)->get(route('attachments.download', $attachmentId));
        $response->assertStatus(200);

        // 4. Delete attachment
        $response = $this->actingAs($this->adminUser)->deleteJson(route('attachments.destroy', $attachmentId));
        $response->assertStatus(200);
        $this->assertDatabaseMissing('attachments', ['id' => $attachmentId]);
    }
}
