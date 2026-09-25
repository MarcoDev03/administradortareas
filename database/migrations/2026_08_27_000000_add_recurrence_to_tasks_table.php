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
        Schema::table('tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('tasks', 'recurrence')) {
                $table->string('recurrence')->default('ninguna')->after('needs_review');
            }
            if (!Schema::hasColumn('tasks', 'parent_task_id')) {
                $table->foreignId('parent_task_id')->nullable()->after('recurrence')->constrained('tasks')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (Schema::hasColumn('tasks', 'parent_task_id')) {
                $table->dropForeign(['parent_task_id']);
                $table->dropColumn('parent_task_id');
            }
            if (Schema::hasColumn('tasks', 'recurrence')) {
                $table->dropColumn('recurrence');
            }
        });
    }
};
