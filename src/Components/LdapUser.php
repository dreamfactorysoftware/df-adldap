<?php
/**
 * This file is part of the DreamFactory Rave(tm)
 *
 * DreamFactory Rave(tm) <http://github.com/dreamfactorysoftware/rave>
 * Copyright 2012-2014 DreamFactory Software, Inc. <support@dreamfactory.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace DreamFactory\DSP\ADLdap\Components;

use DreamFactory\DSP\ADLdap\Contracts\User as LdapUserContract;
use DreamFactory\Library\Utility\ArrayUtils;
use DreamFactory\Rave\Exceptions\NotFoundException;
use DreamFactory\Rave\Exceptions\UnauthorizedException;
use DreamFactory\DSP\ADLdap\Contracts\Provider as ADLdapProvider;

class LdapUser implements LdapUserContract
{
    /** @var ADLdapProvider */
    protected $driver;

    /** @var array */
    protected $data = [ ];

    /**
     * @param ADLdapProvider $driver
     *
     * @throws UnauthorizedException
     */
    public function __construct( ADLdapProvider $driver )
    {
        if ( !$driver->isAuthenticated() )
        {
            throw new UnauthorizedException( 'User is not authenticated.' );
        }
        $this->driver = $driver;
        $this->data = $driver->getUserInfo();
    }

    /**
     * {@inheritdoc}
     */
    public function getDomain()
    {
        $baseDn = $this->driver->getBaseDn();

        return $this->driver->getDomainName( $baseDn );
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        if ( ArrayUtils::get( $this->data, 'count' ) > 0 )
        {
            return ArrayUtils::get( $this->data, 0 );
        }
        else
        {
            throw new NotFoundException( 'No data found for Ldap User,' );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        $data = $this->getData();

        return ArrayUtils::getDeep( $data, 'uidnumber', 0 );
    }

    /**
     * {@inheritdoc}
     */
    public function getUid()
    {
        $data = $this->getData();

        return ArrayUtils::getDeep( $data, 'uid', 0 );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        $data = $this->getData();

        return ArrayUtils::getDeep( $data, 'cn', 0 );
    }

    /**
     * {@inheritdoc}
     */
    public function getFirstName()
    {
        $data = $this->getData();

        return ArrayUtils::getDeep( $data, 'givenname', 0 );
    }

    /**
     * {@inheritdoc}
     */
    public function getLastName()
    {
        $data = $this->getData();

        return ArrayUtils::getDeep( $data, 'sn', 0 );
    }

    /**
     * {@inheritdoc}
     */
    public function getEmail()
    {
        $data = $this->getData();

        return ArrayUtils::getDeep( $data, 'mail', 0 );
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
        $data = $this->getData();

        $password = ArrayUtils::getDeep( $data, 'userpassword', 0 );
        $password .= $this->getDn();
        $password .= time();
        $password = bcrypt( $password );

        return $password;
    }

    /**
     * Magic method to fetch any user value.
     *
     * @param string $method
     * @param array  $args
     *
     * @return mixed
     * @throws NotFoundException
     */
    public function __call( $method, $args )
    {
        $key = strtolower( substr( $method, 3 ) );

        $data = $this->getData();

        if ( in_array( $key, [ 'dn', 'count' ] ) )
        {
            return ArrayUtils::get( $data, $key );
        }

        return ArrayUtils::getDeep( $data, $key, 0 );
    }
}