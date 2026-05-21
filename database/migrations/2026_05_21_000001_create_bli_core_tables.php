<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otp_verifications', function (Blueprint $table) {
            $table->id();
            $table->string('identifier')->index();
            $table->string('otp', 6);
            $table->string('purpose', 50)->default('password_reset');
            $table->timestamp('expires_at');
            $table->boolean('is_used')->default(false);
            $table->timestamps();
        });

        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('student_code', 20)->unique();
            $table->string('registration_no', 50)->unique();
            $table->string('name');
            $table->string('father_name')->nullable();
            $table->string('university_roll_no', 50)->nullable();
            $table->string('college_roll_no', 50)->nullable();
            $table->string('college_name')->nullable()->index();
            $table->string('subject')->nullable()->index();
            $table->string('semester', 20)->nullable()->index();
            $table->string('mobile', 10)->index();
            $table->string('email')->nullable();
            $table->string('internship_mode', 20)->default('online');
            $table->text('address')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('id_proof_path')->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->boolean('profile_completed')->default(false);
            $table->foreignId('college_coordinator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('internship_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('semester', 20)->nullable()->index();
            $table->string('subject')->nullable();
            $table->string('college_name')->nullable()->index();
            $table->string('internship_mode', 20)->default('online');
            $table->string('whatsapp_group_link')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('group_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('internship_group_id')->constrained()->cascadeOnDelete();
            $table->string('assignment_type', 20)->default('manual');
            $table->timestamp('assigned_at')->useCurrent();
            $table->unique(['student_id', 'internship_group_id']);
        });

        Schema::create('daily_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->date('report_date')->index();
            $table->text('work_summary');
            $table->text('learnings')->nullable();
            $table->string('status', 20)->default('submitted');
            $table->text('admin_remarks')->nullable();
            $table->timestamps();
            $table->unique(['student_id', 'report_date']);
        });

        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('internship_group_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path')->nullable();
            $table->string('status', 20)->default('submitted');
            $table->text('feedback')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('file_path');
            $table->string('certificate_no')->unique();
            $table->date('issued_date')->nullable();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('auto_generated')->default(false);
            $table->timestamps();
        });

        Schema::create('offer_letters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('file_path');
            $table->string('letter_no')->unique();
            $table->date('issued_date')->nullable();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('auto_generated')->default(false);
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('internship_group_id')->nullable()->constrained()->nullOnDelete();
            $table->date('attendance_date')->index();
            $table->timestamp('checked_in_at')->nullable();
            $table->string('method', 20)->default('qr');
            $table->string('qr_token')->nullable();
            $table->boolean('is_present')->default(true);
            $table->timestamps();
            $table->unique(['student_id', 'attendance_date']);
        });

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

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('attendance_records');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('offer_letters');
        Schema::dropIfExists('certificates');
        Schema::dropIfExists('assignments');
        Schema::dropIfExists('daily_reports');
        Schema::dropIfExists('group_students');
        Schema::dropIfExists('internship_groups');
        Schema::dropIfExists('students');
        Schema::dropIfExists('otp_verifications');
    }
};
