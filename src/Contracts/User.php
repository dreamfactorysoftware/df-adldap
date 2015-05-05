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

namespace DreamFactory\DSP\ADLdap\Contracts;

interface User
{
    /**
     * Gets LDAP domain name.
     *
     * @return string
     */
    public function getDomain();

    /**
     * Gets unique user ID.
     *
     * @return integer
     */
    public function getId();

    /**
     * Gets unique username.
     *
     * @return string
     */
    public function getUid();

    /**
     * Gets cn (common name).
     *
     * @return string
     */
    public function getName();

    /**
     * Gets givenname.
     *
     * @return string
     */
    public function getFirstName();

    /**
     * Gets sn (surname).
     *
     * @return string
     */
    public function getLastName();

    /**
     * Gets mail.
     *
     * @return string
     */
    public function getEmail();

    /**
     * Gets userpassword hash.
     *
     * @return string
     */
    public function getPassword();

}