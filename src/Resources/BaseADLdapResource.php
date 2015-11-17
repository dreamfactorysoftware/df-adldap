<?php

namespace DreamFactory\Core\ADLdap\Resources;

use DreamFactory\Core\Resources\BaseRestResource;
use DreamFactory\Library\Utility\Inflector;
use DreamFactory\Core\Utility\ResourcesWrapper;
use DreamFactory\Core\Enums\ApiOptions;
use DreamFactory\Core\Utility\ApiDocUtilities;

class BaseADLdapResource extends BaseRestResource
{
    /** A resource identifier used in swagger doc. */
    const RESOURCE_IDENTIFIER = 'name';

    /**
     * {@inheritdoc}
     */
    protected function getResourceIdentifier()
    {
        return static::RESOURCE_IDENTIFIER;
    }

    public function getApiDocInfo()
    {
        $path = '/' . $this->getServiceName() . '/' . $this->getFullPathName();
        $eventPath = $this->getServiceName() . '.' . $this->getFullPathName('.');
        $name = Inflector::camelize($this->name);
        $plural = Inflector::pluralize($name);
        $words = str_replace('_', ' ', $this->name);
        $pluralWords = Inflector::pluralize($words);
        $wrapper = ResourcesWrapper::getWrapper();

        $apis = [
            [
                'path'        => $path,
                'description' => "Operations for $words administration.",
                'operations'  => [
                    [
                        'method'           => 'GET',
                        'summary'          => 'get' . $plural . '() - Retrieve one or more ' . $pluralWords . '.',
                        'nickname'         => 'get' . $plural,
                        'type'             => $plural . 'Response',
                        'event_name'       => [$eventPath . '.list'],
                        'consumes'         => ['application/json', 'application/xml', 'text/csv'],
                        'produces'         => ['application/json', 'application/xml', 'text/csv'],
                        'parameters'       => [
                            ApiOptions::documentOption(ApiOptions::FIELDS),
                        ],
                        'responseMessages' => ApiDocUtilities::getCommonResponses([400, 401, 500]),
                        'notes'            => 'List Active Directory ' . strtolower($pluralWords)
                    ],
                ],
            ],
            [
                'path'        => $path . '/{id}',
                'operations'  => [
                    [
                        'method'           => 'GET',
                        'summary'          => 'get' . $name . '() - Retrieve one ' . $words . '.',
                        'nickname'         => 'get' . $name,
                        'type'             => $name . 'Response',
                        'event_name'       => $eventPath . '.read',
                        'parameters'       => [
                            [
                                'name'          => 'id',
                                'description'   => 'Identifier of the record to retrieve.',
                                'allowMultiple' => false,
                                'type'          => 'string',
                                'paramType'     => 'path',
                                'required'      => true,
                            ],
                            ApiOptions::documentOption(ApiOptions::FIELDS),
                        ],
                        'responseMessages' => ApiDocUtilities::getCommonResponses([400, 401, 500]),
                        'notes'            => 'Use the \'fields\' parameter to limit properties that are returned. By default, all fields are returned.',
                    ],
                ],
                'description' => "Operations for individual $words administration.",
            ],
        ];

        $models = [
            $plural . 'Response' => [
                'id'         => $plural . 'Response',
                'properties' => [
                    $wrapper => [
                        'type'        => 'array',
                        'description' => 'Array of records.',
                        'items'       => [
                            '$ref' => $name . 'Response',
                        ],
                    ]
                ],
            ],
            $name . 'Response'   => [
                'id'         => $name . 'Response',
                'properties' => [
                    'objectclass'       => [
                        'type'        => 'array',
                        'description' => 'This property identifies the class of which the object is an instance, as well as all structural or abstract superclasses from which that class is derived.',
                    ],
                    'cn'                => [
                        'type'        => 'string',
                        'description' => 'Common name of the object'
                    ],
                    'dn'                => [
                        'type'        => 'string',
                        'description' => 'Distinguished name of the object'
                    ],
                    'distinguishedname' => [
                        'type'        => 'string',
                        'description' => 'Distinguished name of the object'
                    ],
                    'whencreated'       => [
                        'type'        => 'string',
                        'description' => 'Date/Time when object was created'
                    ],
                    'whenchanged'       => [
                        'type'        => 'string',
                        'description' => 'Date/Time when object was changed'
                    ],
                    'objectcategory'    => [
                        'type'        => 'string',
                        'description' => 'Shows objectCagetory attribute.'
                    ]
                ],
            ],
        ];

        return ['apis' => $apis, 'models' => $models];
    }
}