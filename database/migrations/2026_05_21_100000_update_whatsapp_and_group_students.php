<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('student_group') && ! Schema::hasTable('group_students')) {
            Schema::rename('student_group', 'group_students');
        }

        if (! Schema::hasTable('group_students')) {
            Schema::create('group_students', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->constrained()->cascadeOnDelete();
                $table->foreignId('internship_group_id')->constrained()->cascadeOnDelete();
                $table->string('assignment_type', 20)->default('manual');
                $table->timestamp('assigned_at')->useCurrent();
                $table->unique(['student_id', 'internship_group_id']);
            });
        }

        if (Schema::hasColumn('internship_groups', 'internship_type') && ! Schema::hasColumn('internship_groups', 'internship_mode')) {
            Schema::table('internship_groups', function (Blueprint $table) {
                $table->string('internship_mode', 20)->default('online')->after('college_name');
            });

            \Illuminate\Support\Facades\DB::table('internship_groups')->update([
                'internship_mode' => \Illuminate\Support\Facades\DB::raw('internship_type'),
            ]);

            Schema::table('internship_groups', function (Blueprint $table) {
                $table->dropColumn('internship_type');
            });
        }

        if (! Schema::hasTable('whatsapp_messages')) {
            Schema::create('whatsapp_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->constrained()->cascadeOnDelete();
                $table->foreignId('internship_group_id')->constrained()->cascadeOnDelete();
                $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete();
                $table->string('mobile', 15);
                $table->text('message_body');
                $table->string('status', 20)->default('pending')->index();
                $table->string('whatsapp_message_id')->nullable();
                $table->text('error_message')->nullable();
                $table->unsignedTinyInteger('retry_count')->default(0);
                $table->unsignedTinyInteger('max_retries')->default(3);
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('next_retry_at')->nullable();
                $table->timestamps();
                $table->index(['internship_group_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');


        if (Schema::hasTable('group_students') && ! Schema::hasTable('student_group')) {
            Schema::rename('group_students', 'student_group');
        }
    }
};
