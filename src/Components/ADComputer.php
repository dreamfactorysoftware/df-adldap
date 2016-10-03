<?php

namespace DreamFactory\Core\ADLdap\Components;

use DreamFactory\Core\ADLdap\Contracts\Computer;

class ADComputer extends BaseObject implements Computer
{
    /** @type array */
    protected $data = [];

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->cn;
    }
}