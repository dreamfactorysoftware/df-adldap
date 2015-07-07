<?php
namespace DreamFactory\Core\ADLdap\Database\Seeds;

use DreamFactory\Core\ADLdap\Models\LDAPConfig;
use DreamFactory\Core\ADLdap\Services\ADLdap;
use DreamFactory\Core\ADLdap\Services\LDAP;
use DreamFactory\Core\Database\Seeds\BaseModelSeeder;
use DreamFactory\Core\Models\ServiceType;

class DatabaseSeeder extends BaseModelSeeder
{
    protected $modelClass = ServiceType::class;

    protected $records = [
        [
            'name'           => 'adldap',
            'class_name'     => ADLdap::class,
            'config_handler' => LDAPConfig::class,
            'label'          => 'adLdap integration',
            'description'    => 'A service for supporting adLdap integration',
            'group'          => 'ldap',
            'singleton'      => false
        ],
        [
            'name'           => 'ldap',
            'class_name'     => LDAP::class,
            'config_handler' => LDAPConfig::class,
            'label'          => 'LDAP integration',
            'description'    => 'A service for supporting OpenLdap integration',
            'group'          => 'ldap',
            'singleton'      => false
        ]
    ];
}