<?php

namespace DreamFactory\Core\ADLdap\Components;

use DreamFactory\Core\ADLdap\Contracts\Provider;
use DreamFactory\Core\Exceptions\InternalServerErrorException;
use DreamFactory\Core\Exceptions\BadRequestException;
use DreamFactory\Core\Exceptions\NotFoundException;
use \Illuminate\Support\Arr;
class OpenLdap implements Provider
{
    protected \LDAP\Connection $connection;

    /** @var  string */
    protected $userDn;

    /** @var array */
    protected $userData = [];

    /** @var bool */
    protected $authenticated = false;

    /** @type int Number of records returned per page */
    protected $pageSize = 1000;

    /**
     * @param $host
     * @param $baseDn
     * @param string $baseDn
     */
    public function __construct($host, protected $baseDn)
    {
        $connection = ldap_connect($host);
        ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($connection, LDAP_OPT_REFERRALS, 0);

        $this->connection = $connection;
    }

    /**
     * @param int $size
     */
    public function setPageSize($size)
    {
        $this->pageSize = $size;
    }

    /**
     * @return string
     */
    public function getBaseDn()
    {
        return $this->baseDn;
    }

    /**
     * Performs user authentication.
     *
     * @param $username
     * @param $password
     *
     * @return bool
     * @throws BadRequestException
     */
    public function authenticate($username, $password)
    {
        if (empty($username) || empty($password)) {
            throw new BadRequestException('No username and/or password provided.');
        }

        $this->userDn = $this->getUserDn($username);

        try {
            $auth = ldap_bind($this->connection, $this->userDn, $password);
        } catch (\Exception $e) {
            \Log::alert('Failed to authenticate using LDAP. ' . $e->getMessage());
            $auth = false;
        }

        $this->authenticated = $auth;

        return $auth;
    }

    /**
     * Checks to see if the instance is authenticated.
     *
     * @return bool
     */
    public function isAuthenticated()
    {
        return $this->authenticated;
    }

    /**
     * Reads all objects.
     *
     * @return array
     */
    public function getUserInfo()
    {
        if ($this->isAuthenticated()) {
            if (empty($this->userData)) {
                $this->userData = $this->getObjectByDn($this->userDn);
            }

            return $this->userData;
        }

        return [];
    }

    /** @inheritdoc */
    public function getUser()
    {
        return new LdapUser($this->getUserInfo());
    }

    /** @inheritdoc */
    public function getGroups($username = null, $attributes = [], $filter = '')
    {
        $result = [];

        if (empty($username)) {
            $user = $this->getUser();
        } else {
            $user = $this->getUserByUserName($username);
        }

        $search = $this->search("(&(memberUid=$user->uid)(objectClass=posixGroup)$filter)", $attributes);
        $groups = !empty($user->memberof) ? $user->memberof : $search;
        if ((empty($groups) || (isset($groups['count'])) && $groups['count'] === 0) && !is_null($user->groupmembership)) {
            $groups = $user->groupmembership;
        }

        if (empty($groups) || (isset($groups['count']) && $groups['count'] === 0)) {
            $groups = $user->getData()['groupmembership'] ?? [];
        }

        if (!empty($groups)) {

            if (!is_array($groups)) {
                $groups = [$groups];
            }

            foreach ($groups as $key => $group) {
                if ($key !== 'count') {
                    $dn = is_array($group) ? Arr::get($group, 'dn') : $group;
                    $adGroup = new ADGroup($this->getObjectByDn($dn));

                    if (in_array('primary', $attributes) || empty($attributes)) {
                        $result[] = array_merge($adGroup->getData($attributes), ['primary' => false]);
                    } else {
                        $result[] = $adGroup->getData($attributes);
                    }
                }
            }
        }

        return $result;
    }

    /** @inheritdoc */
    public function getUserByUserName($username)
    {
        $dn = $this->getUserDn($username);
        if (empty($dn)) {
            throw new NotFoundException('User not found by username [' . $username . ']');
        }

        return new LdapUser($this->getObjectByDn($dn));
    }

    /**
     * Returns connection resource.
     *
     * @return resource
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Fetches domain name from base DN.
     *
     * @param $dn
     *
     * @return string
     * @throws InternalServerErrorException
     */
    public static function getDomainName($dn)
    {
        $dn = str_replace('DC=', 'dc=', $dn);
        $dn = substr($dn, strpos($dn, 'dc='));
        $dcs = explode(',', $dn);

        if (!is_array($dcs)) {
            throw new InternalServerErrorException('Cannot determine Domain name. Invalid Base Dn supplied.');
        }

        foreach ($dcs as $key => $dc) {
            $dcs[$key] = trim(substr($dc, strpos($dc, '=') + 1));
        }

        $domain = implode('.', $dcs);

        return $domain;
    }

    /**
     * @param $dn
     *
     * Fetches the root (DC parts) of a DN
     *
     * @return mixed|string
     */
    public static function getRootDn($dn)
    {
        $dn = str_replace('DC=', 'dc=', (string) $dn);
        $dn = substr($dn, strpos($dn, 'dc='));

        return $dn;
    }

    /** {@inheritdoc} */
    public function getUserDn($username, $uidField = 'uid', $baseDn = null)
    {
        $baseDn = (empty($baseDn)) ? $this->baseDn : $baseDn;
        $connection = $this->connection;

        $search = ldap_search($connection, $baseDn, '(' . $uidField . '=' . $username . ')');
        $result = ldap_get_entries($connection, $search);

        if (isset($result[0]['dn'])) {
            return $result[0]['dn'];
        }

        return null;
    }

    /** @inheritdoc */
    public function getGroupByCn($cn)
    {
        // TODO: Implement getGroupByCn() method.
    }

    public function getComputerByCn($cn)
    {
        // TODO: Implement getComputerByCn() method.
    }

    /** @inheritdoc */
    public function listUser(array $attributes = [], $filter = null)
    {
        // TODO: Implement listUser() method.
    }

    /** @inheritdoc */
    public function listGroup(array $attributes = [], $filter = null)
    {

        $result = [];
        if (!empty($filter) && substr((string) $filter, 0, 1) != '(') {
            $filter = '(' . $filter . ')';
        }

        $groups = $this->getGroups(null, $attributes, $filter);

        if (isset($groups['count']) && $groups['count'] === 0) {
            return [];
        }

        foreach ($groups as $group) {
            if (is_array($group)) {
                $adGroup = new ADGroup($group);
                $result[] = $adGroup->getData($attributes);
            }
        }

        return $result;
    }

    /** @inheritdoc */
    public function listComputer(array $attributes = [], $filter = null)
    {
        // TODO: Implement listComputer() method.
    }

    /**
     * Added to be able to support PHP 7.4+.
     *
     * You can only call this in PHP 7.4+. In all others, the version causes an error when calling the method ldap_search
     *
     * @param string $filter
     * @param array $attributes
     * @param string $baseDn
     * @return array
     */
    public function search($filter, array $attributes = [], $baseDn = null) {
        $cookie = '';
        $out = ['count' => 0];
        $baseDn = (empty($baseDn)) ? $this->baseDn : $baseDn;
        $connection = $this->connection;

        do {
            $controls = [['oid' => LDAP_CONTROL_PAGEDRESULTS, 'value' => ['size' => $this->pageSize, 'cookie' => $cookie]]];
            $search = ldap_search($connection, $baseDn, $filter, $attributes,
                0, 0, 0, LDAP_DEREF_NEVER, $controls);
            ldap_parse_result($connection, $search, $errcode , $matcheddn , $errmsg , $referrals, $controls);

            $result = ldap_get_entries($connection, $search);

            $out['count'] += $result['count'];
            array_shift($result);
            $out = array_merge($out, $result);

            if (isset($controls[LDAP_CONTROL_PAGEDRESULTS]['value']['cookie'])) {
                $cookie = $controls[LDAP_CONTROL_PAGEDRESULTS]['value']['cookie'];
            } else {
                $cookie = '';
            }
        } while (!empty($cookie));
        return $out;
    }

    /**
     * Added to be able to support PHP 7.4+.
     *
     * You can only call this in PHP 7.4+. In all others, the version causes an error when calling the method ldap_read
     *
     * @param $dn
     * @param array $attributes
     * @return array
     */
    public function getObjectByDn($dn, $attributes = []) {
        $out = ['count' => 0];
        $cookie = '';
        do {
            $controls = [['oid' => LDAP_CONTROL_PAGEDRESULTS, 'value' => ['size' => $this->pageSize, 'cookie' => $cookie]]];
            $search = ldap_read($this->connection, $dn, "(objectclass=*)", $attributes,
                0, 0, 0, LDAP_DEREF_NEVER, $controls);
            $result = ldap_get_entries($this->connection, $search);

            $out['count'] += $result['count'];
            array_shift($result);
            $out = array_merge($out, $result);

            if (isset($controls[LDAP_CONTROL_PAGEDRESULTS]['value']['cookie'])) {
                $cookie = $controls[LDAP_CONTROL_PAGEDRESULTS]['value']['cookie'];
            } else {
                $cookie = '';
            }
        } while (!empty($cookie));

        if (isset($out[0])) {
            return $out[0];
        }

        return [];
    }
}
