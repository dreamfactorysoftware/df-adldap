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

class OpenLdapDriver
{
    /** @var resource  */
    protected $connection;

    /** @var  string */
    protected $baseDn;

    /** @var  string */
    protected $dn;

    /** @var array  */
    protected $data = [];

    /**
     * @param $host
     * @param $baseDn
     */
    public function __construct($host, $baseDn)
    {
        $connection = ldap_connect($host);
        ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, 3);

        $this->connection = $connection;
        $this->baseDn = $baseDn;
    }

    /**
     * @return string
     */
    public function getBaseDn()
    {
        return $this->baseDn;
    }

    /**
     * Performs user authentication.
     *
     * @param $username
     * @param $password
     *
     * @return bool
     * @throws BadRequestException
     */
    public function authenticate($username, $password)
    {
        if(empty($username) || empty($password))
        {
            throw new BadRequestException('No username and/or password provided.');
        }

        $dn = static::getDn($username, $this->baseDn);

        try
        {
            $auth = ldap_bind( $this->connection, $dn, $password );
        }
        catch(\Exception $e)
        {
            $auth = false;
        }

        if($auth)
        {
            $this->dn = $dn;
        }

        return $auth;
    }

    /**
     * Checks to see if the instance is authenticated.
     *
     * @return bool
     */
    public function isAuthenticated()
    {
        if(!empty($this->dn))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * Reads all objects.
     *
     * @return array
     */
    public function readAll()
    {
        $rs = ldap_read($this->connection, $this->dn, "(objectclass=*)");
        $this->data = ldap_get_entries($this->connection, $rs);

        return $this->data;
    }

    /**
     * Returns connection resource.
     *
     * @return resource
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Fetches domain name from base DN.
     *
     * @param $baseDn
     *
     * @return string
     */
    public static function getDomainName($baseDn)
    {
        $baseDn = str_replace('DC=', 'dc=', $baseDn);
        $baseDn = substr($baseDn, strpos($baseDn, 'dc='));
        list($dc1, $dc2) = explode(',', $baseDn);

        $dc1 = substr($dc1, strpos($dc1, '=')+1);
        $dc2 = substr($dc2, strpos($dc2, '=')+1);

        $domain = $dc1.'.'.$dc2;

        return $domain;
    }

    /**
     * Gets DN
     *
     * @param $username
     * @param $baseDn
     *
     * @return string
     */
    public static function getDn($username, $baseDn)
    {
        return 'uid='.$username.','.$baseDn;
    }
}