<?php
namespace DreamFactory\Core\ADLdap\Services;

use DreamFactory\Core\ADLdap\Components\ADGroup;
use DreamFactory\Core\ADLdap\Models\RoleADLdap;
use DreamFactory\Core\ADLdap\Resources\Computer;
use DreamFactory\Core\ADLdap\Resources\Group;
use DreamFactory\Core\ADLdap\Resources\User;
use DreamFactory\Core\Exceptions\BadRequestException;
use DreamFactory\Core\Exceptions\UnauthorizedException;
use DreamFactory\Library\Utility\ArrayUtils;
use DreamFactory\Core\Exceptions\InternalServerErrorException;
use DreamFactory\Core\Utility\Session;

class ADLdap extends LDAP
{
    /** Provider name */
    const PROVIDER_NAME = 'adldap';

    /** @type array Service Resources */
    protected static $resources = [
        Computer::RESOURCE_NAME => [
            'name'       => Computer::RESOURCE_NAME,
            'class_name' => Computer::class,
            'label'      => 'Computer'
        ],
        Group::RESOURCE_NAME    => [
            'name'       => Group::RESOURCE_NAME,
            'class_name' => Group::class,
            'label'      => 'Group'
        ],
        User::RESOURCE_NAME     => [
            'name'       => User::RESOURCE_NAME,
            'class_name' => User::class,
            'label'      => 'User'
        ]
    ];

    /**
     * Sets the Active Directory Driver.
     */
    protected function setDriver()
    {
        $host = $this->getHost();
        $baseDn = $this->getBaseDn();
        $accountSuffix = array_get($this->config, 'account_suffix');

        $this->driver = new \DreamFactory\Core\ADLdap\Components\ADLdap($host, $baseDn, $accountSuffix);
        $this->driver->setPageSize(array_get($this->config, 'max_page_size', 1000));
    }

    /**
     * Authenticates the Admin user. Used for utilizing additional
     * features of this service.
     *
     * @param string $username
     * @param string $password
     *
     * @return mixed
     * @throws \DreamFactory\Core\Exceptions\UnauthorizedException
     * @throws BadRequestException
     */
    public function authenticateAdminUser($username = null, $password = null)
    {
        $user = (empty($username)) ? array_get($this->config, 'username') : $username;
        $pwd = (empty($password)) ? array_get($this->config, 'password') : $password;

        if (empty($user) || empty($pwd)) {
            throw new BadRequestException('No username and/or password found in service definition.');
        }

        $auth = $this->driver->authenticate($user, $pwd);

        if (!$auth) {
            if (!empty($username)) {
                throw new UnauthorizedException('Invalid credentials provided. Cannot authenticate against the Active Directory server.');
            } else {
                throw new UnauthorizedException('Invalid credentials in service definition. Cannot authenticate against the Active Directory server.');
            }
        }

        return $auth;
    }

    /** @inheritdoc */
    public function getRole()
    {
        if (array_get($this->config, 'map_group_to_role', false)) {
            $groups = $this->driver->getGroups();
            $primaryGroup = ArrayUtils::findByKeyValue($groups, 'primary', true);
            $role = $this->findRoleByGroup($primaryGroup);

            if (empty($role)) {
                foreach ($groups as $group) {
                    $role = $this->findRoleByGroup($group);
                    if (!empty($role)) {
                        return $role->role_id;
                    }
                }

                return $this->defaultRole;
            }

            return $role->role_id;
        }

        return $this->defaultRole;
    }

    /**
     * Finds a matching role, first with group dn then if not found,
     * finds with parent group's (memberOf) dn. (supporting sub-group).
     *
     * @param array $group
     *
     * @return mixed|null|static
     */
    public function findRoleByGroup(array $group)
    {
        $dn = array_get($group, 'dn');

        if (!empty($dn)) {
            $role = RoleADLdap::whereDn($dn)->first();

            if (empty($role) && array_get($this->config, 'map_group_hierarchy', false)) {
                $memberOf = array_get($group, 'memberof');
                $parentGroups = (is_array($memberOf)) ? $memberOf : [$memberOf];

                foreach ($parentGroups as $parentGroupDn) {
                    if (!empty($parentGroupDn)) {
                        $group = new ADGroup($this->driver->getObjectByDn($parentGroupDn));
                        $role = $this->findRoleByGroup($group->getData());
                        if (!empty($role)) {
                            return $role;
                        }
                    }
                }

                return null;
            }

            return $role;
        }

        return null;
    }

    /** @inheritdoc */
    public static function getApiDocInfo($service)
    {
        $base = parent::getApiDocInfo($service);

        $apis = [];
        $models = [];
        foreach (static::$resources as $resourceInfo) {
            $resourceClass = array_get($resourceInfo, 'class_name');

            if (!class_exists($resourceClass)) {
                throw new InternalServerErrorException('Service configuration class name lookup failed for resource ' .
                    $resourceClass);
            }

            $resourceName = array_get($resourceInfo, static::RESOURCE_IDENTIFIER);
            if (Session::checkForAnyServicePermissions($service->name, $resourceName)) {
                $results = $resourceClass::getApiDocInfo($service->name, $resourceInfo);
                if (isset($results, $results['paths'])) {
                    $apis = array_merge($apis, $results['paths']);
                }
                if (isset($results, $results['definitions'])) {
                    $models = array_merge($models, $results['definitions']);
                }
            }
        }

        $base['paths'] = array_merge($base['paths'], $apis);
        $base['definitions'] = array_merge($base['definitions'], $models);

        return $base;
    }
}