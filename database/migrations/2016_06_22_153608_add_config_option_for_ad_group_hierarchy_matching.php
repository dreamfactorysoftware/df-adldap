<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddConfigOptionForAdGroupHierarchyMatching extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('ldap_config', 'map_group_hierarchy')) {
            Schema::table(
                'ldap_config',
                function (Blueprint $t){
                    $t->boolean('map_group_hierarchy')->after('map_group_to_role')->default(0);
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
        if (Schema::hasColumn('ldap_config', 'map_group_hierarchy')) {
            Schema::table(
                'ldap_config',
                function (Blueprint $t){
                    $t->dropColumn('map_group_hierarchy');
                }
            );
        }
    }
}
