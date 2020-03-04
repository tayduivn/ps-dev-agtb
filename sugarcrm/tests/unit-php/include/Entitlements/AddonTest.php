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

namespace Sugarcrm\SugarcrmTestsUnit\inc\Entitlements;

use Exception;
use PHPUnit\Framework\TestCase;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;
use Sugarcrm\Sugarcrm\Entitlements\Addon;

/**
 * Class AddonTest
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Entitlements\Addon
 */
class AddonTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::parse
     * @covers ::__get
     *
     * @dataProvider addonProvider
     */
    public function testGetData($id, $data, $expected)
    {
        $addon = new Addon($id, $data);
        $this->assertSame($expected['id'], $addon->id);
        $this->assertSame($expected['quantity'], $addon->quantity);
        $this->assertSame($expected, TestReflection::getProtectedValue($addon, 'data'));
        $this->assertEmpty($addon->xyz);
    }

    public function addonProvider()
    {
        return [
            [
                '11d7e3f8-ed89-f588-e9af-4dbf44a9b207',
                [
                    'quantity' => '150',
                    'product_name' => 'iPad with offline sync',
                    'expiration_date' => 1898582400,
                ],
                [
                    'id' => '11d7e3f8-ed89-f588-e9af-4dbf44a9b207',
                    'quantity' => '150',
                    'product_name' => 'iPad with offline sync',
                    'expiration_date' => 1898582400,
                ],
            ],
        ];
    }

    /**
     * @covers ::__construct
     * @covers ::parse
     *
     * @dataProvider addonExceptionProvider
     */
    public function testGetDataException($id, $data)
    {
        $this->expectException(Exception::class);
        new Addon($id, $data);
    }

    public function addonExceptionProvider()
    {
        return [
            [
                '',
                [
                    'quantity' => '150',
                    'product_name' => 'iPad with offline sync',
                    'expiration_date' => 1898582400,
                ],
            ],
        ];
    }
}
