<?php
namespace DreamFactory\Core\ADLdap\Services;

use DreamFactory\Core\ADLdap\Resources\ADComputer;
use DreamFactory\Core\ADLdap\Resources\ADGroup;
use DreamFactory\Core\ADLdap\Resources\ADUser;
use DreamFactory\Library\Utility\ArrayUtils;
use DreamFactory\Core\Exceptions\UnauthorizedException;

class ADLdap extends LDAP
{
    /** Provider name */
    const PROVIDER_NAME = 'adldap';

    /** @type array Service Resources */
    protected $resources = [
        ADComputer::RESOURCE_NAME => [
            'name'       => ADComputer::RESOURCE_NAME,
            'class_name' => ADComputer::class,
            'label'      => 'Computer'
        ],
        ADGroup::RESOURCE_NAME    => [
            'name'       => ADGroup::RESOURCE_NAME,
            'class_name' => ADGroup::class,
            'label'      => 'Group'
        ],
        ADUser::RESOURCE_NAME     => [
            'name'       => ADUser::RESOURCE_NAME,
            'class_name' => ADUser::class,
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