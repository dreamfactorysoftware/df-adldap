<?php
namespace DreamFactory\Core\ADLdap\Components;

use DreamFactory\Core\ADLdap\Contracts\Provider;
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
            }

            return $this->userData;
        }

        return [];
    }

    /** @inheritdoc */
    public function getObjectByDn($dn)
    {
        $rs = ldap_read($this->connection, $dn, "(objectclass=*)");
        $objInfo = ldap_get_entries($this->connection, $rs);

        if (isset($objInfo[0])) {
            return $objInfo[0];
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
        return [];
    }

    /** @inheritdoc */
    public function getUserByUserName($username)
    {
        $dn = $this->getUserDn($username);
        if (empty($dn)) {
            throw new NotFoundException('User not found by username [' . $username . ']');
        }

        return $this->getUser($this->getObjectByDn($dn));
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
            $dcs[$key] = substr($dc, strpos($dc, '=') + 1);
        }

        $domain = implode('.', $dcs);

        return $domain;
    }

    /**
     * Gets DN by username
     *
     * @param $username
     * @param $uidField
     *
     * @return string
     */
    public function getUserDn($username, $uidField = 'uid')
    {
        $baseDn = $this->baseDn;
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
     * @param       $filter
     * @param array $attributes
     *
     * @return array
     */
    public function search($filter, array $attributes = [])
    {
        $baseDn = $this->baseDn;
        $connection = $this->connection;

        $cookie = '';
        $out = ['count' => 0];

        do {
            ldap_control_paged_result($connection, $this->pageSize, true, $cookie);

            $search = ldap_search($connection, $baseDn, $filter, $attributes);
            $result = ldap_get_entries($connection, $search);

            $out['count'] += $result['count'];
            array_shift($result);
            $out = array_merge($out, $result);

            ldap_control_paged_result_response($connection, $search, $cookie);
        } while ($cookie !== null && $cookie != '');

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
}