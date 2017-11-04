<?php

namespace DreamFactory\Core\ADLdap\Resources;

use DreamFactory\Core\Contracts\RequestHandlerInterface;
use DreamFactory\Core\Utility\ResourcesWrapper;
use DreamFactory\Core\Enums\ApiOptions;
use DreamFactory\Core\ADLdap\Contracts\Provider;

class Group extends BaseADLdapResource
{
    /**
     * Name of this resource.
     */
    const RESOURCE_NAME = 'group';

    /** @type Provider */
    protected $provider = null;

    /**
     * @param \DreamFactory\Core\Contracts\RequestHandlerInterface $parent
     */
    public function setParent(RequestHandlerInterface $parent)
    {
        parent::setParent($parent);
        $this->provider = $this->parent->getDriver();
    }

    /**
     * Handles all GET requests.
     *
     * @return array
     */
    protected function handleGET()
    {
        $this->parent->authenticateAdminUser();
        $groupName = $this->resource;
        $username = $this->request->getParameter('user');
        $fields = $this->request->getParameter(ApiOptions::FIELDS, ApiOptions::FIELDS_ALL);
        $filter = $this->request->getParameter(ApiOptions::FILTER);
        $attributes = [];

        if ('*' !== $fields) {
            $attributes = explode(',', $fields);
        }

        if (!empty($username)) {
            $asList = $this->request->getParameterAsBool(ApiOptions::AS_LIST);
            if ($asList) {
                $attributes = ['samaccountname'];
            }
            $resources = $this->provider->getGroups($username, $attributes);
        } else {
            if (empty($groupName)) {
                $asList = $this->request->getParameterAsBool(ApiOptions::AS_LIST);
                if ($asList) {
                    $attributes = ['samaccountname'];
                }
                $resources = $this->provider->listGroup($attributes, $filter);
            } else {
                $adGroup = $this->provider->getGroupByCn($groupName);
                $resources = $adGroup->getData($attributes);
            }
        }

        return ResourcesWrapper::cleanResources($resources);
    }

    protected function getApiDocPaths()
    {
        $base = parent::getApiDocPaths();
        $resourceName = strtolower($this->name);
        $path = '/' . $resourceName;

        $base[$path]['get']['parameters'][] = [
            'name'        => 'user',
            'description' => 'Accepts an username to list groups by username.',
            'schema'      => ['type' => 'string', 'format' => 'int32'],
            'in'          => 'query',
        ];

        return $base;
    }

    protected function getApiDocSchemas()
    {
        $base = parent::getApiDocSchemas();
        $base['Group']['properties'] =
            array_merge($base['Group']['properties'], [
                'member'      => [
                    'type'        => 'array',
                    'description' => 'Lists the member of the group.',
                    'items'       => [
                        'type' => 'string'
                    ]
                ],
                'description' => [
                    'type'        => 'string',
                    'description' => 'Description of the group.'
                ],
            ]);

        return $base;
    }
}