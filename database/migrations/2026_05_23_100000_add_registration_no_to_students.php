<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('students', 'registration_no')) {
            Schema::table('students', function (Blueprint $table) {
                $table->string('registration_no', 50)->nullable()->unique()->after('student_code');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('students', 'registration_no')) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropUnique(['registration_no']);
                $table->dropColumn('registration_no');
            });
        }
    }
};
