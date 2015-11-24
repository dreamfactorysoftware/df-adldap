<?php
namespace DreamFactory\Core\ADLdap\Database\Seeds;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        Model::unguard();

        if (class_exists('DreamFactory\\Core\\ADLdap\\Database\\Seeds\\DbTableExtrasSeeder')) {
            $this->call('DreamFactory\\Core\\ADLdap\\Database\\Seeds\\DbTableExtrasSeeder');
        }
        if (class_exists('DreamFactory\\Core\\ADLdap\\Database\\Seeds\\ServiceTypeSeeder')) {
            $this->call('DreamFactory\\Core\\ADLdap\\Database\\Seeds\\ServiceTypeSeeder');
        }
    }
}