<?php

namespace DreamFactory\Core\ADLdap\Models;

use DreamFactory\Core\Models\Role;

class ADConfig extends LDAPConfig
{
    /** @type array  */
    protected $fillable = [
        'service_id',
        'default_role',
        'host',
        'base_dn',
        'account_suffix',
        'username',
        'password',
        'map_group_to_role'
    ];

    /** @type array  */
    protected $hidden = [];

    /** @type array  */
    protected $encrypted = ['password'];

    /** @type array  */
    protected $casts = ['service_id' => 'integer', 'default_role' => 'integer', 'map_group_to_role' => 'boolean'];

    /** @inheritdoc */
    protected static function prepareConfigSchemaField(array &$schema)
    {
        $roles = Role::whereIsActive(1)->get();
        $roleList = [];

        foreach ($roles as $role) {
            $roleList[] = [
                'label' => $role->name,
                'name'  => $role->id
            ];
        }

        parent::prepareConfigSchemaField($schema);

        switch ($schema['name']) {
            case 'default_role':
                $schema['type'] = 'picklist';
                $schema['values'] = $roleList;
                $schema['description'] = 'Select a default role for users logging in with this AD/LDAP service type.';
                break;
            case 'host':
                $schema['description'] = 'The host name for your AD/LDAP server.';
                break;
            case 'base_dn':
                $schema['label'] = 'Base DN';
                $schema['description'] = 'The base DN for your domain.';
                break;
            case 'account_suffix':
                $schema['description'] = 'The full account suffix for your domain.';
                break;
            case 'map_group_to_role':
                $schema['description'] = 'Checking this will map your Roles to AD Groups.';
                break;
            case 'username':
                $schema['description'] = '(Optional) Enter AD administrator username to enable additional features.';
                break;
            case 'password':
                $schema['description'] = '(Optional) Enter AD administrator password to enable additional features.';
                break;
        }
    }
}