<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateLdapConfigTable extends Migration
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
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
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
                    $t->dropColumn('username');
                    $t->dropColumn('password');
                }
            );
        }
    }
}
