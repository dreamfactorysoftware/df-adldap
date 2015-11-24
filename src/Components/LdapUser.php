<?php
namespace DreamFactory\Core\ADLdap\Components;

use DreamFactory\Core\ADLdap\Contracts\User as LdapUserContract;
use DreamFactory\Library\Utility\ArrayUtils;

class LdapUser extends BaseObject implements LdapUserContract
{
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
    public static function cleanUserData(array $user)
    {
        return static::cleanData($user);
    }

    /**
     * Validates user data array.
     *
     * @throws \DreamFactory\Core\Exceptions\InternalServerErrorException
     */
    protected function validate()
    {
        return true;
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
    public function getId()
    {
        return ArrayUtils::get($this->data, 'uidnumber');
    }

    /**
     * {@inheritdoc}
     */
    public function getUid()
    {
        return ArrayUtils::get($this->data, 'uid');
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return ArrayUtils::get($this->data, 'cn');
    }

    /**
     * {@inheritdoc}
     */
    public function getFirstName()
    {
        return ArrayUtils::get($this->data, 'givenname');
    }

    /**
     * {@inheritdoc}
     */
    public function getLastName()
    {
        return ArrayUtils::get($this->data, 'sn');
    }

    /**
     * {@inheritdoc}
     */
    public function getEmail()
    {
        return ArrayUtils::get($this->data, 'mail');
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
        $password = ArrayUtils::get($this->data, 'userpassword');
        $password .= $this->getDn();
        $password .= time();
        $password = bcrypt($password);

        return $password;
    }
}