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
        $data = $this->getData();

        return ArrayUtils::get($data, 'samaccountname');
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return ArrayUtils::get($this->getData(), 'name');
    }

    /**
     * Validates user data array.
     *
     * @throws \DreamFactory\Core\Exceptions\InternalServerErrorException
     */
    protected function validate()
    {
        $attributes = array_keys($this->data);

        if (!in_array('samaccountname', $attributes)) {
            throw new InternalServerErrorException('Cannot initiate Active Directory user. Invalid user data supplied.');
        }
    }
}