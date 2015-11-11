<?php
namespace DreamFactory\Core\ADLdap\Components;

use DreamFactory\Core\ADLdap\Contracts\User as LdapUserContract;
use DreamFactory\Core\ADLdap\Services\LDAP;
use DreamFactory\Core\Exceptions\InternalServerErrorException;
use DreamFactory\Library\Utility\ArrayUtils;
use DreamFactory\Core\Exceptions\NotFoundException;

class LdapUser implements LdapUserContract
{
    /** @var array */
    protected $data = [];

    /**
     * @param array $userInfo
     */
    public function __construct(array $userInfo)
    {
        $this->data = static::cleanUserData($userInfo);
        $this->validate();
    }

    /**
     * Cleans and re-formats user data.
     *
     * @param array $user
     *
     * @return array
     */
    protected function cleanUserData(array $user)
    {
        return LDAP::cleanData($user);
    }

    /**
     * Validates user data array.
     *
     * @throws \DreamFactory\Core\Exceptions\InternalServerErrorException
     */
    protected function validate()
    {
        $attributes = array_keys($this->data);

        if (!in_array('dn', $attributes) || !in_array('objectclass', $attributes) || !in_array('uid', $attributes)) {
            throw new InternalServerErrorException('Cannot initiate LDAP user. Invalid user data supplied.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDomain()
    {
        return OpenLdap::getDomainName($this->getDn());
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        $data = $this->getData();

        return ArrayUtils::get($data, 'uidnumber');
    }

    /**
     * {@inheritdoc}
     */
    public function getUid()
    {
        $data = $this->getData();

        return ArrayUtils::get($data, 'uid');
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        $data = $this->getData();

        return ArrayUtils::get($data, 'cn');
    }

    /**
     * {@inheritdoc}
     */
    public function getFirstName()
    {
        $data = $this->getData();

        return ArrayUtils::get($data, 'givenname');
    }

    /**
     * {@inheritdoc}
     */
    public function getLastName()
    {
        $data = $this->getData();

        return ArrayUtils::get($data, 'sn');
    }

    /**
     * {@inheritdoc}
     */
    public function getEmail()
    {
        $data = $this->getData();

        return ArrayUtils::get($data, 'mail');
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
        $data = $this->getData();

        $password = ArrayUtils::get($data, 'userpassword');
        $password .= $this->getDn();
        $password .= time();
        $password = bcrypt($password);

        return $password;
    }

    /**
     * Magic method to fetch any user value.
     *
     * @param string $method
     * @param array  $args
     *
     * @return mixed
     * @throws NotFoundException
     */
    public function __call($method, $args)
    {
        $key = strtolower(substr($method, 3));

        $data = $this->getData();

        return ArrayUtils::get($data, $key);
    }
}