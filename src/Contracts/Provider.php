<?php
/**
 * This file is part of the DreamFactory(tm)
 *
 * DreamFactory(tm) <http://github.com/dreamfactorysoftware/rave>
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

namespace DreamFactory\Core\ADLdap\Contracts;

use DreamFactory\Core\ADLdap\Contracts\User as LdapUser;

interface Provider
{
    /**
     * Gets the base dn.
     *
     * @return string
     */
    public function getBaseDn();

    /**
     * Gets full dn.
     *
     * @param string $username
     * @param string $uidField
     *
     * @return string
     */
    public function getDn( $username, $uidField = 'uid' );

    /**
     * Authenticates User.
     *
     * @param string $username
     * @param string $password
     *
     * @return mixed
     */
    public function authenticate( $username, $password );

    /**
     * Checks to see if connection is bound/authenticated.
     *
     * @return bool
     */
    public function isAuthenticated();

    /**
     * Gets user info.
     *
     * @return array
     */
    public function getUserInfo();

    /**
     * Gets the user object.
     *
     * @return LdapUser
     */
    public function getUser();

    /**
     * Gets the connection resource.
     *
     * @return resource
     */
    public function getConnection();

    /**
     * Gets the domain name.
     *
     * @param string $baseDn
     *
     * @return string
     */
    public static function getDomainName( $baseDn );
}