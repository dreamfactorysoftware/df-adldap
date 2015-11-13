<?php

namespace DreamFactory\Core\ADLdap\Contracts;

interface Group
{
    /**
     * Gets cn (common name).
     *
     * @return string
     */
    public function getName();
}