<?php
namespace DreamFactory\Core\ADLdap\Components;

use DreamFactory\Library\Utility\ArrayUtils;
use DreamFactory\Core\Exceptions\InternalServerErrorException;

class ADUser extends LdapUser
{
    /**
     * {@inheritdoc}
     */
    public function getUid()
    {
        return ArrayUtils::get($this->data, 'samaccountname');
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return ArrayUtils::get($this->data, 'name');
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