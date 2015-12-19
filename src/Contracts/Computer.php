<?php

namespace DreamFactory\Core\ADLdap\Contracts;

interface Computer
{
    /**
     * Gets cn (common name).
     *
     * @return string
     */
    public function getName();
}