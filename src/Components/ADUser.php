<?php
namespace DreamFactory\Core\ADLdap\Components;


class ADUser extends LdapUser
{
    /**
     * {@inheritdoc}
     */
    public function getUid()
    {
        return array_get($this->data, 'samaccountname');
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return array_get($this->data, 'name');
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
}