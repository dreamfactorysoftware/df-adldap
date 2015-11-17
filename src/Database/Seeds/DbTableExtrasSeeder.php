<?php
namespace DreamFactory\Core\ADLdap\Database\Seeds;

use DreamFactory\Core\Database\Seeds\BaseModelSeeder;
use DreamFactory\Core\ADLdap\Models\RoleADLdap;
use DreamFactory\Core\Models\DbTableExtras;
use DreamFactory\Core\Models\Service;

class DbTableExtrasSeeder extends BaseModelSeeder
{
    protected $modelClass = DbTableExtras::class;

    protected $recordIdentifier = ['service_id','table'];

    protected $records = [
        [
            'table' => 'role_adldap',
            'model' => RoleADLdap::class,
        ]
    ];

    protected function getRecordExtras()
    {
        $systemServiceId = Service::whereType('system')->value('id');

        return [
            'service_id' => $systemServiceId,
        ];
    }
}
