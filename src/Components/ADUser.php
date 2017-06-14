<?php

namespace DreamFactory\Core\ADLdap\Components;

class ADUser extends LdapUser
{
    /**
     * {@inheritdoc}
     */
    public function getUid()
    {
        return array_get($this->data, 'samaccountname');
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return array_get($this->data, 'name');
    }

    /**
     * Generates the primary group objectSID from
     * primary group id and user objectSID.
     *
     * @return string|null
     */
    public function getPrimaryGroupObjectSID()
    {
        $primaryGroupId = $this->primarygroupid;
        $objectSID = static::binToStrSID($this->objectsid);

        if (!empty($primaryGroupId) && !empty($objectSID)) {
            $segments = explode('-', $objectSID);
            $segments[count($segments) - 1] = $primaryGroupId;
            $groupObjectSID = implode('-', $segments);

            return $groupObjectSID;
        } else {
            return null;
        }
    }

    /**
     * Converts binary ObjectSID to string.
     * Thanks to the author for this at
     * http://php.net/manual/en/function.ldap-get-values-len.php#73198
     *
     * @param $binsid
     *
     * @return string
     */
    public static function binToStrSID($binsid)
    {
        if (!empty($binsid)) {
            if (is_array($binsid) && isset($binsid[0])) {
                $binsid = $binsid[0];
            }
            $hex_sid = bin2hex($binsid);
            $rev = hexdec(substr($hex_sid, 0, 2));
            $subcount = hexdec(substr($hex_sid, 2, 2));
            $auth = hexdec(substr($hex_sid, 4, 12));
            $result = "$rev-$auth";

            for ($x = 0; $x < $subcount; $x++) {
                $subauth[$x] =
                    hexdec(static::littleEndian(substr($hex_sid, 16 + ($x * 8), 8)));
                $result .= "-" . $subauth[$x];
            }

            // Cheat by tacking on the S-
            return 'S-' . $result;
        } else {
            return null;
        }
    }

    /**
     * Converts a little-endian hex-number to one, that 'hexdec' can convert
     * Thanks to the author for this at
     * http://php.net/manual/en/function.ldap-get-values-len.php#73198
     *
     * @param $hex
     *
     * @return string
     */
    private static function littleEndian($hex)
    {
        $result = '';
        for ($x = strlen($hex) - 2; $x >= 0; $x = $x - 2) {
            $result .= substr($hex, $x, 2);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->getSamAccountname();
    }
}