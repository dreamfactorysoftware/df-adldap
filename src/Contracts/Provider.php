<?php
namespace DreamFactory\Core\ADLdap\Contracts;

use DreamFactory\Core\ADLdap\Contracts\User as ADLdapUser;
use DreamFactory\Core\ADLdap\Contracts\Group as ADLdapGroup;
use DreamFactory\Core\ADLdap\Contracts\Computer as ADLdapComputer;

interface Provider
{
    /**
     * Gets the base dn.
     *
     * @return string
     */
    public function getBaseDn();

    /**
     * Gets full dn.
     *
     * @param string $username
     * @param string $uidField
     * @param string $baseDn
     *
     * @return string
     */
    public function getUserDn($username, $uidField = 'uid', $baseDn = null);

    /**
     * Authenticates User.
     *
     * @param string $username
     * @param string $password
     *
     * @return mixed
     */
    public function authenticate($username, $password);

    /**
     * Checks to see if connection is bound/authenticated.
     *
     * @return bool
     */
    public function isAuthenticated();

    /**
     * Gets user info.
     *
     * @return array
     */
    public function getUserInfo();

    /**
     * Gets the user object of the authenticated user.
     *
     * @return ADLdapUser
     */
    public function getUser();

    /**
     * Gets the list of groups of the authenticated user.
     *
     * @param string $username
     * @param array  $attributes
     *
     * @return array
     */
    public function getGroups($username = null, $attributes = []);

    /**
     * Gets user object by username.
     *
     * @param string $username
     *
     * @return ADLdapUser
     */
    public function getUserByUserName($username);

    /**
     * Gets group object by group cn.
     *
     * @param $cn
     *
     * @return ADLdapGroup
     */
    public function getGroupByCn($cn);

    /**
     * Gets computer object by its cn.
     *
     * @param $cn
     *
     * @return ADLdapComputer
     */
    public function getComputerByCn($cn);

    /**
     * Gets AD/Ldap objects by its dn
     *
     * @param string $dn
     * @param array  $attributes
     *
     * @return array
     */
    public function getObjectByDn($dn, $attributes = []);

    /**
     * Gets the connection resource.
     *
     * @return resource
     */
    public function getConnection();

    /**
     * Gets the domain name.
     *
     * @param string $dn
     *
     * @return string
     */
    public static function getDomainName($dn);

    /**
     * Lists all users.
     *
     * @param array $attributes
     * @param mixed $filter
     *
     * @return mixed
     */
    public function listUser(array $attributes = [], $filter = null);

    /**
     * Lists all groups.
     *
     * @param array $attributes
     * @param mixed $filter
     *
     * @return mixed
     */
    public function listGroup(array $attributes = [], $filter = null);

    /**
     * Lists all computers.
     *
     * @param array $attributes
     * @param mixed $filter
     *
     * @return mixed
     */
    public function listComputer(array $attributes = [], $filter = null);
}