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
        if (Schema::hasTable('incidents') && !Schema::hasColumn('incidents', 'assigned_user_id')) {
            Schema::table('incidents', function (Blueprint $table) {
                $table->foreignId('assigned_user_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            });
        }

        if (!Schema::hasTable('attachments')) {
            Schema::create('attachments', function (Blueprint $table) {
                $table->id();
                $table->morphs('attachable'); // attachable_id, attachable_type
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('original_name');
                $table->string('file_path');
                $table->string('mime_type')->nullable();
                $table->unsignedBigInteger('file_size')->default(0);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('attachments')) {
            Schema::dropIfExists('attachments');
        }

        if (Schema::hasTable('incidents') && Schema::hasColumn('incidents', 'assigned_user_id')) {
            Schema::table('incidents', function (Blueprint $table) {
                $table->dropForeign(['assigned_user_id']);
                $table->dropColumn('assigned_user_id');
            });
        }
    }
};
