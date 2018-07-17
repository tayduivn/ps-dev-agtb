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

namespace Sugarcrm\SugarcrmTestsUnit\inc;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass MarketingVersionHandler
 */
class MarketingVersionHandlerTest extends TestCase
{
    protected static $verHandler;

    public static function setUpBeforeClass()
    {
        self::$verHandler = new \MarketingVersionHandler;
    }

    /**
     * @dataProvider versionProvider
     * @covers getMarketingVersion
     * @param string $version
     * @param string $expect
     */
    public function testGetMarketingVersion($version, $expect)
    {
        $actual = self::$verHandler->getMarketingVersion($version);
        $this->assertEquals($expect, $actual);
    }
    public function versionProvider()
    {
        return [
            [
                'version' => '7.9.4.0',
                'expect' => '',
            ],
            [
                'version' => '7.10.2.0',
                'expect' => 'Fall \'17',
            ],
            [
                'version' => '7.11.4.1',
                'expect' => 'Winter \'18',
            ],
            [
                'version' => '8.0.0',
                'expect' => 'Spring \'18',
            ],
            [
                'version' => '8.0.3',
                'expect' => 'Spring \'18',
            ],
            [
                'version' => '8.1.0',
                'expect' => 'Summer \'18',
            ],
            [
                'version' => '8.2.0',
                'expect' => 'Fall \'18',
            ],
            [
                'version' => '8.2.0-patch2',
                'expect' => 'Fall \'18',
            ],
            [
                'version' => '8.3.0',
                'expect' => 'Winter \'19',
            ],
            [
                'version' => '11.3.0',
                'expect' => 'Winter \'22',
            ],
        ];
    }
}
