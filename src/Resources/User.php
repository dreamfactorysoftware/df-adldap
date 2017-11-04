<?php
namespace DreamFactory\Core\ADLdap\Resources;

use DreamFactory\Core\ADLdap\Contracts\Provider;
use DreamFactory\Core\Contracts\RequestHandlerInterface;
use DreamFactory\Core\Enums\ApiOptions;
use DreamFactory\Core\Utility\ResourcesWrapper;

class User extends BaseADLdapResource
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
    }

    /**
     * Handles all GET requests.
     *
     * @return array
     * @throws \DreamFactory\Core\Exceptions\NotFoundException
     */
    protected function handleGET()
    {
        $this->parent->authenticateAdminUser();
        $username = $this->resource;
        $fields = $this->request->getParameter(ApiOptions::FIELDS, ApiOptions::FIELDS_ALL);
        $filter = $this->request->getParameter(ApiOptions::FILTER);
        $attributes = [];

        if ('*' !== $fields) {
            $attributes = explode(',', $fields);
        }

        if (empty($username)) {
            $asList = $this->request->getParameterAsBool(ApiOptions::AS_LIST);
            if ($asList) {
                $attributes = ['samaccountname'];
            }
            $resources = $this->provider->listUser($attributes, $filter);
        } else {
            $user = $this->provider->getUserByUserName($username);
            $resources = $user->getData($attributes);
        }

        return ResourcesWrapper::cleanResources($resources);
    }

    protected function getApiDocSchemas()
    {
        $base = parent::getApiDocSchemas();

        $base['User']['properties'] =
            array_merge($base['User']['properties'], [
                'sn'             => [
                    'type'        => 'string',
                    'description' => 'Surname of the user.'
                ],
                'givenname'      => [
                    'type'        => 'string',
                    'description' => 'First name of the user.'
                ],
                'memberof'       => [
                    'type'        => 'array',
                    'description' => 'Lists the groups (dn) this user is a member of.',
                    'items'       => [
                        'type' => 'string'
                    ]
                ],
                'name'           => [
                    'type'        => 'string',
                    'description' => 'Full name of the user.'
                ],
                'samaccountname' => [
                    'type'        => 'string',
                    'description' => 'User login name.'
                ]
            ]);

        return $base;
    }
}