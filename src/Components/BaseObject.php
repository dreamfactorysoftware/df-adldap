<?php

namespace DreamFactory\Core\ADLdap\Components;

use DreamFactory\Core\Exceptions\InternalServerErrorException;
use DreamFactory\Library\Utility\ArrayUtils;
use DreamFactory\Core\Utility\DataFormatter;
use DreamFactory\Core\Exceptions\NotFoundException;

class BaseObject
{
    /** @var array */
    protected $data = [];

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
     * @param array $object
     *
     * @return array
     */
    public static function cleanData(array $object)
    {
        foreach ($object as $key => $value) {
            if ($key === 'count') {
                unset($object[$key]);
                continue;
            }
            if (is_numeric($key)) {
                unset($object[$key]);
                continue;
            } else if (is_array($value) && isset($value[0]) && !DataFormatter::isPrintable($value[0])) {
                unset($object[$key]);
                continue;
            } else if (is_string($value) && !DataFormatter::isPrintable($value)) {
                unset($object[$key]);
                continue;
            }

            if (is_array($value)) {
                if (ArrayUtils::get($value, 'count') === 1) {
                    $object[$key] = $value[0];
                } else if (ArrayUtils::get($value, 'count') > 1) {
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

        return ArrayUtils::get($this->data, $key);
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
        return ArrayUtils::get($this->data, $key);
    }
}