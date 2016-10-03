<?php

namespace DreamFactory\Core\ADLdap\Components;

use DreamFactory\Core\Utility\DataFormatter;
use DreamFactory\Core\Exceptions\NotFoundException;

abstract class BaseObject
{
    /** @var array */
    protected $data = [];

    /** @var array */
    protected $rawData = [];

    /**
     * @param array $userInfo
     */
    public function __construct(array $userInfo)
    {
        $this->rawData = static::cleanData($userInfo, true);
        $this->data = static::cleanData($this->rawData);
        $this->validate();
    }

    /**
     * Validates object.
     *
     * @throws \DreamFactory\Core\Exceptions\InternalServerErrorException
     */
    protected function validate()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getData(array $attributes = [])
    {
        if (empty($attributes)) {
            return $this->data;
        } else {
            $data = [];
            foreach ($attributes as $attribute) {
                $data[$attribute] = $this->{$attribute};
            }

            return $data;
        }
    }

    /**
     * Cleans and re-formats data.
     *
     * @param array   $object
     * @param boolean $cleanNumericOnly
     *
     * @return array
     */
    public static function cleanData(array $object, $cleanNumericOnly = false)
    {
        foreach ($object as $key => $value) {
            if ('count' === $key) {
                unset($object[$key]);
                continue;
            } elseif (is_numeric($key)) {
                unset($object[$key]);
                continue;
            } else if (
                !$cleanNumericOnly &&
                is_array($value) &&
                isset($value[0]) &&
                !DataFormatter::isPrintable($value[0])
            ) {
                unset($object[$key]);
                continue;
            } else if (!$cleanNumericOnly && is_string($value) && !DataFormatter::isPrintable($value)) {
                unset($object[$key]);
                continue;
            }

            if (is_array($value)) {
                if (array_get($value, 'count') === 1) {
                    $object[$key] = $value[0];
                } else if (array_get($value, 'count') > 1) {
                    unset($object[$key]['count']);
                }
            }
        }

        return $object;
    }

    /**
     * Magic method to fetch any user value.
     *
     * @param string $method
     * @param array  $args
     *
     * @return mixed
     * @throws NotFoundException
     */
    public function __call($method, $args)
    {
        $key = strtolower(substr($method, 3));

        return array_get($this->data, $key);
    }

    /**
     * Magic method to fetch any user value.
     *
     * @param string $key
     *
     * @return mixed
     * @throws NotFoundException
     */
    public function __get($key)
    {
        return array_get($this->data, $key, array_get($this->rawData, $key));
    }
}