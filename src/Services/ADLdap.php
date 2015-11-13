<?php
namespace DreamFactory\Core\ADLdap\Services;

use DreamFactory\Core\ADLdap\Resources\Computer;
use DreamFactory\Core\ADLdap\Resources\Group;
use DreamFactory\Core\ADLdap\Resources\User;
use DreamFactory\Library\Utility\ArrayUtils;
use DreamFactory\Core\Exceptions\UnauthorizedException;

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
     * @return mixed
     * @throws \DreamFactory\Core\Exceptions\UnauthorizedException
     */
    public function authenticateAdminUser()
    {
        $username = ArrayUtils::get($this->config, 'username');
        $password = ArrayUtils::get($this->config, 'password');

        $auth = $this->driver->authenticate($username, $password);

        if (!$auth) {
            throw new UnauthorizedException('Invalid credentials in service definition. Cannot authenticate against the Active Directory server.');
        }

        return $auth;
    }
}