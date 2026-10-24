<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use App\Models\RecurringTask;
use App\Services\RecurringTaskService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class SystemDateValidationTest extends TestCase
{
    protected $adminUser;
    protected $testProject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::firstOrCreate(
            ['email' => 'admin_date_test@example.com'],
            ['name' => 'Admin Date Test', 'password' => Hash::make('password123'), 'role' => 'Admin']
        );

        $this->testProject = Project::firstOrCreate(
            ['name' => 'Proyecto Date Test'],
            ['description' => 'Test project description']
        );
        $this->testProject->members()->syncWithoutDetaching([$this->adminUser->id]);
    }

    protected function tearDown(): void
    {
        Task::where('title', 'like', '%DateTest%')->delete();
        RecurringTask::where('title', 'like', '%DateTest%')->delete();
        parent::tearDown();
    }

    public function test_cannot_create_task_with_end_date_before_start_date()
    {
        $response = $this->actingAs($this->adminUser)->post(route('tasks.store'), [
            'project_id' => $this->testProject->id,
            'title' => 'DateTest Invalid End Date',
            'status' => 'no_iniciado',
            'priority' => 'media',
            'start_date' => '2026-10-25',
            'end_date' => '2026-10-20',
        ]);

        $response->assertSessionHasErrors(['end_date']);
        $this->assertDatabaseMissing('tasks', [
            'title' => 'DateTest Invalid End Date',
        ]);
    }

    public function test_cannot_create_task_with_same_date_and_end_time_before_start_time()
    {
        $response = $this->actingAs($this->adminUser)->post(route('tasks.store'), [
            'project_id' => $this->testProject->id,
            'title' => 'DateTest Same Day Invalid Time',
            'status' => 'no_iniciado',
            'priority' => 'media',
            'start_date' => '2026-10-25',
            'start_time' => '14:00',
            'end_date' => '2026-10-25',
            'end_time' => '10:00',
        ]);

        $response->assertSessionHasErrors(['end_date']);
        $this->assertDatabaseMissing('tasks', [
            'title' => 'DateTest Same Day Invalid Time',
        ]);
    }

    public function test_can_create_task_with_same_date_and_valid_times()
    {
        $response = $this->actingAs($this->adminUser)->post(route('tasks.store'), [
            'project_id' => $this->testProject->id,
            'title' => 'DateTest Same Day Valid',
            'status' => 'no_iniciado',
            'priority' => 'media',
            'start_date' => '2026-10-25',
            'start_time' => '10:00',
            'end_date' => '2026-10-25',
            'end_time' => '14:00',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('tasks', [
            'title' => 'DateTest Same Day Valid',
            'start_date' => '2026-10-25 00:00:00',
            'end_date' => '2026-10-25 00:00:00',
        ]);
    }

    public function test_can_create_task_with_past_valid_dates()
    {
        $response = $this->actingAs($this->adminUser)->post(route('tasks.store'), [
            'project_id' => $this->testProject->id,
            'title' => 'DateTest Past Dates Valid',
            'status' => 'no_iniciado',
            'priority' => 'media',
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-05',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('tasks', [
            'title' => 'DateTest Past Dates Valid',
        ]);
    }

    public function test_cannot_update_task_with_invalid_end_date()
    {
        $task = Task::create([
            'project_id' => $this->testProject->id,
            'created_by' => $this->adminUser->id,
            'title' => 'DateTest Update Invalid',
            'status' => 'no_iniciado',
            'priority' => 'media',
            'start_date' => '2026-10-20',
            'end_date' => '2026-10-25',
        ]);

        $response = $this->actingAs($this->adminUser)->put(route('tasks.update', $task), [
            'title' => 'DateTest Update Invalid',
            'status' => 'no_iniciado',
            'priority' => 'media',
            'start_date' => '2026-10-20',
            'end_date' => '2026-10-15',
        ]);

        $response->assertSessionHasErrors(['end_date']);
    }

    public function test_can_remove_end_date_on_task_update()
    {
        $task = Task::create([
            'project_id' => $this->testProject->id,
            'created_by' => $this->adminUser->id,
            'title' => 'DateTest Remove End Date',
            'status' => 'no_iniciado',
            'priority' => 'media',
            'start_date' => '2026-10-20',
            'end_date' => '2026-10-25',
        ]);

        $response = $this->actingAs($this->adminUser)->put(route('tasks.update', $task), [
            'title' => 'DateTest Remove End Date',
            'status' => 'no_iniciado',
            'priority' => 'media',
            'start_date' => '2026-10-20',
            'end_date' => null,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'end_date' => null,
        ]);
    }

    public function test_cannot_create_recurring_task_with_end_time_before_start_time()
    {
        $response = $this->actingAs($this->adminUser)->post(route('recurring-tasks.store'), [
            'project_id' => $this->testProject->id,
            'title' => 'DateTest Rec Invalid Times',
            'priority' => 'media',
            'recurrence' => 'diaria',
            'start_date' => '2026-10-01',
            'start_time' => '17:00',
            'end_time' => '09:00',
        ]);

        $response->assertSessionHasErrors(['end_time']);
        $this->assertDatabaseMissing('recurring_tasks', [
            'title' => 'DateTest Rec Invalid Times',
        ]);
    }

    public function test_can_create_recurring_task_with_valid_times_and_dates()
    {
        $response = $this->actingAs($this->adminUser)->post(route('recurring-tasks.store'), [
            'project_id' => $this->testProject->id,
            'title' => 'DateTest Rec Valid Times',
            'priority' => 'media',
            'recurrence' => 'diaria',
            'start_date' => '2026-10-01',
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('recurring_tasks', [
            'title' => 'DateTest Rec Valid Times',
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);
    }

    public function test_recurring_tasks_generate_instances_without_duplicates()
    {
        $rule = RecurringTask::create([
            'project_id' => $this->testProject->id,
            'created_by' => $this->adminUser->id,
            'title' => 'DateTest Rec Duplicate Check',
            'priority' => 'media',
            'recurrence' => 'diaria',
            'is_active' => true,
            'start_date' => '2026-10-01',
            'start_time' => '10:00',
            'end_time' => '12:00',
        ]);

        $targetDate = Carbon::parse('2026-10-01');

        // First sync
        RecurringTaskService::syncRecurringTasks($targetDate, [$this->testProject->id]);
        $countAfterFirst = Task::where('recurring_task_id', $rule->id)->count();
        $this->assertGreaterThan(0, $countAfterFirst);

        // Second sync: must not create duplicates
        RecurringTaskService::syncRecurringTasks($targetDate, [$this->testProject->id]);
        $countAfterSecond = Task::where('recurring_task_id', $rule->id)->count();
        $this->assertEquals($countAfterFirst, $countAfterSecond);

        // Edit recurring task start date to earlier date
        $response = $this->actingAs($this->adminUser)->put(route('recurring-tasks.update', $rule), [
            'title' => 'DateTest Rec Duplicate Check',
            'priority' => 'media',
            'recurrence' => 'diaria',
            'start_date' => '2026-09-25',
            'start_time' => '10:00',
            'end_time' => '12:00',
        ]);
        $response->assertSessionHasNoErrors();

        // Third sync: check still no duplicates for existing days
        RecurringTaskService::syncRecurringTasks($targetDate, [$this->testProject->id]);
        $countAfterThird = Task::where('recurring_task_id', $rule->id)
            ->whereBetween('start_date', ['2026-10-01', '2026-10-31'])
            ->count();
        $this->assertEquals($countAfterFirst, $countAfterThird);
    }
}
