<?php

namespace DreamFactory\Core\ADLdap\Resources;

use DreamFactory\Core\Enums\ApiOptions;
use DreamFactory\Core\Resources\BaseRestResource;
use DreamFactory\Core\Utility\ResourcesWrapper;

class BaseADLdapResource extends BaseRestResource
{
    /** A resource identifier used in swagger doc. */
    const RESOURCE_IDENTIFIER = 'name';

    /**
     * {@inheritdoc}
     */
    protected static function getResourceIdentifier()
    {
        return static::RESOURCE_IDENTIFIER;
    }

    protected function getApiDocPaths()
    {
        $service = $this->getServiceName();
        $capitalized = camelize($service);
        $class = trim(strrchr(static::class, '\\'), '\\');
        $resourceName = strtolower($this->name);
        $pluralClass = str_plural($class);
        $path = '/' . $resourceName;

        return [
            $path                                        => [
                'get' => [
                    'summary'     => 'get' . $capitalized . $pluralClass . '() - Retrieve one or more ' . $pluralClass . '.',
                    'operationId' => 'get' . $capitalized . $pluralClass,
                    'parameters'  => [
                        ApiOptions::documentOption(ApiOptions::FIELDS),
                        [
                            'name'        => ApiOptions::FILTER,
                            'schema'      => ['type' => 'string'],
                            'in'          => 'query',
                            'description' => 'LDAP Query like filter to limit the records to retrieve.'
                        ],
                    ],
                    'responses'   => [
                        '200' => ['$ref' => '#/components/responses/' . $pluralClass . 'Response']
                    ],
                    'description' => 'List Active Directory ' . strtolower($pluralClass)
                ],
            ],
            $path . '/{' . strtolower($class) . '_name}' => [
                'get' => [
                    'summary'     => 'get' . $capitalized . $class . '() - Retrieve one ' . $class . '.',
                    'operationId' => 'get' . $capitalized . $class,
                    'parameters'  => [
                        [
                            'name'        => strtolower($class) . '_name',
                            'description' => 'Identifier of the record to retrieve.',
                            'schema'      => ['type' => 'string'],
                            'in'          => 'path',
                            'required'    => true,
                        ],
                        ApiOptions::documentOption(ApiOptions::FIELDS),
                    ],
                    'responses'   => [
                        '200' => ['$ref' => '#/components/responses/' . $class . 'Response']
                    ],
                    'description' => 'Use the \'fields\' parameter to limit properties that are returned. By default, all fields are returned.',
                ],
            ],
        ];
    }

    protected function getApiDocSchemas()
    {
        $class = trim(strrchr(static::class, '\\'), '\\');
        $pluralClass = str_plural($class);
        $wrapper = ResourcesWrapper::getWrapper();

        return [
            $class . 'Response'       => [
                'type'       => 'object',
                'properties' => [
                    'objectclass'       => [
                        'type'        => 'array',
                        'description' => 'This property identifies the class of which the object is an instance, as well as all structural or abstract superclasses from which that class is derived.',
                        'items'       => [
                            'type' => 'string'
                        ]
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
                        'description' => 'Shows objectCategory attribute.'
                    ]
                ],
            ],
            $pluralClass . 'Response' => [
                'type'       => 'object',
                'properties' => [
                    $wrapper => [
                        'type'        => 'array',
                        'description' => 'Array of records.',
                        'items'       => [
                            '$ref' => '#/components/schemas/' . $class . 'Response',
                        ],
                    ]
                ],
            ],
        ];
    }
}