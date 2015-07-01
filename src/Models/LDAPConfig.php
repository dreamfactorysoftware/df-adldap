<?php
namespace DreamFactory\Core\ADLdap\Models;

use DreamFactory\Core\Models\BaseServiceConfigModel;
use DreamFactory\Core\Exceptions\BadRequestException;

class LDAPConfig extends BaseServiceConfigModel
{
    protected $table = 'ldap_config';

    protected $fillable = ['service_id', 'default_role', 'host', 'base_dn', 'account_suffix'];

    public static function validateConfig($config)
    {
        $validator = \Validator::make($config, [
            'default_role' => 'required',
            'host'         => 'required',
            'base_dn'      => 'required'
        ]);

        if ($validator->fails()) {
            $messages = $validator->messages()->getMessages();
            throw new BadRequestException('Validation failed.', null, null, $messages);
        }

        return true;
    }
}