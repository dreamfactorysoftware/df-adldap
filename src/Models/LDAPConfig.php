<?php
namespace DreamFactory\Core\ADLdap\Models;

use DreamFactory\Core\Components\AppRoleMapper;
use DreamFactory\Core\Components\RequireExtensions;
use DreamFactory\Core\Models\BaseServiceConfigModel;
use DreamFactory\Core\Exceptions\BadRequestException;
use DreamFactory\Core\Models\Role;
use DreamFactory\Core\Models\AppRoleMap;

class LDAPConfig extends BaseServiceConfigModel
{
    use RequireExtensions, AppRoleMapper;

    protected $table = 'ldap_config';

    protected $fillable = [
        'service_id',
        'default_role',
        'host',
        'base_dn',
        'account_suffix'
    ];

    protected $hidden = ['map_group_to_role', 'username', 'password'];

    protected $casts = ['service_id' => 'integer', 'default_role' => 'integer'];

    public static function validateConfig($config, $create = true)
    {
        static::checkExtensions(['ldap']);

        $validator = static::makeValidator($config, [
            'host'    => 'required',
            'base_dn' => 'required'
        ], $create);

        if ($validator->fails()) {
            $messages = $validator->messages()->getMessages();
            throw new BadRequestException('Validation failed.', null, null, $messages);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public static function getConfigSchema()
    {
        $schema = parent::getConfigSchema();
        $schema[] = AppRoleMap::getConfigSchema();

        return $schema;
    }

    /**
     * @param array $schema
     */
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
        }
    }
}