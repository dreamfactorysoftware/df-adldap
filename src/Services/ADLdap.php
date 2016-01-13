<?php
namespace DreamFactory\Core\ADLdap\Services;

use DreamFactory\Core\ADLdap\Models\RoleADLdap;
use DreamFactory\Core\ADLdap\Resources\Computer;
use DreamFactory\Core\ADLdap\Resources\Group;
use DreamFactory\Core\ADLdap\Resources\User;
use DreamFactory\Core\Exceptions\BadRequestException;
use DreamFactory\Core\Exceptions\UnauthorizedException;
use DreamFactory\Library\Utility\ArrayUtils;

class ADLdap extends LDAP
{
    /** Provider name */
    const PROVIDER_NAME = 'adldap';

    /** @type array Service Resources */
    protected $resources = [
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
}