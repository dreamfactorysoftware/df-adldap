<?php
namespace DreamFactory\Core\ADLdap\Models;

use DreamFactory\Core\Components\AppRoleMapper;
use DreamFactory\Core\Components\RequireExtensions;
use DreamFactory\Core\Models\BaseServiceConfigModel;
use DreamFactory\Core\Models\Role;

class LDAPConfig extends BaseServiceConfigModel
{
    use RequireExtensions;
    use AppRoleMapper {
        getConfigSchema as public getConfigSchemaMapper;
    }

    protected $table = 'ldap_config';

    protected $fillable = [
        'service_id',
        'default_role',
        'host',
        'base_dn',
        'account_suffix',
        'map_group_to_role'
    ];

    protected $hidden = ['map_group_hierarchy', 'username', 'password'];

    protected $casts = [
        'service_id' => 'integer',
        'default_role' => 'integer',
        'map_group_to_role'   => 'boolean',
        'map_group_hierarchy'   => 'boolean'
    ];

    protected $rules = [
        'host'    => 'required',
        'base_dn' => 'required'
    ];

    public function validate($data, $throwException = true)
    {
        static::checkExtensions(['ldap']);

        return parent::validate($data, $throwException);
    }

    /**
     * {@inheritdoc}
     */
    public static function getConfigSchema()
    {
        $schema = static::getConfigSchemaMapper();
        $map = array_pop($schema);
        array_splice($schema, 1, 0, [$map]);

        return $schema;
    }

    /**
     * @param array $schema
     */
    protected static function prepareConfigSchemaField(array &$schema)
    {
        parent::prepareConfigSchemaField($schema);

        switch ($schema['name']) {
            case 'default_role':
                $roles = Role::whereIsActive(1)->get();
                $roleList = [];

                foreach ($roles as $role) {
                    $roleList[] = [
                        'label' => $role->name,
                        'name'  => $role->id
                    ];
                }

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
                $schema['description'] = 'Checking this will map your user group to corresponding role.';
                break;
        }
    }
}