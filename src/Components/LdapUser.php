<?php

namespace DreamFactory\Core\ADLdap\Components;

use DreamFactory\Core\ADLdap\Contracts\User as LdapUserContract;
use Illuminate\Support\Facades\Hash;
use \Illuminate\Support\Arr;

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
        return Arr::get($this->data, 'uidnumber');
    }

    /**
     * {@inheritdoc}
     */
    public function getUid()
    {
        return Arr::get($this->data, 'uid');
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->getUid();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return Arr::get($this->data, 'cn');
    }

    /**
     * {@inheritdoc}
     */
    public function getFirstName()
    {
        return Arr::get($this->data, 'givenname');
    }

    /**
     * {@inheritdoc}
     */
    public function getLastName()
    {
        return Arr::get($this->data, 'sn');
    }

    /**
     * {@inheritdoc}
     */
    public function getEmail()
    {
        return Arr::get($this->data, 'mail');
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
        $password = Arr::get($this->data, 'userpassword');
        $password .= $this->getDn();
        $password .= time();
        $password = Hash::make($password);

        return $password;
    }
}