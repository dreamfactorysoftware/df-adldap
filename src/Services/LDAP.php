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
use DreamFactory\Core\ADLdap\Contracts\User as LdapUserContract;


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
                $user = $this->createShadowADLdapUser($ldapUser);
                //\Auth::login($user);
                Session::setUserInfoWithJWT($user);
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

    /**
     * If does not exists, creates a shadow LDap user using user info provided
     * by the Ldap service provider and assigns default role to this user
     * for all apps in the system. If user already exists then updates user's
     * role for all apps and returns it.
     *
     * @param LdapUserContract $ldapUser
     *
     * @return User
     * @throws \Exception
     */
    public function createShadowADLdapUser(LdapUserContract $ldapUser)
    {
        $email = $ldapUser->getEmail();
        $serviceName = $this->getName();

        if (empty($email)) {
            $uid = $ldapUser->getUid();
            if (empty($uid)) {
                $uid = str_replace(' ', '', $ldapUser->getName());
            }
            $domain = $ldapUser->getDomain();
            $email = $uid . '+' . $serviceName . '@' . $domain;
        } else {
            list($emailId, $domain) = explode('@', $email);
            $email = $emailId . '+' . $serviceName . '@' . $domain;
        }

        $user = User::whereEmail($email)->first();

        if (empty($user)) {
            $data = [
                'name'       => $ldapUser->getName(),
                'first_name' => $ldapUser->getFirstName(),
                'last_name'  => $ldapUser->getLastName(),
                'email'      => $email,
                'is_active'  => 1,
                'adldap'     => $this->getProviderName(),
                'password'   => $ldapUser->getPassword()
            ];

            $user = User::create($data);
        }

        $defaultRole = $this->getDefaultRole();

        User::applyDefaultUserAppRole($user, $defaultRole);

        return $user;
    }
}