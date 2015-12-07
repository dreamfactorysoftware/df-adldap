<?php

namespace DreamFactory\Core\ADLdap\Models;

use DreamFactory\Core\Models\BaseModel;

class RoleADLdap extends BaseModel
{
    /** @type string */
    protected $table = 'role_adldap';

    /** @type string */
    protected $primaryKey = 'role_id';

    /** @type array */
    protected $fillable = ['role_id', 'dn'];

    /** @type bool */
    public $timestamps = false;
}