<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ACP (Agent Control Plane): maps a DF user to the directory department
 * (LDAP departmentNumber / AD department) captured at login, so AI usage
 * can be charged back by cost-center. Deliberately a thin side table, NOT
 * a column on the core user record — the directory owns this attribute and
 * we only cache the last-seen value. See df-adldap LDAP::handleLogin.
 */
class CreateUserDepartmentTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('user_department')) {
            return; // ponytail: table may pre-exist from the live rollout; no-op
        }
        Schema::create('user_department', function (Blueprint $table) {
            $table->unsignedInteger('user_id')->primary();
            $table->string('department', 128)->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_department');
    }
}
