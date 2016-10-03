<?php

namespace DreamFactory\Core\ADLdap\Components;

use DreamFactory\Core\ADLdap\Contracts\Group;

class ADGroup extends BaseObject implements Group
{
    /** @type array */
    protected $data = [];

    /** @inheritdoc */
    public function getName()
    {
        return $this->cn;
    }
}