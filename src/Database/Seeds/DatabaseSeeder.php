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

namespace DreamFactory\DSP\ADLdap\Database\Seeds;

use Illuminate\Database\Seeder;
use DreamFactory\Rave\Models\ServiceType;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        if ( !ServiceType::whereName( 'adldap' )->exists() )
        {
            // Add the service type
            ServiceType::create(
                [
                    'name'           => 'adldap',
                    'class_name'     => "DreamFactory\\DSP\\ADLdap\\Services\\ADLdap",
                    'config_handler' => "DreamFactory\\DSP\\ADLdap\\Models\\LDAPConfig",
                    'label'          => 'adLdap integration',
                    'description'    => 'A service for supporting adLdap integration',
                    'group'          => 'ldap',
                    'singleton'      => 1
                ]
            );
            $this->command->info( 'adldap service type seeded!' );
        }

        if ( !ServiceType::whereName( 'ldap' )->exists() )
        {
            // Add the service type
            ServiceType::create(
                [
                    'name'           => 'ldap',
                    'class_name'     => "DreamFactory\\DSP\\ADLdap\\Services\\LDAP",
                    'config_handler' => "DreamFactory\\DSP\\ADLdap\\Models\\LDAPConfig",
                    'label'          => 'LDAP integration',
                    'description'    => 'A service for supporting OpenLdap integration',
                    'group'          => 'ldap',
                    'singleton'      => 1
                ]
            );
            $this->command->info( 'ldap service type seeded!' );
        }
    }
}