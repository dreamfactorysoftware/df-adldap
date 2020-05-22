<?php

namespace DreamFactory\Core\ADLdap\Commands;

use DreamFactory\Core\ADLdap\Models\RoleADLdap;
use DreamFactory\Core\ADLdap\Services\LDAP;
use DreamFactory\Core\Enums\ServiceTypeGroups;
use DreamFactory\Core\Exceptions\BadRequestException;
use DreamFactory\Core\Exceptions\RestException;
use DreamFactory\Core\Utility\ResourcesWrapper;
use DreamFactory\Core\Enums\Verbs;
use Illuminate\Console\Command;
use ServiceManager;

class LdapGroupImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'df:ldap-group-import
                            {service : Provide the AD/LDAP service name to use}
                            {--username= : Username to connect using a specific account}
                            {--password= : Password for username}
                            {--filter= : Optional ldap_search filter. Accepts standard LDAP query}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports LDAP groups as roles.';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (!class_exists('DreamFactory\\Core\\ADLdap\\Services\\LDAP')) {
            $this->error('Command unavailable. Please install \'dreamfactory/df-adldap\' package to use this command.');

            return;
        }

        try {
            $serviceName = $this->argument('service');
            $username = $this->option('username');
            $password = $this->option('password');
            $filter = $this->option('filter');

            /** @type LDAP $service */
            $service = ServiceManager::getService($serviceName);
            $serviceGroup = $service->getServiceTypeInfo()->getGroup();

            if ($serviceGroup !== ServiceTypeGroups::LDAP) {
                throw new BadRequestException('Invalid service name [' .
                    $serviceName .
                    ']. Please use a valid LDAP service');
            }

            if (empty($username) || empty($password)) {
                throw new BadRequestException('Please provide valid username or password. You can use -h flag.');
            }

            if (!empty($filter)) {
                $this->warn('If you don\'t have memberUid attribute configured your filter will not be taken into accout.');
            }

            $this->line('Contacting your LDAP server...');
            $service->authenticateAdminUser($username, $password);

            $this->line('Fetching LDAP groups...');
            $groups = $service->getDriver()->listGroup(['dn', 'description'], $filter);
            $roles = [];

            foreach ($groups as $group) {
                $dfRole = RoleADLdap::whereDn($group['dn'])->first();
                if (empty($dfRole)) {
                    $role = [
                        'name'                   => static::dnToRoleName($group['dn']),
                        'description'            => $group['description'],
                        'is_active'              => true,
                        'role_adldap_by_role_id' => ['dn' => $group['dn']]
                    ];

                    $this->info('|--------------------------------------------------------------------');
                    $this->info('| DN: ' . $group['dn']);
                    $this->info('| Role Name: ' . $role['name']);
                    $this->info('| Description: ' . $role['description']);
                    $this->info('|--------------------------------------------------------------------');

                    $roles[] = $role;
                }
            }

            $roleCount = count($roles);
            if ($roleCount > 0) {
                $this->warn('Total Roles to import: [' . $roleCount . ']');
                if ($this->confirm('The above roles will be imported into your DreamFactory instance based on your LDAP groups. Do you wish to continue?')) {
                    $this->line('Importing Roles...');
                    $payload = ResourcesWrapper::wrapResources($roles);
                    $result = ServiceManager::handleRequest('system', Verbs::POST, 'role', ['continue' => true], [], $payload, null, false);
                    if ($result->getStatusCode() >= 300) {
                        $this->error(print_r($result->getContent(), true));
                    } else {
                        $this->info('Successfully imported all LDAP groups as Roles.');
                    }
                } else {
                    $this->info('Aborted import process. No Roles were imported');
                }
            } else if (count($groups) > 0 && $roleCount === 0) {
                $this->info('All groups found on the LDAP server are already imported.');
            } else {
                $this->warn('No group was found on ALDAP server.');
            }
        } catch (RestException $e) {
            $this->error($e->getMessage());
            if ($this->option('verbose')) {
                $this->error(print_r($e->getContext(), true));
            }
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            $this->error($msg);
            if (strpos($msg, 'Size limit exceeded') !== false) {
                $this->error('Please use "--filter=" option to avoid exceeding size limit');
            }
        }
    }

    public static function dnToRoleName($dn)
    {
        $attributes = explode(',', $dn);
        $attValues = [];

        foreach ($attributes as $attribute) {
            $value = substr($attribute, 3);
            $attValues[] = str_replace(' ', '', $value);
        }

        $roleName = implode('+', $attValues);

        if (strlen($roleName) > 64) {
            $roleName = substr($roleName, 0, 59) . '_' . sprintf("%04d", mt_rand(1, 9999));
        }

        return $roleName;
    }
}
