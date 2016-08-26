<?php
namespace DreamFactory\Core\ADLdap\Components;

use DreamFactory\Core\Exceptions\BadRequestException;
use DreamFactory\Core\Exceptions\NotFoundException;

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
            $this->accountSuffix = static::getDomainName($baseDn);
        } else {
            $this->accountSuffix = trim(ltrim($suffix, '@'));
        }
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
                $this->userDn = $this->getUserDn($username, 'samaccountname', static::getRootDn($this->baseDn));
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
        $user = new ADUser($this->getUserInfo());

        return $user;
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

        if (!empty($groups)) {

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
        }

        $primaryGroupObjectSID = $user->getPrimaryGroupObjectSID();

        if (!empty($primaryGroupObjectSID)) {
            $primaryGroup = $this->getGroupByObjectSID($primaryGroupObjectSID);

            if (in_array('primary', $attributes) || empty($attributes)) {
                $result[] = array_merge($primaryGroup->getData($attributes), ['primary' => true]);
            } else {
                $result[] = $primaryGroup->getData($attributes);
            }
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
    public function listUser(array $attributes = [], $filter = null)
    {
        $result = [];
        $users = $this->search(
            "(&(objectCategory=person)(objectClass=user)(samaccountname=*)$filter)",
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
        if (!array_get($group, 'count')) {
            throw new NotFoundException('Group not found by cn [' . $cn . ']');
        }

        return new ADGroup($group[0]);
    }

    /**
     * Retrieves a group by its ObjectSID.
     *
     * @param string $id
     *
     * @return mixed
     * @throws \DreamFactory\Core\Exceptions\NotFoundException
     */
    protected function getGroupByObjectSID($id)
    {
        $groups = $this->search(
            "(&(objectCategory=group)(objectClass=group)(objectSID=$id))",
            [],
            static::getRootDn($this->baseDn)
        );

        if (!empty($groups) && isset($groups[0])) {
            return new ADGroup($groups[0]);
        }

        throw new NotFoundException('Group not found by ObjectSID [' . $id . ']');
    }

    /** @inheritdoc */
    public function listGroup(array $attributes = [], $filter = null)
    {
        $result = [];
        if (!empty($filter) && substr($filter, 0, 1) != '(') {
            $filter = '(' . $filter . ')';
        }
        $groups = $this->search(
            "(&(objectCategory=group)(objectClass=group)$filter)",
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
        if (!array_get($computer, 'count')) {
            throw new NotFoundException('Computer not found by cn [' . $cn . ']');
        }

        return new ADComputer($computer[0]);
    }

    /** @inheritdoc */
    public function listComputer(array $attributes = [], $filter = null)
    {
        $result = [];
        $computers = $this->search(
            "(&(objectCategory=computer)(objectClass=computer)$filter)",
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