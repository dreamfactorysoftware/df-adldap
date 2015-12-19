<?php

namespace DreamFactory\Core\ADLdap\Components;

use DreamFactory\Core\ADLdap\Contracts\Computer;

class ADComputer extends BaseObject implements Computer
{
    /** @type array */
    protected $data = [];

    /**
     * ADComputer constructor.
     *
     * @param array $computerData
     */
    public function __construct(array $computerData)
    {
        $this->data = static::cleanComputerData($computerData);
        $this->validate();
    }

    /**
     * Cleans and re-formats group data.
     *
     * @param array $group
     *
     * @return array
     */
    public static function cleanComputerData(array $group)
    {
        return static::cleanData($group);
    }

    /** @inheritdoc */
    protected function validate()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->cn;
    }
}