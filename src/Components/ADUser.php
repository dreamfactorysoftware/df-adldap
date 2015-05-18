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

use DreamFactory\Library\Utility\ArrayUtils;

class ADUser extends LdapUser
{
    /**
     * {@inheritdoc}
     */
    public function getUid()
    {
        $data = $this->getData();

        return ArrayUtils::getDeep( $data, 'samaccountname', 0 );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return ArrayUtils::getDeep( $this->getData(), 'name', 0 );
    }
}