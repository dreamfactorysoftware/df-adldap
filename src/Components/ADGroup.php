<?php

namespace DreamFactory\Core\ADLdap\Components;

use DreamFactory\Core\ADLdap\Contracts\Group;

class ADGroup extends BaseObject implements Group
{
    /** @type array */
    protected $data = [];

    /**
     * ADGroup constructor.
     *
     * @param array $groupData
     */
    public function __construct(array $groupData)
    {
        $this->data = static::cleanGroupData($groupData);
        $this->validate();
    }

    /**
     * Cleans and re-formats group data.
     *
     * @param array $group
     *
     * @return array
     */
    public static function cleanGroupData(array $group)
    {
        return static::cleanData($group);
    }

    /** @inheritdoc */
    protected function validate()
    {
        return true;
    }

    /** @inheritdoc */
    public function getName()
    {
        return $this->cn;
    }
}