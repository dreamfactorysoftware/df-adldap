<?php
namespace DreamFactory\Core\ADLdap\Components;

use DreamFactory\Core\Exceptions\BadRequestException;
use DreamFactory\Core\Exceptions\NotFoundException;
use DreamFactory\Library\Utility\ArrayUtils;

class ADLdap extends OpenLdap
{
    /** @var  string */
    protected $accountSuffix;

    /**
     * @param string      $host
     * @param string      $baseDn
     * @param string|null $accountSuffix
     */
    public function __construct($host, $baseDn, $accountSuffix = null)
    {
        parent::__construct($host, $baseDn);
        $this->setAccountSuffix($accountSuffix);
    }

    /**
     * @param string|null $suffix
     */
    public function setAccountSuffix($suffix = null)
    {
        if (empty($suffix)) {
            $baseDn = $this->getBaseDn();
            $suffix = static::getDomainName($baseDn);
        }

        $this->accountSuffix = $suffix;
    }

    /**
     * @return string
     */
    public function getAccountSuffix()
    {
        return $this->accountSuffix;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate($username, $password)
    {
        if (empty($username) || empty($password)) {
            throw new BadRequestException('No username and/or password provided.');
        }

        $accountSuffix = $this->getAccountSuffix();

        try {
            $preAuth = ldap_bind($this->connection, $username . '@' . $accountSuffix, $password);

            if ($preAuth) {
                $this->userDn = $this->getUserDn($username, 'samaccountname');

                $auth = ldap_bind($this->connection, $this->userDn, $password);
            } else {
                $auth = false;
            }
        } catch (\Exception $e) {
            \Log::alert('Failed to authenticate with AD server using LDAP. ' . $e->getMessage());
            $auth = false;
        }

        $this->authenticated = $auth;

        return $auth;
    }

    /** @inheritdoc */
    public function getUser()
    {
        return new ADUser($this->getUserInfo());
    }

    /** @inheritdoc */
    public function getGroups($username = null, $attributes = [])
    {
        $result = [];

        if (empty($username)) {
            $user = $this->getUser();
        } else {
            $user = $this->getUserByUserName($username);
        }
        $groups = $user->memberof;

        if (!is_array($groups)) {
            $groups = [$groups];
        }

        foreach ($groups as $group) {
            $adGroup = new ADGroup($this->getObjectByDn($group));

            if (in_array('primary', $attributes) || empty($attributes)) {
                $result[] = array_merge($adGroup->getData($attributes), ['primary' => false]);
            } else {
                $result[] = $adGroup->getData($attributes);
            }
        }

        $primaryGroupId = $user->primarygroupid;
        $primaryGroup = $this->getGroupByPrimaryGroupId($primaryGroupId);

        if (in_array('primary', $attributes) || empty($attributes)) {
            $result[] = array_merge($primaryGroup->getData($attributes), ['primary' => true]);
        } else {
            $result[] = $primaryGroup->getData($attributes);
        }

        return $result;
    }

    /** @inheritdoc */
    public function getUserByUserName($username)
    {
        $dn = $this->getUserDn($username, 'samaccountname');
        if (empty($dn)) {
            throw new NotFoundException('User not found by username [' . $username . ']');
        }

        return new ADUser($this->getObjectByDn($dn));
    }

    /** @inheritdoc */
    public function listUser(array $attributes = [])
    {
        $result = [];
        $users = $this->search(
            "(&(objectCategory=person)(objectClass=user)(samaccountname=*))",
            $attributes
        );

        if ($users['count'] === 0) {
            return [];
        }

        foreach ($users as $user) {
            if (is_array($user)) {
                $adUser = new ADUser($user);
                $result[] = $adUser->getData($attributes);
            }
        }

        return $result;
    }

    /** @inheritdoc */
    public function getGroupByCn($cn)
    {
        $group = $this->search(
            "(&(objectCategory=group)(objectClass=group)(cn=" . $cn . "))"
        );
        if (!ArrayUtils::get($group, 'count')) {
            throw new NotFoundException('Group not found by cn [' . $cn . ']');
        }

        return new ADGroup($group[0]);
    }

    protected function getGroupByPrimaryGroupId($id)
    {
        $groups = $this->search(
            "(&(objectCategory=group)(objectClass=group))",
            ['*', 'primarygrouptoken']
        );

        array_shift($groups);

        foreach ($groups as $group) {
            if (ArrayUtils::getDeep($group, 'primarygrouptoken', 0) === $id) {
                return new ADGroup($group);
            }
        }

        throw new NotFoundException('Group not found by primarygrouptoken [' . $id . ']');
    }

    /** @inheritdoc */
    public function listGroup(array $attributes = [])
    {
        $result = [];
        $groups = $this->search(
            "(&(objectCategory=group)(objectClass=group))",
            $attributes
        );

        if ($groups['count'] === 0) {
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
    public function getComputerByCn($cn)
    {
        $computer = $this->search(
            "(&(objectCategory=computer)(objectClass=computer)(cn=" . $cn . "))"
        );
        if (!ArrayUtils::get($computer, 'count')) {
            throw new NotFoundException('Computer not found by cn [' . $cn . ']');
        }

        return new ADComputer($computer[0]);
    }

    /** @inheritdoc */
    public function listComputer(array $attributes = [])
    {
        $result = [];
        $computers = $this->search(
            "(&(objectCategory=computer)(objectClass=computer))",
            $attributes
        );

        if ($computers['count'] === 0) {
            return [];
        }

        foreach ($computers as $computer) {
            if (is_array($computer)) {
                $adComputer = new ADComputer($computer);
                $result[] = $adComputer->getData($attributes);
            }
        }

        return $result;
    }
}