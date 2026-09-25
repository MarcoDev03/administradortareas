<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('recurring_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('module_id')->nullable()->constrained('modules')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('priority')->default('media'); // baja, media, alta, urgente
            $table->string('recurrence')->default('diaria'); // diaria, semanal, mensual, anual
            $table->boolean('is_active')->default(true);
            $table->date('start_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->boolean('needs_review')->default(false);
            $table->timestamp('last_generated_at')->nullable();
            $table->timestamps();
        });

        Schema::create('recurring_task_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recurring_task_id')->constrained('recurring_tasks')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('recurring_subtasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recurring_task_id')->constrained('recurring_tasks')->cascadeOnDelete();
            $table->string('title');
            $table->timestamps();
        });

        Schema::table('tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('tasks', 'recurring_task_id')) {
                $table->foreignId('recurring_task_id')->nullable()->after('needs_review')->constrained('recurring_tasks')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (Schema::hasColumn('tasks', 'recurring_task_id')) {
                $table->dropForeign(['recurring_task_id']);
                $table->dropColumn('recurring_task_id');
            }
        });

        Schema::dropIfExists('recurring_subtasks');
        Schema::dropIfExists('recurring_task_user');
        Schema::dropIfExists('recurring_tasks');
    }
};
