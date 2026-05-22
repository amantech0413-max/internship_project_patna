<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('colleges', function (Blueprint $table) {
            $table->string('slug', 120)->nullable()->unique()->after('college_name');
            $table->softDeletes();
        });

        Schema::table('students', function (Blueprint $table) {
            $table->softDeletes();
        });

        if (Schema::hasTable('colleges')) {
            $rows = DB::table('colleges')->whereNull('slug')->get();
            foreach ($rows as $row) {
                $base = Str::slug($row->college_name) ?: 'college';
                $slug = $base;
                $n = 1;
                while (
                    DB::table('colleges')->where('slug', $slug)->where('id', '!=', $row->id)->exists()
                ) {
                    $slug = $base.'-'.$n++;
                }
                DB::table('colleges')->where('id', $row->id)->update(['slug' => $slug]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('colleges', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn('slug');
        });
    }
};
