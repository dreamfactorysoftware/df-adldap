<?php
namespace DreamFactory\Core\ADLdap\Models;

use DreamFactory\Core\Components\RequireExtensions;
use DreamFactory\Core\Models\BaseServiceConfigModel;
use DreamFactory\Core\Exceptions\BadRequestException;

class LDAPConfig extends BaseServiceConfigModel
{
    use RequireExtensions;

    protected $table = 'ldap_config';

    protected $fillable = ['service_id', 'default_role', 'host', 'base_dn', 'account_suffix'];

    protected $casts = ['service_id' => 'integer', 'default_role' => 'integer'];

    public static function validateConfig($config, $create=true)
    {
        static::checkExtensions(['ldap']);

        $validator = static::makeValidator($config, [
            'default_role' => 'required',
            'host'         => 'required',
            'base_dn'      => 'required'
        ], $create);

        if ($validator->fails()) {
            $messages = $validator->messages()->getMessages();
            throw new BadRequestException('Validation failed.', null, null, $messages);
        }

        return true;
    }
}