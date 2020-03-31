<?php

namespace DreamFactory\Core\ADLdap\Utility;

class VersionUtility
{
    public static function isPHP73orLower() {
        $version = explode('.', phpversion());
        return $version[0] <= 7 && $version[1] <= 3;
    }
}
