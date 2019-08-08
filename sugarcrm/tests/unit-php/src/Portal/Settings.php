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

namespace Sugarcrm\SugarcrmTestsUnit\src\Portal;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Portal\Settings;

/**
 * @coversDefaultClass Settings
 */
class SettingsTest extends TestCase
{
    protected static $ps;

    public static function setupBeforeClass(): void
    {
        self::$ps = new Settings();
    }

    public static function tearDownAfterClass() : void
    {
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Portal\Settings::isServe
     */
    public function testIsServe() : void
    {
        $psMock = $this->createPartialMock(Settings, ['getSubscriptions']);

        $psMock->method('getSubscriptions')
            ->willReturn(['SUGAR_SERVE' => ['more info here']]);

        $this->assertTrue($psMock->isServe());

        $psMock->method('getSubscriptions')
            ->willReturn([]);

        $this->assertFalse($psMock->isServe());
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Portal\Settings::allowCasesForContactsWithoutAccount
     */
    public function testAllowCasesForContactsWithoutAccount() : void
    {
        $this->assertFalse(self::$ps->allowCasesForContactsWithoutAccount());
    }
}
