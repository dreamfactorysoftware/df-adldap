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

use DreamFactory\Rave\Exceptions\BadRequestException;

class ADLdap extends OpenLdap
{
    /** @var  string */
    protected $accountSuffix;

    /**
     * @param string      $host
     * @param string      $baseDn
     * @param string|null $accountSuffix
     */
    public function __construct( $host, $baseDn, $accountSuffix = null )
    {
        parent::__construct( $host, $baseDn );
        $this->setAccountSuffix( $accountSuffix );
    }

    /**
     * @param string|null $suffix
     */
    public function setAccountSuffix( $suffix = null )
    {
        if ( empty( $suffix ) )
        {
            $baseDn = $this->getBaseDn();
            $suffix = static::getDomainName( $baseDn );
        }

        $this->accountSuffix = $suffix;
    }

    /**
     * @return string
     */
    public function getAccountSuffix()
    {
        return $this->accountSuffix;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate( $username, $password )
    {
        if ( empty( $username ) || empty( $password ) )
        {
            throw new BadRequestException( 'No username and/or password provided.' );
        }

        $accountSuffix = $this->getAccountSuffix();

        try
        {
            $preAuth = ldap_bind( $this->connection, $username . '@' . $accountSuffix, $password );

            if ( $preAuth )
            {
                $this->dn = $this->getDn( $username, 'samaccountname' );

                $auth = ldap_bind( $this->connection, $this->dn, $password );
            }
            else
            {
                $auth = false;
            }
        }
        catch ( \Exception $e )
        {
            $auth = false;
        }

        $this->authenticated = $auth;

        return $auth;
    }

    /**
     * @return LdapUser
     */
    public function getUser()
    {
        return new ADUser( $this );
    }
}