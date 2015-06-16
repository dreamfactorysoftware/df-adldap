<?php
namespace DreamFactory\Core\ADLdap\Models;

use DreamFactory\Core\Models\BaseServiceConfigModel;

class LDAPConfig extends BaseServiceConfigModel
{
    protected $table = 'ldap_config';

    protected $fillable = ['service_id', 'default_role', 'host', 'base_dn', 'account_suffix'];
}