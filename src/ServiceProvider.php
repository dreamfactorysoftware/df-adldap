<?php

namespace DreamFactory\Core\ADLdap;

use DreamFactory\Core\ADLdap\Commands\ADGroupImport;
use DreamFactory\Core\ADLdap\Models\ADConfig;
use DreamFactory\Core\ADLdap\Models\LDAPConfig;
use DreamFactory\Core\ADLdap\Models\RoleADLdap;
use DreamFactory\Core\ADLdap\Services\ADLdap;
use DreamFactory\Core\ADLdap\Services\LDAP;
use DreamFactory\Core\Enums\LicenseLevel;
use DreamFactory\Core\Enums\ServiceTypeGroups;
use DreamFactory\Core\Models\SystemTableModelMapper;
use DreamFactory\Core\Services\ServiceManager;
use DreamFactory\Core\Services\ServiceType;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register()
    {
        // Add our service types.
        $this->app->resolving('df.service', function (ServiceManager $df) {
            $df->addType(
                new ServiceType([
                    'name'                  => 'adldap',
                    'label'                 => 'Active Directory',
                    'description'           => 'A service for supporting Active Directory integration',
                    'group'                 => ServiceTypeGroups::LDAP,
                    'subscription_required' => LicenseLevel::SILVER,
                    'config_handler'        => ADConfig::class,
                    'factory'               => function ($config) {
                        return new ADLdap($config);
                    }
                ])
            );
            $df->addType(
                new ServiceType([
                    'name'                  => 'ldap',
                    'label'                 => 'Standard LDAP',
                    'description'           => 'A service for supporting Open LDAP integration',
                    'group'                 => ServiceTypeGroups::LDAP,
                    'subscription_required' => LicenseLevel::SILVER,
                    'config_handler'        => LDAPConfig::class,
                    'factory'               => function ($config) {
                        return new LDAP($config);
                    }
                ])
            );
        });

        // Add our table model mapping
        $this->app->resolving('df.system.table_model_map', function (SystemTableModelMapper $df) {
            $df->addMapping('role_adldap', RoleADLdap::class);
        });
    }

    public function boot()
    {
        // add commands, https://laravel.com/docs/5.4/packages#commands
        /** @noinspection PhpUndefinedMethodInspection */
        if ($this->app->runningInConsole()) {
            $this->commands([ADGroupImport::class]);
        }

        // add migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
