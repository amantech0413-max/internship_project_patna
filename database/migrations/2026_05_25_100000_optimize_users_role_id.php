<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'assigned_role_id') && ! Schema::hasColumn('users', 'role_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->renameColumn('assigned_role_id', 'role_id');
            });
        }

        $this->migrateEnumRolesToRoleId();

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'permissions')) {
                $table->dropColumn('permissions');
            }
        });

        if (Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex(['role']);
                $table->dropColumn('role');
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 30)->default('student')->index();
            $table->json('permissions')->nullable();
        });

        if (Schema::hasColumn('users', 'role_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropConstrainedForeignId('role_id');
            });
        }
    }

    protected function migrateEnumRolesToRoleId(): void
    {
        if (! Schema::hasTable('roles') || ! Schema::hasColumn('users', 'role')) {
            return;
        }

        $map = DB::table('roles')->pluck('id', 'slug');

        $users = DB::table('users')->get();

        foreach ($users as $user) {
            $roleId = $user->role_id ?? null;

            if ($roleId) {
                continue;
            }

            $enum = $user->role ?? 'student';

            if ($enum === 'college_coordinator') {
                $roleId = $map['college-coordinator-role'] ?? $map['entry-operator'] ?? null;
            } else {
                $roleId = $map[$enum] ?? $map['student'] ?? null;
            }

            if ($roleId) {
                DB::table('users')->where('id', $user->id)->update(['role_id' => $roleId]);
            }
        }
    }
};
