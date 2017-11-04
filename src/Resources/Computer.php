<?php
namespace DreamFactory\Core\ADLdap\Resources;

use DreamFactory\Core\Contracts\RequestHandlerInterface;
use DreamFactory\Core\Enums\ApiOptions;
use DreamFactory\Core\Utility\ResourcesWrapper;

class Computer extends BaseADLdapResource
{
    /**
     * Name of this resource.
     */
    const RESOURCE_NAME = 'computer';

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
        $computerName = $this->resource;
        $fields = $this->request->getParameter(ApiOptions::FIELDS, ApiOptions::FIELDS_ALL);
        $filter = $this->request->getParameter(ApiOptions::FILTER);
        $attributes = [];

        if ('*' !== $fields) {
            $attributes = explode(',', $fields);
        }

        if (empty($computerName)) {
            $asList = $this->request->getParameterAsBool(ApiOptions::AS_LIST);
            if ($asList) {
                $attributes = ['cn'];
            }
            $resources = $this->provider->listComputer($attributes, $filter);
        } else {
            $computer = $this->provider->getComputerByCn($computerName);
            $resources = $computer->getData($attributes);
        }

        return ResourcesWrapper::cleanResources($resources);
    }

    protected function getApiDocSchemas()
    {
        $base = parent::getApiDocSchemas();

        $base['Computer']['properties'] =
            array_merge($base['Computer']['properties'], [
                'name'                       => [
                    'type'        => 'string',
                    'description' => 'Computer name'
                ],
                'cn'                         => [
                    'type'        => 'string',
                    'description' => 'Common name of the Computer.'
                ],
                'dn'                         => [
                    'type'        => 'string',
                    'description' => 'Distinguished name of the Computer.'
                ],
                'operatingsystem'            => [
                    'type'        => 'string',
                    'description' => 'Operating System running on the Computer'
                ],
                'operatingsystemversion'     => [
                    'type'        => 'string',
                    'description' => 'Operating System version'
                ],
                'operatingsystemservicepack' => [
                    'type'        => 'string',
                    'description' => 'Operating System service pack'
                ],
                'dnshostname'                => [
                    'type'        => 'string',
                    'description' => 'DNS Host name'
                ],
            ]);

        return $base;
    }
}