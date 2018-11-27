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

use PHPUnit\Framework\TestCase;

class CheckPHPVersionTest extends TestCase
{
    public function providerPhpVersion() : iterable
    {
        return [
            'too-old' => ['7.0.0', -1],
            'supported-but-dev' => ['7.1.0-dev', -1],
            'supported-71' => ['7.1.0', 1],
            'supported-72' => ['7.2.0', 1],
            'too-new-and-dev' => ['7.3.0-dev', -1],
            'too-new' => ['7.3.0', -1],
        ];
    }

    /**
     * @dataProvider providerPhpVersion
     * @ticket 33202
     */
    public function testPhpVersion(string $ver, int $expected_retval) : void
    {
        $this->assertEquals($expected_retval, check_php_version($ver));
    }
}
