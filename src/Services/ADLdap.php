<?php
namespace DreamFactory\Core\ADLdap\Services;

use DreamFactory\Core\ADLdap\Models\RoleADLdap;
use DreamFactory\Core\ADLdap\Resources\Group;
use DreamFactory\Core\ADLdap\Resources\User;
use DreamFactory\Core\Exceptions\BadRequestException;
use DreamFactory\Library\Utility\ArrayUtils;
use DreamFactory\Core\Exceptions\UnauthorizedException;
use DreamFactory\Core\Exceptions\InternalServerErrorException;
use DreamFactory\Core\Resources\BaseRestResource;

class ADLdap extends LDAP
{
    /** Provider name */
    const PROVIDER_NAME = 'adldap';

    /** @type array Service Resources */
    protected $resources = [
        Group::RESOURCE_NAME => [
            'name'       => Group::RESOURCE_NAME,
            'class_name' => Group::class,
            'label'      => 'Group'
        ],
        User::RESOURCE_NAME  => [
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
        $accountSuffix = ArrayUtils::get($this->config, 'account_suffix');

        $this->driver = new \DreamFactory\Core\ADLdap\Components\ADLdap($host, $baseDn, $accountSuffix);
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
        $user = (empty($username)) ? ArrayUtils::get($this->config, 'username') : $username;
        $pwd = (empty($password)) ? ArrayUtils::get($this->config, 'password') : $password;

        if (empty($user) || empty($pwd)) {
            throw new BadRequestException('No username and/or password provided in service definition.');
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
        if (ArrayUtils::get($this->config, 'map_group_to_role', false)) {
            $groups = $this->driver->getGroups();
            $primaryGroupDn = ArrayUtils::findByKeyValue($groups, 'primary', true, 'dn');
            $role = RoleADLdap::whereDn($primaryGroupDn)->first();

            if (empty($role)) {
                foreach ($groups as $group) {
                    $groupDn = ArrayUtils::get($group, 'dn');
                    $role = RoleADLdap::whereDn($groupDn)->first();
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
     * {@inheritdoc}
     */
    public function getApiDocInfo()
    {
        $base = parent::getApiDocInfo();

        $apis = [];
        $models = [];

        foreach ($this->getResources(true) as $resourceInfo) {
            $className = ArrayUtils::get($resourceInfo, 'class_name');

            if (!class_exists($className)) {
                throw new InternalServerErrorException('Service configuration class name lookup failed for resource ' .
                    $this->resourcePath);
            }

            /** @var BaseRestResource $resource */
            $resource = $this->instantiateResource($className, $resourceInfo);

            $name = ArrayUtils::get($resourceInfo, 'name', '') . '/';
            $access = $this->getPermissions($name);
            if (!empty($access)) {
                $results = $resource->getApiDocInfo();
                if (isset($results, $results['apis'])) {
                    $apis = array_merge($apis, $results['apis']);
                }
                if (isset($results, $results['models'])) {
                    $models = array_merge($models, $results['models']);
                }
            }
        }

        $base['apis'] = array_merge($base['apis'], $apis);
        $base['models'] = array_merge($base['models'], $models);

        return $base;
    }
}