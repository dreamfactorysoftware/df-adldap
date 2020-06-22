<?php

namespace DreamFactory\Core\ADLdap\Services;

use DreamFactory\Core\ADLdap\Components\OpenLdap;
use DreamFactory\Core\ADLdap\Models\RoleADLdap;
use DreamFactory\Core\Components\RequireExtensions;
use DreamFactory\Core\Exceptions\UnauthorizedException;
use DreamFactory\Core\Models\User;
use DreamFactory\Core\Services\BaseRestService;
use DreamFactory\Core\Enums\Verbs;
use DreamFactory\Core\ADLdap\Contracts\Provider as ADLdapProvider;
use DreamFactory\Core\Utility\Session;
use DreamFactory\Core\ADLdap\Contracts\User as LdapUserContract;
use Carbon\Carbon;

class LDAP extends BaseRestService
{
    use RequireExtensions;

    /** Provider name */
    const PROVIDER_NAME = 'ldap';

    /** @var mixed */
    protected $defaultRole;

    /** @var mixed */
    protected $config;

    /** @var  ADLdapProvider */
    protected $driver;

    /** @type array Service Resources */
    protected static $resources = [];

    /**
     * @param array $settings
     */
    public function __construct($settings = [])
    {
        $settings = (array)$settings;
        $settings['verbAliases'] = [
            Verbs::PUT => Verbs::POST,
        ];
        parent::__construct($settings);

        static::checkExtensions(['ldap']);

        $this->config = array_get($settings, 'config');
        $this->defaultRole = array_get($this->config, 'default_role');
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
        $this->driver->setPageSize(array_get($this->config, 'max_page_size', 1000));
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
    public function getRole()
    {
        if (array_get($this->config, 'map_group_to_role', false)) {
            $groups = $this->driver->getGroups();
            if (!empty($groups)) {
                foreach ($groups as $group) {
                    $role = $this->findRoleByGroup($group);
                    if (!empty($role)) {

                        \Log::warning("DEBUG_LDAP LDAP_ROLE:: \n" . print_r($role, true));;
                        return $role->role_id;
                    }
                }
            }
        }

        \Log::warning("DEBUG_LDAP DEFAULT_LDAP_ROLE:: \n" . print_r($this->defaultRole, true));;
        return $this->defaultRole;
    }

    /**
     * Finds a matching role, first with group dn then if not found,
     * finds with parent group's (memberOf) dn. (supporting sub-group).
     *
     * @param array $group
     *
     * @return mixed|null
     */
    public function findRoleByGroup(array $group)
    {
        $dn = array_get($group, 'dn');

        if (!empty($dn)) {
            \Log::warning("DEBUG_LDAP GROUP_DN:: \n" . print_r($dn, true));;
            return RoleADLdap::whereDn($dn)->first();
        }

        return null;
    }

    /**
     * @return string
     */
    public function getProviderName()
    {
        return static::PROVIDER_NAME;
    }

    /**
     * @return string|null
     */
    public function getHost()
    {
        return array_get($this->config, 'host');
    }

    /**
     * @return string|null
     */
    public function getBaseDn()
    {
        return array_get($this->config, 'base_dn');
    }

    /**
     * Handles login using this service.
     *
     * @param array $credential
     * @param bool  $remember
     *
     * @return array
     * @throws \DreamFactory\Core\Exceptions\UnauthorizedException
     */
    public function handleLogin(array $credential, $remember = false)
    {
        $username = array_get($credential, 'username');
        $password = array_get($credential, 'password');
        $auth = $this->driver->authenticate($username, $password);

        if ($auth) {
            $ldapUser = $this->driver->getUser();
            $user = $this->createShadowADLdapUser($ldapUser);
            $user->last_login_date = Carbon::now()->toDateTimeString();
            $user->confirm_code = null;
            $user->save();
            Session::setUserInfoWithJWT($user, $remember);
            \Log::warning("DEBUG_LDAP driver->getGroups():: \n" . print_r($this->driver->getGroups(), true));;
            $userGroups = $this->getGroupsDns($this->driver->getGroups());

            return array_merge(Session::getPublicInfo(), $userGroups);
        } else {
            throw new UnauthorizedException('Invalid username and password provided.');
        }
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

        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        $ldapUsername = $ldapUser->getUsername();
        if (empty($ldapUsername)) {
            $ldapUsername = $email;
        } else {
            $ldapUsername .= '+' . $serviceName;
        }

        $data = [
            'name'          => $ldapUser->getName(),
            'username'      => $ldapUsername,
            'ldap_username' => $ldapUser->getUsername(),
            'first_name'    => $ldapUser->getFirstName(),
            'last_name'     => $ldapUser->getLastName(),
            'email'         => $email,
            'is_active'     => true,
            'adldap'        => $this->getProviderName(),
            'password'      => $ldapUser->getPassword()
        ];
        $roleId = $this->getRole();
        $serviceId = $this->getServiceId();

        return static::setShadowUser($data, $roleId, $serviceId);
    }

    /**
     * Creates or updates shadow user upon successful login.
     *
     * @param array $data
     * @param null  $roleId
     * @param null  $serviceId
     *
     * @return User
     */
    protected static function setShadowUser(array $data, $roleId = null, $serviceId = null)
    {
        $email = array_get($data, 'email');
        /** @var User $user */
        $user = User::whereEmail($email)->first();

        if (empty($user)) {
            $user = User::create($data);
        } else {
            $user->username = array_get($data, 'username');
            $user->ldap_username = array_get($data, 'ldap_username');
            $user->update();
            $user = User::whereEmail($email)->first();
        }

        if (!empty($roleId)) {
            User::applyDefaultUserAppRole($user, $roleId);
        }
        if (!empty($serviceId)) {
            User::applyAppRoleMapByService($user, $serviceId);
        }

        return $user;
    }


    /**
     * Map groups to groupMembership array.
     *
     * @param array $groups
     * @return array
     */
    public function getGroupsDns(array $groups)
    {
        $result = [];
        foreach ($groups as $group) {
            $result [] = array_get($group, 'dn');
        }
        return ['groupMembership' => $result];
    }
}