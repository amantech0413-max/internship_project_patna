<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('colleges', function (Blueprint $table) {
            $table->id();
            $table->string('college_name');
            $table->text('address')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('mobile_number', 15)->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->timestamps();
        });

        Schema::create('excel_import_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('college_id')->constrained()->cascadeOnDelete();
            $table->foreignId('imported_by')->constrained('users')->cascadeOnDelete();
            $table->string('file_name')->nullable();
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('success_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->unsignedInteger('skipped_count')->default(0);
            $table->json('errors')->nullable();
            $table->timestamps();
        });

        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('college_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->after('college_id')->constrained('users')->nullOnDelete();
        });

        if (Schema::hasColumn('students', 'name')) {
            DB::statement('ALTER TABLE students CHANGE name student_name VARCHAR(255) NOT NULL');
        }

        if (Schema::hasColumn('students', 'mobile')) {
            DB::statement('ALTER TABLE students CHANGE mobile mobile_number VARCHAR(10) NOT NULL');
        }

        DB::statement('ALTER TABLE students MODIFY user_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE students MODIFY student_code VARCHAR(20) NULL');
        DB::statement('ALTER TABLE students MODIFY registration_no VARCHAR(50) NULL');
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['college_id']);
            $table->dropForeign(['created_by']);
            $table->dropColumn(['college_id', 'created_by']);
        });

        if (Schema::hasColumn('students', 'student_name')) {
            DB::statement('ALTER TABLE students CHANGE student_name name VARCHAR(255) NOT NULL');
        }

        if (Schema::hasColumn('students', 'mobile_number')) {
            DB::statement('ALTER TABLE students CHANGE mobile_number mobile VARCHAR(10) NOT NULL');
        }

        Schema::dropIfExists('excel_import_logs');
        Schema::dropIfExists('colleges');
    }
};
