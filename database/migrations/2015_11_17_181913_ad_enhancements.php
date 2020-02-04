<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AdEnhancements extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('ldap_config', 'map_group_to_role')) {
            Schema::table(
                'ldap_config',
                function (Blueprint $t){
                    $t->boolean('map_group_to_role')->default(0);
                }
            );
        }
        if (!Schema::hasColumn('ldap_config', 'username')) {
            Schema::table(
                'ldap_config',
                function (Blueprint $t){
                    $t->string('username', 50)->nullable()->default(null);
                    $t->longText('password')->nullable()->default(null);
                }
            );
        }
        Schema::create(
            'role_adldap',
            function (Blueprint $t){
                $t->integer('role_id')->unsigned()->primary();
                $t->foreign('role_id')->references('id')->on('role')->onDelete('cascade');
                $t->text('dn');
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('role_adldap');

        if (Schema::hasColumn('ldap_config', 'map_group_to_role')) {
            Schema::table(
                'ldap_config',
                function (Blueprint $t){
                    $t->dropColumn('map_group_to_role');
                }
            );
        }
        if (Schema::hasColumn('ldap_config', 'username')) {
            Schema::table(
                'ldap_config',
                function (Blueprint $t){
                    $t->dropColumn(['username', 'password']);
                }
            );
        }
        Schema::dropIfExists('ldap_config');
    }
}
