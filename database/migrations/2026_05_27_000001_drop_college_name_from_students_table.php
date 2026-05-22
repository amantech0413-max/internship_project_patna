<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('students', 'college_name')) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropColumn('college_name');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('students', 'college_name')) {
            Schema::table('students', function (Blueprint $table) {
                $table->string('college_name')->nullable()->index()->after('college_roll_no');
            });
        }
    }
};
