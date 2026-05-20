<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableNames = config('permission.table_names');
        $teamKey = config('permission.column_names.team_foreign_key', 'team_id');

        if (Schema::hasTable($tableNames['roles']) && !Schema::hasColumn($tableNames['roles'], $teamKey)) {
            Schema::table($tableNames['roles'], function (Blueprint $table) use ($teamKey) {
                $table->unsignedBigInteger($teamKey)->nullable()->after('id');
                $table->index($teamKey, 'roles_team_foreign_key_index');
            });
        }

        if (Schema::hasTable($tableNames['model_has_roles']) && !Schema::hasColumn($tableNames['model_has_roles'], $teamKey)) {
            Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($teamKey) {
                $table->unsignedBigInteger($teamKey)->nullable()->after('role_id');
                $table->index($teamKey, 'model_has_roles_team_foreign_key_index');
            });
        }

        if (Schema::hasTable($tableNames['model_has_permissions']) && !Schema::hasColumn($tableNames['model_has_permissions'], $teamKey)) {
            Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($teamKey) {
                $table->unsignedBigInteger($teamKey)->nullable()->after('permission_id');
                $table->index($teamKey, 'model_has_permissions_team_foreign_key_index');
            });
        }
    }

    public function down(): void
    {
        $tableNames = config('permission.table_names');
        $teamKey = config('permission.column_names.team_foreign_key', 'team_id');

        if (Schema::hasTable($tableNames['model_has_permissions']) && Schema::hasColumn($tableNames['model_has_permissions'], $teamKey)) {
            Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($teamKey) {
                $table->dropIndex('model_has_permissions_team_foreign_key_index');
                $table->dropColumn($teamKey);
            });
        }

        if (Schema::hasTable($tableNames['model_has_roles']) && Schema::hasColumn($tableNames['model_has_roles'], $teamKey)) {
            Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($teamKey) {
                $table->dropIndex('model_has_roles_team_foreign_key_index');
                $table->dropColumn($teamKey);
            });
        }

        if (Schema::hasTable($tableNames['roles']) && Schema::hasColumn($tableNames['roles'], $teamKey)) {
            Schema::table($tableNames['roles'], function (Blueprint $table) use ($teamKey) {
                $table->dropIndex('roles_team_foreign_key_index');
                $table->dropColumn($teamKey);
            });
        }
    }
};
