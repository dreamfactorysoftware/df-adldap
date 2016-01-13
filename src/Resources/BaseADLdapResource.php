<?php

namespace DreamFactory\Core\ADLdap\Resources;

use DreamFactory\Core\Resources\BaseRestResource;
use DreamFactory\Library\Utility\ArrayUtils;
use DreamFactory\Library\Utility\Inflector;
use DreamFactory\Core\Utility\ResourcesWrapper;
use DreamFactory\Core\Enums\ApiOptions;

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

    public static function getApiDocInfo(\DreamFactory\Core\Models\Service $service, array $resource = [])
    {
        $serviceName = strtolower($service->name);
        $capitalized = Inflector::camelize($service->name);
        $class = trim(strrchr(static::class, '\\'), '\\');
        $resourceName = strtolower(ArrayUtils::get($resource, 'name', $class));
        $pluralClass = Inflector::pluralize($class);
        $path = '/' . $serviceName . '/' . $resourceName;
        $eventPath = $serviceName . '.' . $resourceName;
        $wrapper = ResourcesWrapper::getWrapper();

        $apis = [
            $path                                       => [
                'get' => [
                    'tags'        => [$serviceName],
                    'summary'     => 'get' . $capitalized.$pluralClass . '() - Retrieve one or more ' . $pluralClass . '.',
                    'operationId' => 'get' . $capitalized.$pluralClass,
                    'event_name'  => [$eventPath . '.list'],
                    'consumes'    => ['application/json', 'application/xml', 'text/csv'],
                    'produces'    => ['application/json', 'application/xml', 'text/csv'],
                    'parameters'  => [
                        ApiOptions::documentOption(ApiOptions::FIELDS),
                    ],
                    'responses'   => [
                        '200'     => [
                            'description' => 'Response',
                            'schema'      => ['$ref' => '#/definitions/' . $pluralClass . 'Response']
                        ],
                        'default' => [
                            'description' => 'Error',
                            'schema'      => ['$ref' => '#/definitions/Error']
                        ]
                    ],
                    'description' => 'List Active Directory ' . strtolower($pluralClass)
                ],
            ],
            $path . '/{' . strtolower($class) . '_name}' => [
                'get' => [
                    'tags'        => [$serviceName],
                    'summary'     => 'get' . $capitalized.$class . '() - Retrieve one ' . $class . '.',
                    'operationId' => 'get' . $capitalized.$class,
                    'event_name'  => $eventPath . '.read',
                    'parameters'  => [
                        [
                            'name'        => strtolower($class) . 'name',
                            'description' => 'Identifier of the record to retrieve.',
                            'type'        => 'string',
                            'in'          => 'path',
                            'required'    => true,
                        ],
                        ApiOptions::documentOption(ApiOptions::FIELDS),
                    ],
                    'responses'   => [
                        '200'     => [
                            'description' => 'AD/LDAP Response',
                            'schema'      => ['$ref' => '#/definitions/' . $class . 'Response']
                        ],
                        'default' => [
                            'description' => 'Error',
                            'schema'      => ['$ref' => '#/definitions/Error']
                        ]
                    ],
                    'description' => 'Use the \'fields\' parameter to limit properties that are returned. By default, all fields are returned.',
                ],
            ],
        ];

        $models = [
            $pluralClass . 'Response' => [
                'type'       => 'object',
                'properties' => [
                    $wrapper => [
                        'type'        => 'array',
                        'description' => 'Array of records.',
                        'items'       => [
                            '$ref' => '#/definitions/' . $class . 'Response',
                        ],
                    ]
                ],
            ],
            $class . 'Response'   => [
                'type'       => 'object',
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

        return ['paths' => $apis, 'definitions' => $models];
    }
}