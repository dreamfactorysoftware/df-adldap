<?php

namespace DreamFactory\Core\ADLdap\Components;

use DreamFactory\Core\ADLdap\Contracts\Provider;
use DreamFactory\Core\ADLdap\Utility\VersionUtility;
use DreamFactory\Core\Exceptions\InternalServerErrorException;
use DreamFactory\Core\Exceptions\BadRequestException;
use DreamFactory\Core\Exceptions\NotFoundException;

class OpenLdap implements Provider
{
    /** @var resource */
    protected $connection;

    /** @var  string */
    protected $baseDn;

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
     */
    public function __construct($host, $baseDn)
    {
        $connection = ldap_connect($host);
        ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($connection, LDAP_OPT_REFERRALS, 0);

        $this->connection = $connection;
        $this->baseDn = $baseDn;
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
                \Log::warning("DEBUG_LDAP USER_ByDn:: \n" . print_r($this->userData, true));;
            }

            return $this->userData;
        }

        return [];
    }

    /** @inheritdoc */
    public function getObjectByDn($dn, $attributes = [])
    {
        if (VersionUtility::isPHP73orLower()) {
            $out = $this->getObjectByDn_php73($dn, $attributes);
        } else {
            $out = $this->getObjectByDn_php74($dn, $attributes);
        }

        if (isset($out[0])) {
            return $out[0];
        }

        return [];
    }

    /** @inheritdoc */
    public function getUser()
    {
        return new LdapUser($this->getUserInfo());
    }

    /** @inheritdoc */
    public function getGroups($username = null, $attributes = [])
    {
        $result = [];

        if (empty($username)) {
            $user = $this->getUser();
            \Log::warning("DEBUG_LDAP USER:: \n" . print_r($user, true));;
        } else {
            $user = $this->getUserByUserName($username);
            \Log::warning("DEBUG_LDAP USER:: \n" . print_r($user, true));;
        }

        $search = $this->search('(&(memberUid=' . $user->uid . '))');
        \Log::warning("DEBUG_LDAP MEMBERUID_SEARCH:: \n" . print_r($search, true));;
        $groups = !empty($user->memberof) ? $user->memberof : $search;
        if (empty($groups) && !is_null($user->groupmembership)) {
            $groups = $user->groupmembership;
        }

        if (empty($groups) && array_key_exists('count', $groups)) {
            $groups = isset($user->getData()['groupmembership']) ? $user->getData()['groupmembership'] : [];
        }

        \Log::warning("DEBUG_LDAP FOUND_GROUPS:: \n" . print_r($groups, true));;
        if (!empty($groups)) {

            if (!is_array($groups)) {
                $groups = [$groups];
            }

            foreach ($groups as $key => $group) {
                if ($key !== 'count') {
                    $dn = is_array($group) ? array_get($group, 'dn') : $group;
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

        $user = new LdapUser($this->getObjectByDn($dn));
        return $user;
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
        $dn = str_replace('DC=', 'dc=', $dn);
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

    /**
     * A generic function for searching AD/LDAP server.
     *
     * @param string $filter
     * @param array  $attributes
     * @param string $baseDn
     *
     * @return array
     * @throws \ErrorException;
     */
    public function search($filter, array $attributes = [], $baseDn = null)
    {
        $baseDn = (empty($baseDn)) ? $this->baseDn : $baseDn;
        $connection = $this->connection;

        if (VersionUtility::isPHP73orLower()) {
            $out = $this->search_php73($connection, $filter, $attributes, $baseDn);
        } else {
            $out = $this->search_php74($connection, $filter, $attributes, $baseDn);
        }

        return $out;
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
        // TODO: Implement listGroup() method.
    }

    /** @inheritdoc */
    public function listComputer(array $attributes = [], $filter = null)
    {
        // TODO: Implement listComputer() method.
    }

    /**
     * Added to be able to support PHP 7.3 and lower.
     *
     * You can only call this in PHP 7.3 and lower. In all others, the version causes an error when calling
     * the method ldap_control_paged_result, as this method deprecated in 7.4+.
     *
     * @param resource $connection
     * @param string $filter
     * @param array $attributes
     * @param string $baseDn
     * @return array
     * @throws \ErrorException
     */
    protected function search_php73($connection, $filter, array $attributes = [], $baseDn = null) {
        $cookie = '';
        $search = false;
        $result = false;
        $out = ['count' => 0];

        do {
            try {
                ldap_control_paged_result($connection, $this->pageSize, true, $cookie);

                $search = ldap_search($connection, $baseDn, $filter, $attributes);
                $result = ldap_get_entries($connection, $search);

                $out['count'] += $result['count'];
                array_shift($result);
                $out = array_merge($out, $result);

                ldap_control_paged_result_response($connection, $search, $cookie);
            } catch (\ErrorException $e) {
                if (false === $search || false === $result) {
                    throw $e;
                }
                $cookie = '';
            }
        } while ($cookie !== null && $cookie != '');

        return $out;
    }

    /**
     * Added to be able to support PHP 7.4+.
     *
     * You can only call this in PHP 7.4+. In all others, the version causes an error when calling the method ldap_search
     *
     * @param resource $connection
     * @param string $filter
     * @param array $attributes
     * @param string $baseDn
     * @return array
     */
    protected function search_php74($connection, $filter, array $attributes = [], $baseDn = null) {
        $cookie = '';
        $out = ['count' => 0];

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
     * Added to be able to support PHP 7.3 and lower.
     *
     * You can only call this in PHP 7.3 and lower. In all others, the version causes an error when calling
     * the method ldap_control_paged_result, as this method deprecated in 7.4+.
     *
     * @param $dn
     * @param array $attributes
     * @return array
     * @throws \ErrorException
     */
    protected function getObjectByDn_php73($dn, $attributes = []) {
        $cookie = '';
        $search = false;
        $result = false;
        $out = ['count' => 0];

        do {
            try {
                ldap_control_paged_result($this->connection, $this->pageSize, true, $cookie);

                $search = ldap_read($this->connection, $dn, "(objectclass=*)", $attributes);
                $result = ldap_get_entries($this->connection, $search);

                $out['count'] += $result['count'];
                array_shift($result);
                $out = array_merge($out, $result);

                ldap_control_paged_result_response($this->connection, $search, $cookie);
            } catch (\ErrorException $e) {
                if (false === $search || false === $result) {
                    throw $e;
                }
                $cookie = '';
            }
        } while ($cookie !== null && $cookie != '');

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
    protected function getObjectByDn_php74($dn, $attributes = []) {
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
        return $out;
    }
}
