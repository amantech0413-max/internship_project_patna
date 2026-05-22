<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('whatsapp_messages', function (Blueprint $table) {
            if (! Schema::hasColumn('whatsapp_messages', 'bulk_student_id')) {
                $table->foreignId('bulk_student_id')
                    ->nullable()
                    ->after('student_id')
                    ->constrained('bulk_students')
                    ->nullOnDelete();
            }
        });

        if (Schema::hasColumn('whatsapp_messages', 'student_id')) {
            Schema::table('whatsapp_messages', function (Blueprint $table) {
                $table->unsignedBigInteger('student_id')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        Schema::table('whatsapp_messages', function (Blueprint $table) {
            if (Schema::hasColumn('whatsapp_messages', 'bulk_student_id')) {
                $table->dropForeign(['bulk_student_id']);
                $table->dropColumn('bulk_student_id');
            }
        });
    }
};
