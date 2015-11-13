<?php
namespace DreamFactory\Core\ADLdap\Resources;

use DreamFactory\Core\Enums\ApiOptions;
use DreamFactory\Core\Resources\BaseRestResource;
use DreamFactory\Core\ADLdap\Contracts\Provider;
use DreamFactory\Core\Contracts\RequestHandlerInterface;
use DreamFactory\Core\Utility\ResourcesWrapper;

class User extends BaseRestResource
{
    /**
     * Name of this resource.
     */
    const RESOURCE_NAME = 'user';

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
     * @throws \DreamFactory\Core\Exceptions\NotFoundException
     */
    protected function handleGET()
    {
        $username = $this->resource;
        $fields = $this->request->getParameter(ApiOptions::FIELDS, ApiOptions::FIELDS_ALL);
        $attributes = [];

        if ('*' !== $fields) {
            $attributes = explode(',', $fields);
        }

        if (empty($username)) {
            $asList = $this->request->getParameterAsBool(ApiOptions::AS_LIST);
            if ($asList) {
                $attributes = ['samaccountname'];
            }
            $resources = $this->provider->listUser($attributes);
        } else {
            $user = $this->provider->getUserByUserName($username);
            $resources = $user->getData($attributes);
        }

        return ResourcesWrapper::cleanResources($resources);
    }
}