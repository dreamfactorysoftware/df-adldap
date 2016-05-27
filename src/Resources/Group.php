<?php
namespace DreamFactory\Core\ADLdap\Resources;

use DreamFactory\Core\Contracts\RequestHandlerInterface;
use DreamFactory\Core\Utility\ResourcesWrapper;
use DreamFactory\Core\Enums\ApiOptions;
use DreamFactory\Core\ADLdap\Contracts\Provider;
use DreamFactory\Library\Utility\ArrayUtils;

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
        } else if (empty($groupName)) {
            $asList = $this->request->getParameterAsBool(ApiOptions::AS_LIST);
            if ($asList) {
                $attributes = ['samaccountname'];
            }
            $resources = $this->provider->listGroup($attributes, $filter);
        } else {
            $adGroup = $this->provider->getGroupByCn($groupName);
            $resources = $adGroup->getData($attributes);
        }

        return ResourcesWrapper::cleanResources($resources);
    }

    public static function getApiDocInfo($service, array $resource = [])
    {
        $base = parent::getApiDocInfo($service, $resource);
        $serviceName = strtolower($service);
        $class = trim(strrchr(static::class, '\\'), '\\');
        $resourceName = strtolower(ArrayUtils::get($resource, 'name', $class));
        $path = '/' . $serviceName . '/' . $resourceName;

        $base['paths'][$path]['get']['parameters'][] = [
            'name'        => 'user',
            'description' => 'Accepts an username to list groups by username.',
            'type'        => 'string',
            'format'      => 'int32',
            'in'          => 'query',
            'required'    => false,
        ];

        $base['definitions']['GroupResponse']['properties'] =
            array_merge($base['definitions']['GroupResponse']['properties'], [
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