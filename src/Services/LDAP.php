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

use DreamFactory\Rave\Services\BaseRestService;
use DreamFactory\Library\Utility\ArrayUtils;
use DreamFactory\Library\Utility\Enums\Verbs;

class LDAP extends BaseRestService
{
    protected $defaultRole;

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

    protected function handlePOST()
    {
        if('session' === $this->resource)
        {
            $username = $this->getPayloadData('username');
            $password = $this->getPayloadData('password');
            $baseDn = ArrayUtils::get($this->config, 'base_dn');
            $fullDn = 'cn='.$username.','.$baseDn;

            $r = ldap_connect(ArrayUtils::get($this->config, 'host'));
            ldap_set_option($r, LDAP_OPT_PROTOCOL_VERSION, 3);

            $auth = ldap_bind($r, $fullDn, $password);

            if($auth)
            {
                // - Get user info
                // - Create shadow user
                // - Login with shadow user
                // - return session info
            }
            else
            {
                // - Return exception
            }
        }
    }
}