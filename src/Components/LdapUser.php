<?php
namespace DreamFactory\Core\ADLdap\Components;

use DreamFactory\Core\ADLdap\Contracts\User as LdapUserContract;

class LdapUser extends BaseObject implements LdapUserContract
{
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
        return array_get($this->data, 'uidnumber');
    }

    /**
     * {@inheritdoc}
     */
    public function getUid()
    {
        return array_get($this->data, 'uid');
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return array_get($this->data, 'cn');
    }

    /**
     * {@inheritdoc}
     */
    public function getFirstName()
    {
        return array_get($this->data, 'givenname');
    }

    /**
     * {@inheritdoc}
     */
    public function getLastName()
    {
        return array_get($this->data, 'sn');
    }

    /**
     * {@inheritdoc}
     */
    public function getEmail()
    {
        return array_get($this->data, 'mail');
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
        $password = array_get($this->data, 'userpassword');
        $password .= $this->getDn();
        $password .= time();
        $password = bcrypt($password);

        return $password;
    }
}