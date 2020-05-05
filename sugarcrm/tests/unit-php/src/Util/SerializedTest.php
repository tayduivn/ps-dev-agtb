<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

namespace Sugarcrm\SugarcrmTestsUnit\Util;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Util\Serialized
 */
class SerializedTest extends TestCase
{
    /**
     * @covers ::unserialize
     * @dataProvider providerTestUnserialize
     */
    public function testUnserialize($value, $default, $base64, $html, $expected)
    {
        $this->assertEquals(
            $expected,
            \Sugarcrm\Sugarcrm\Util\Serialized::unserialize($value, $default, $base64, $html)
        );
    }
    public function providerTestUnserialize()
    {
        return [
            [
                'b:0;',
                false,
                false,
                false,
                false,
            ],
            [
                'b:1;',
                false,
                false,
                false,
                true,
            ],
            [
                'i:10;',
                null,
                false,
                false,
                10,
            ],
            [
                'd:12.199999999999999;',
                null,
                false,
                false,
                12.2,
            ],
            [
                's:6:"String";',
                null,
                false,
                false,
                'String',
            ],
            [
                'a:1:{s:3:"foo";s:3:"bar";}',
                [],
                false,
                false,
                ['foo' => 'bar'],
            ],
            [
                'O:8:"stdClass":1:{s:3:"foo";s:3:"bar";}',
                null,
                false,
                false,
                false,
            ],
            [
                'a:2:{s:3:"foo";s:3:"bar";s:3:"baz";O:8:"stdClass":1:{s:3:"foo";s:3:"bar";}}',
                null,
                false,
                false,
                false,
            ],
            [
                'a:2:{s:3:"foo";s:3:"bar";s:3:"baz";O:+8:"stdClass":1:{s:3:"foo";s:3:"bar";}}',
                null,
                false,
                false,
                false,
            ],
            [
                'a:2:{s:3:"foo";s:3:"bar";s:3:"baz";O: qwerty 8:"stdClass":1:{s:3:"foo";s:3:"bar";}}',
                null,
                false,
                false,
                false,
            ],
            [
                'a:2:{s:3:"foo";s:3:"bar";s:3:"baz";O:\x008:"stdClass":1:{s:3:"foo";s:3:"bar";}}',
                null,
                false,
                false,
                false,
            ],
            [
                'a:2:{s:3:"foo";s:3:"bar";s:3:"baz";O:0x8:"stdClass":1:{s:3:"foo";s:3:"bar";}}',
                null,
                false,
                false,
                false,
            ],
            [
                'O:8:',
                null,
                false,
                false,
                null,
            ],
            [
                'O:0x8:',
                null,
                false,
                false,
                null,
            ],
            [
                'O:\x08:',
                null,
                false,
                false,
                null,
            ],
            [
                'O:0b1000:',
                null,
                false,
                false,
                null,
            ],
            [
                'YToyOntzOjE6ImYiO3M6MToibyI7czoxOiJiIjtPOjg6InN0ZENsYXNzIjoxOntzOjM6ImZvbyI7czoyOiJiYSI7fX0=',
                null,
                true,
                false,
                null,
            ],
            [
                'YToyOntzOjE6ImYiO3M6MToibyI7czoxOiJiIjtPOjg6InN0ZENsYXNzIjoxOntzOjM6ImZvbyI7czoyOiJiYSI7fX0=',
                false,
                true,
                false,
                false,
            ],
            [
                'YToxOntzOjM6ImZvbyI7czozOiJiYXIiO30=',
                [],
                true,
                false,
                ['foo' => 'bar'],
            ],
            [
                's:28:&quot;&lt;div class=&quot;link&quot;&gt;Link&lt;/div&gt;&quot;;',
                null,
                false,
                true,
                '<div class="link">Link</div>',
            ],
            [
                'czoyODomcXVvdDsmbHQ7ZGl2IGNsYXNzPSZxdW90O2xpbmsmcXVvdDsmZ3Q7TGluayZsdDsvZGl2Jmd0OyZxdW90Ozs=',
                null,
                true,
                true,
                '<div class="link">Link</div>',
            ],
        ];
    }
}
