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

namespace Sugarcrm\SugarcrmTestsUnit\modules\OutboundEmail\clients\base\api;

/**
 * @coversDefaultClass \OutboundEmailFilterApi
 */
class OutboundEmailFilterApiTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::registerApiRest
     */
    public function testRegisterApiRest()
    {
        $api = new \OutboundEmailFilterApi();
        $endpoints = $api->registerApiRest();

        $path = implode('/', $endpoints['filterModuleGet']['path']);
        $this->assertEquals('OutboundEmail/filter', $path);

        $path = implode('/', $endpoints['filterModuleAll']['path']);
        $this->assertEquals('OutboundEmail', $path);

        $path = implode('/', $endpoints['filterModuleAllCount']['path']);
        $this->assertEquals('OutboundEmail/count', $path);

        $path = implode('/', $endpoints['filterModulePost']['path']);
        $this->assertEquals('OutboundEmail/filter', $path);

        $path = implode('/', $endpoints['filterModulePostCount']['path']);
        $this->assertEquals('OutboundEmail/filter/count', $path);

        $path = implode('/', $endpoints['filterModuleCount']['path']);
        $this->assertEquals('OutboundEmail/filter/count', $path);
    }

    public function checkMaxListLimitProvider()
    {
        return [
            [10, 10],
            [100, 100],
            [0, -1],
            [-1, -1],
            [-100, -1],
        ];
    }

    /**
     * @covers ::checkMaxListLimit
     * @dataProvider checkMaxListLimitProvider
     * @param $limit
     * @param $expected
     */
    public function testCheckMaxListLimit($limit, $expected)
    {
        $api = new \OutboundEmailFilterApi();
        $actual = $api->checkMaxListLimit($limit);
        $this->assertSame($expected, $actual);
    }
}
