<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLdapUsernameFiledToUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('user', 'ldap_username')) {
            Schema::table(
                'user',
                function (Blueprint $t){
                    $t->string('ldap_username')->nullable()->after('username');
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
        if (Schema::hasColumn('user', 'ldap_username')) {
            Schema::table(
                'user',
                function (Blueprint $t){
                    $t->dropColumn('ldap_username');
                }
            );
        }
    }
}
