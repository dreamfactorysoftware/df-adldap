<?php
namespace DreamFactory\Core\ADLdap\Database\Seeds;

use DreamFactory\Core\Database\Seeds\BaseModelSeeder;

class DatabaseSeeder extends BaseModelSeeder
{
    protected $modelClass = 'DreamFactory\\Core\\Models\\ServiceType';

    protected $records = [
        [
            'name'           => 'adldap',
            'class_name'     => "DreamFactory\\Core\\ADLdap\\Services\\ADLdap",
            'config_handler' => "DreamFactory\\Core\\ADLdap\\Models\\LDAPConfig",
            'label'          => 'adLdap integration',
            'description'    => 'A service for supporting adLdap integration',
            'group'          => 'ldap',
            'singleton'      => 1
        ],
        [
            'name'           => 'ldap',
            'class_name'     => "DreamFactory\\Core\\ADLdap\\Services\\LDAP",
            'config_handler' => "DreamFactory\\Core\\ADLdap\\Models\\LDAPConfig",
            'label'          => 'LDAP integration',
            'description'    => 'A service for supporting OpenLdap integration',
            'group'          => 'ldap',
            'singleton'      => 1
        ]
    ];
}