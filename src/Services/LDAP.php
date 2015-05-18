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

namespace DreamFactory\DSP\ADLdap\Services;

use DreamFactory\DSP\ADLdap\Components\OpenLdap;
use DreamFactory\Rave\Exceptions\UnauthorizedException;
use DreamFactory\Rave\Models\User;
use DreamFactory\Rave\Services\BaseRestService;
use DreamFactory\Library\Utility\ArrayUtils;
use DreamFactory\Library\Utility\Enums\Verbs;
use DreamFactory\DSP\ADLdap\Contracts\Provider as ADLdapProvider;
use DreamFactory\Rave\Exceptions\NotFoundException;
use DreamFactory\Rave\Utility\Session as SessionUtil;

class LDAP extends BaseRestService
{
    /** Provider name */
    const PROVIDER_NAME = 'ldap';

    /** @var mixed */
    protected $defaultRole;

    /** @var mixed */
    protected $config;

    /** @var  ADLdapProvider */
    protected $driver;

    /**
     * @param array $settings
     */
    public function __construct( $settings = [ ] )
    {
        $verbAliases = [
            Verbs::PUT   => Verbs::POST,
            Verbs::MERGE => Verbs::PATCH
        ];
        ArrayUtils::set( $settings, "verbAliases", $verbAliases );
        parent::__construct( $settings );

        $this->config = ArrayUtils::get( $settings, 'config' );
        $this->defaultRole = ArrayUtils::get( $this->config, 'default_role' );
        $this->setDriver();
    }

    /**
     * Sets the Ldap driver.
     */
    protected function setDriver()
    {
        $host = $this->getHost();
        $baseDn = $this->getBaseDn();

        $this->driver = new OpenLdap( $host, $baseDn );
    }

    /**
     * Returns the Ldap driver.
     *
     * @return ADLdapProvider
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * @return array|null
     */
    public function getDefaultRole()
    {
        return $this->defaultRole;
    }

    /**
     * @return string
     */
    public function getProviderName()
    {
        return static::PROVIDER_NAME;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getHost()
    {
        return ArrayUtils::get( $this->config, 'host' );
    }

    /**
     * @return string|null
     */
    public function getBaseDn()
    {
        return ArrayUtils::get( $this->config, 'base_dn' );
    }

    /**
     * Gets basic user session data.
     *
     * @return array
     * @throws NotFoundException
     */
    protected function handleGET()
    {
        return SessionUtil::getUserInfo();
    }

    /**
     * Handles the POST request on this service.
     *
     * @return array|bool
     * @throws UnauthorizedException
     * @throws \DreamFactory\Rave\Exceptions\BadRequestException
     * @throws \DreamFactory\Rave\Exceptions\NotFoundException
     */
    protected function handlePOST()
    {
        if ( 'session' === $this->resource )
        {
            $username = $this->getPayloadData( 'username' );
            $password = $this->getPayloadData( 'password' );

            $auth = $this->driver->authenticate( $username, $password );

            if ( $auth )
            {
                $ldapUser = $this->driver->getUser();

                $user = User::createShadowADLdapUser( $ldapUser, $this );

                \Auth::login( $user );

                return SessionUtil::getUserInfo();
            }
            else
            {
                throw new UnauthorizedException( 'Invalid username and password provided.' );
            }
        }

        return false;
    }

    /**
     * Logs out user
     *
     * @return array
     */
    protected function handleDELETE()
    {
        \Auth::logout();

        return [ 'success' => true ];
    }
}