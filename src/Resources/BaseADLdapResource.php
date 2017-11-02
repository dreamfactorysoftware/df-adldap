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
                    'summary'     => 'Retrieve one or more ' . $pluralClass . '.',
                    'description' => 'List Active Directory ' . strtolower($pluralClass),
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
                ],
            ],
            $path . '/{' . strtolower($class) . '_name}' => [
                'get' => [
                    'summary'     => 'Retrieve one ' . $class . '.',
                    'description' => 'Use the \'fields\' parameter to limit properties that are returned. By default, all fields are returned.',
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
                ],
            ],
        ];
    }

    protected function getApiDocResponses()
    {
        $class = trim(strrchr(static::class, '\\'), '\\');
        $pluralClass = str_plural($class);

        return [
            $class . 'Response'       => [
                'description' => $class . ' Response',
                'content'     => [
                    'application/json' => [
                        'schema' => ['$ref' => '#/components/schemas/' . $class]
                    ],
                    'application/xml'  => [
                        'schema' => ['$ref' => '#/components/schemas/' . $class]
                    ],
                ],
            ],
            $pluralClass . 'Response' => [
                'description' => $pluralClass . ' Response',
                'content'     => [
                    'application/json' => [
                        'schema' => ['$ref' => '#/components/schemas/' . $class]
                    ],
                    'application/xml'  => [
                        'schema' => ['$ref' => '#/components/schemas/' . $class]
                    ],
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
            $class       => [
                'type'       => 'object',
                'properties' => [
                    'objectclass'       => [
                        'type'        => 'array',
                        'description' => 'This property identifies the class of which the object is an instance, ' .
                            'as well as all structural or abstract superclasses from which that class is derived.',
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
            $pluralClass => [
                'type'       => 'object',
                'properties' => [
                    $wrapper => [
                        'type'        => 'array',
                        'description' => 'Array of objects.',
                        'items'       => [
                            '$ref' => '#/components/schemas/' . $class,
                        ],
                    ]
                ],
            ],
        ];
    }
}