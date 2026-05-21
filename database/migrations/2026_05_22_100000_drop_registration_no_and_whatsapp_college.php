<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('students', 'registration_no')) {
            try {
                Schema::table('students', function (Blueprint $table) {
                    $table->dropUnique(['registration_no']);
                });
            } catch (\Throwable) {
                // Index may already be removed
            }
            Schema::table('students', function (Blueprint $table) {
                $table->dropColumn('registration_no');
            });
        }

        Schema::table('whatsapp_messages', function (Blueprint $table) {
            if (! Schema::hasColumn('whatsapp_messages', 'college_id')) {
                $table->foreignId('college_id')->nullable()->after('student_id')->constrained()->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_messages', function (Blueprint $table) {
            if (Schema::hasColumn('whatsapp_messages', 'college_id')) {
                $table->dropForeign(['college_id']);
                $table->dropColumn('college_id');
            }
        });

        Schema::table('students', function (Blueprint $table) {
            $table->string('registration_no', 50)->nullable()->unique();
        });
    }
};
