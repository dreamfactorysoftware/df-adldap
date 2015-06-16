<?php
namespace DreamFactory\Core\ADLdap\Services;

use DreamFactory\Core\ADLdap\Components\OpenLdap;
use DreamFactory\Core\Exceptions\UnauthorizedException;
use DreamFactory\Core\Models\User;
use DreamFactory\Core\Services\BaseRestService;
use DreamFactory\Library\Utility\ArrayUtils;
use DreamFactory\Library\Utility\Enums\Verbs;
use DreamFactory\Core\ADLdap\Contracts\Provider as ADLdapProvider;
use DreamFactory\Core\Exceptions\NotFoundException;
use DreamFactory\Core\Utility\Session;

class LDAP extends BaseRestService
{
    /** Provider name */
    const PROVIDER_NAME = 'ldap';

    /** @var mixed */
    protected $defaultRole;

    /** @var mixed */
    protected $config;

    /** @var  ADLdapProvider */
    protected $driver;

    /**
     * @param array $settings
     */
    public function __construct($settings = [])
    {
        $verbAliases = [
            Verbs::PUT   => Verbs::POST,
            Verbs::MERGE => Verbs::PATCH
        ];
        ArrayUtils::set($settings, "verbAliases", $verbAliases);
        parent::__construct($settings);

        $this->config = ArrayUtils::get($settings, 'config');
        $this->defaultRole = ArrayUtils::get($this->config, 'default_role');
        $this->setDriver();
    }

    /**
     * Sets the Ldap driver.
     */
    protected function setDriver()
    {
        $host = $this->getHost();
        $baseDn = $this->getBaseDn();

        $this->driver = new OpenLdap($host, $baseDn);
    }

    /**
     * Returns the Ldap driver.
     *
     * @return ADLdapProvider
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * @return array|null
     */
    public function getDefaultRole()
    {
        return $this->defaultRole;
    }

    /**
     * @return string
     */
    public function getProviderName()
    {
        return static::PROVIDER_NAME;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getHost()
    {
        return ArrayUtils::get($this->config, 'host');
    }

    /**
     * @return string|null
     */
    public function getBaseDn()
    {
        return ArrayUtils::get($this->config, 'base_dn');
    }

    /**
     * Gets basic user session data.
     *
     * @return array
     * @throws NotFoundException
     */
    protected function handleGET()
    {
        return Session::getPublicInfo();
    }

    /**
     * Handles the POST request on this service.
     *
     * @return array|bool
     * @throws UnauthorizedException
     * @throws \DreamFactory\Core\Exceptions\BadRequestException
     * @throws \DreamFactory\Core\Exceptions\NotFoundException
     */
    protected function handlePOST()
    {
        if ('session' === $this->resource) {
            $username = $this->getPayloadData('username');
            $password = $this->getPayloadData('password');

            $auth = $this->driver->authenticate($username, $password);

            if ($auth) {
                $ldapUser = $this->driver->getUser();

                $user = User::createShadowADLdapUser($ldapUser, $this);

                \Auth::login($user);

                return Session::getPublicInfo();
            } else {
                throw new UnauthorizedException('Invalid username and password provided.');
            }
        }

        return false;
    }

    /**
     * Logs out user
     *
     * @return array
     */
    protected function handleDELETE()
    {
        \Auth::logout();

        return ['success' => true];
    }
}