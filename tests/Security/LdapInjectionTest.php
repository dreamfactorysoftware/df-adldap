<?php

namespace DreamFactory\Core\ADLdap\Tests\Security;

use PHPUnit\Framework\TestCase;

/**
 * Security: LDAP filter strings must escape user-supplied values via
 * ldap_escape(LDAP_ESCAPE_FILTER).
 *
 * Phase 2 audit found two injection sites in OpenLdap.php:
 *
 *   - getUserDn():  "($uidField=$username)"
 *     A login payload like `*)(uid=*` for $username breaks out of the
 *     intended filter and matches every entry — auth bypass.
 *
 *   - getGroups():  "(&(memberUid=$user->uid)(objectClass=posixGroup)$filter)"
 *     The user's uid attribute (returned from a prior LDAP search but still
 *     untrusted relative to the filter syntax) was concatenated raw.
 *
 * After the fix, both sites route values through ldap_escape with the
 * LDAP_ESCAPE_FILTER flag.
 */
class LdapInjectionTest extends TestCase
{
    private string $sourcePath;
    private string $contents;

    protected function setUp(): void
    {
        $this->sourcePath = __DIR__ . '/../../src/Components/OpenLdap.php';
        $this->assertFileExists($this->sourcePath);
        $this->contents = file_get_contents($this->sourcePath);
    }

    public function testGetUserDnEscapesFilterValues(): void
    {
        $start = strpos($this->contents, 'function getUserDn');
        $this->assertNotFalse($start, 'getUserDn() must exist');
        $next = strpos($this->contents, "\n    /**", $start + 10);
        $body = substr($this->contents, $start, $next === false ? null : ($next - $start));

        // Forbid the raw concatenation pattern.
        $this->assertDoesNotMatchRegularExpression(
            "/'\(' \. \\\$uidField \. '=' \. \\\$username \. '\)'/",
            $body,
            'getUserDn() must not concatenate raw $uidField + $username into the LDAP filter'
        );
        // Require ldap_escape on both values.
        $this->assertMatchesRegularExpression(
            '/ldap_escape\s*\(\s*(?:\([a-z]+\)\s*)?\$username/',
            $body,
            'getUserDn() must call ldap_escape on $username'
        );
        $this->assertMatchesRegularExpression(
            '/ldap_escape\s*\(\s*(?:\([a-z]+\)\s*)?\$uidField/',
            $body,
            'getUserDn() must call ldap_escape on $uidField'
        );
        $this->assertMatchesRegularExpression(
            '/LDAP_ESCAPE_FILTER/',
            $body,
            'getUserDn() must use LDAP_ESCAPE_FILTER mode'
        );
    }

    public function testGetGroupsEscapesUid(): void
    {
        $start = strpos($this->contents, 'function getGroups');
        $this->assertNotFalse($start, 'getGroups() must exist');
        $next = strpos($this->contents, "\n    /**", $start + 10);
        $body = substr($this->contents, $start, $next === false ? null : ($next - $start));

        $this->assertDoesNotMatchRegularExpression(
            '/memberUid=\$user->uid/',
            $body,
            'getGroups() must not interpolate $user->uid raw into the LDAP filter'
        );
        $this->assertMatchesRegularExpression(
            '/ldap_escape\s*\(\s*(?:\([a-z]+\)\s*)?\$user->uid/',
            $body,
            'getGroups() must call ldap_escape on $user->uid'
        );
    }

    /**
     * Behavioral: ldap_escape with LDAP_ESCAPE_FILTER must turn the bypass
     * payload into a benign substring.
     */
    public function testLdapEscapeFlattensBypassPayload(): void
    {
        $payload = '*)(uid=*';
        $escaped = ldap_escape($payload, '', LDAP_ESCAPE_FILTER);

        // Each metachar (* ( ) \ NUL) must be \-hex encoded.
        $this->assertStringNotContainsString('*', $escaped, 'asterisk must be escaped');
        $this->assertStringNotContainsString('(', $escaped, 'paren must be escaped');
        $this->assertStringNotContainsString(')', $escaped, 'paren must be escaped');
    }
}
