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

use DreamFactory\DSP\ADLdap\Components\LdapUser;
use DreamFactory\DSP\ADLdap\Components\OpenLdapDriver;
use DreamFactory\Rave\Exceptions\UnauthorizedException;
use DreamFactory\Rave\Models\User;
use DreamFactory\Rave\Services\BaseRestService;
use DreamFactory\Library\Utility\ArrayUtils;
use DreamFactory\Library\Utility\Enums\Verbs;
use DreamFactory\Rave\User\Resources\Session;

class LDAP extends BaseRestService
{
    const PROVIDER_NAME = 'ldap';

    /** @var mixed  */
    protected $defaultRole;

    /** @var mixed */
    protected $config;

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
        $this->defaultRole = ArrayUtils::get($this->config, 'default_role');
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
        return ArrayUtils::get($this->config, 'host');
    }

    /**
     * @return string|null
     */
    public function getBaseDn()
    {
        return ArrayUtils::get($this->config, 'base_dn');
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
        if('session' === $this->resource)
        {
            $username = $this->getPayloadData('username');
            $password = $this->getPayloadData('password');

            $host = $this->getHost();
            $baseDn = $this->getBaseDn();

            $ldap = new OpenLdapDriver($host, $baseDn);
            $auth = $ldap->authenticate($username, $password);

            if($auth)
            {
                $ldapUser = new LdapUser($ldap);

                $user = User::createShadowLdapUser($ldapUser, $this);

                \Auth::login($user);

                return Session::getSessionData();
            }
            else
            {
                throw new UnauthorizedException('Invalid username and password provided.');
            }
        }

        return false;
    }
}