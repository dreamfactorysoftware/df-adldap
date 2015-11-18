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
        $this->parent->authenticateAdminUser();
    }

    /**
     * Handles all GET requests.
     *
     * @return array
     */
    protected function handleGET()
    {
        $groupName = $this->resource;
        $username = $this->request->getParameter('user');
        $fields = $this->request->getParameter(ApiOptions::FIELDS, ApiOptions::FIELDS_ALL);
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
            $resources = $this->provider->listGroup($attributes);
        } else {
            $adGroup = $this->provider->getGroupByCn($groupName);
            $resources = $adGroup->getData($attributes);
        }

        return ResourcesWrapper::cleanResources($resources);
    }

    /** @inheritdoc */
    public function getApiDocInfo()
    {
        $base = parent::getApiDocInfo();

        $base['models']['GroupResponse']['properties'] = array_merge($base['models']['GroupResponse']['properties'], [
            'member' => [
                'type'        => 'array',
                'description' => 'Lists the member of the group.'
            ],
            'description' => [
                'type' => 'string',
                'description' => 'Description of the group.'
            ],
        ]);

        return $base;
    }
}