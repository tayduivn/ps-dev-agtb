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

namespace Sugarcrm\SugarcrmTestsUnit\modules\BusinessCenter;

use PHPUnit\Framework\TestCase;
use Sugarcrm\SugarcrmTestsUnit\TestMockHelper;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;
use Sugarcrm\Sugarcrm\Util\Uuid;

/**
 * @coversDefaultClass \BusinessCenter
 */
class BusinessCenterTest extends TestCase
{
    /**
     * BusinessCenter bean
     * @var BusinessCenter
     */
    private static $bc;

    /**
     * BusinessCenter Record view defs
     * @var array
     */
    private static $recordViewDefs = [];

    /**
     * Simply loads the environment and sets up some of the needed data
     */
    public static function setupBeforeClass()
    {
        \SugarTestHelper::init();
        static::$bc = static::getDecoratedBusinessCenterBean();
        static::$recordViewDefs = \MetaDataManager::getManager()->getModuleViewFields('BusinessCenters', 'record');
    }

    public static function tearDownAfterClass()
    {
        \MetaDataManager::resetManagers();
        \SugarTestHelper::tearDown();
    }

    /**
     * Provider for ::testHasFields
     * @return array
     */
    public function providerHasFields(): array
    {
        return [
            [
                [
                    'timezone' => [
                        'name' => 'timezone',
                        'type' => 'enum',
                    ],
                ],
            ],
            [
                [
                    'address_street' => [
                        'name' => 'address_street',
                        'type' => 'text',
                    ],
                ],
            ],
            [
                [
                    'address_city' => [
                        'name' => 'address_city',
                        'type' => 'varchar',
                    ],
                ],
            ],
            [
                [
                    'address_state' => [
                        'name' => 'address_state',
                        'type' => 'varchar',
                    ],
                ],
            ],
            [
                [
                    'address_postalcode' => [
                        'name' => 'address_postalcode',
                        'type' => 'varchar',
                    ],
                ],
            ],
            [
                [
                    'address_country' => [
                        'name' => 'address_country',
                        'type' => 'varchar',
                    ],
                ],
            ],
        ];
    }

    /**
     * @coversNothing
     * @dataProvider providerHasFields
     * @param array $fields Field definitions.
     */
    public function testHasFields(array $fields)
    {
        $fieldKey = array_keys($fields)[0];
        $this->assertArrayHasKey($fieldKey, static::$bc->field_defs);

        $fieldDef = static::$bc->field_defs[$fieldKey];
        $this->assertEquals($fields[$fieldKey]['name'], $fieldDef['name']);
        $this->assertEquals($fields[$fieldKey]['type'], $fieldDef['type']);
    }

    /**
     * Provider for ::testCheckHasFieldOnRecordView
     * @return array
     */
    public function hasFieldOnRecordViewProvider(): array
    {
        return [
            ['timezone'],
            ['address_street'],
            ['address_city'],
            ['address_state'],
            ['address_postalcode'],
            ['address_country'],
        ];
    }

    /**
     * Checks that the desired fields are on the record view.
     *
     * @coversNothing
     * @param string $fieldName Name of the field which we would like to
     *   confirm is on the record view.
     * @dataProvider hasFieldOnRecordViewProvider
     */
    public function testCheckHasFieldOnRecordView(string $fieldName)
    {
        $this->assertContains($fieldName, static::$recordViewDefs);
    }

    /**
     * Provider for ::testGetNormalizedDay
     * @return array
     */
    public function getNormalizedDayProvider()
    {
        return [
            ['day' => 'su', 'expect' => 'sunday'],
            ['day' => 'sunday', 'expect' => 'sunday'],
            ['day' => 'm', 'expect' => 'monday'],
            ['day' => 'monday', 'expect' => 'monday'],
            ['day' => 't', 'expect' => 'tuesday'],
            ['day' => 'tuesday', 'expect' => 'tuesday'],
            ['day' => 'w', 'expect' => 'wednesday'],
            ['day' => 'wednesday', 'expect' => 'wednesday'],
            ['day' => 'th', 'expect' => 'thursday'],
            ['day' => 'thursday', 'expect' => 'thursday'],
            ['day' => 'f', 'expect' => 'friday'],
            ['day' => 'friday', 'expect' => 'friday'],
            ['day' => 's', 'expect' => 'saturday'],
            ['day' => 'saturday', 'expect' => 'saturday'],
        ];
    }

    /**
     * Tests the day normalizers
     * @covers ::getNormalizedDay
     * @dataProvider getNormalizedDayProvider
     * @param string $day Day name or shortcode
     * @param string $expect Lower case weekday name
     */
    public function testGetNormalizedDay($day, $expect)
    {
        $actual = static::$bc->getNormalizedDay($day);
        $this->assertSame($expect, $actual);
    }

    /**
     * Provider for ::testIsOpen
     * @return array
     */
    public function isOpenProvider()
    {
        return [
            ['day' => 'su', 'expect' => false],
            ['day' => 'sunday', 'expect' => false],
            ['day' => 'm', 'expect' => false],
            ['day' => 'monday', 'expect' => false],
            ['day' => 't', 'expect' => true],
            ['day' => 'tuesday', 'expect' => true],
            ['day' => 'w', 'expect' => true],
            ['day' => 'wednesday', 'expect' => true],
            ['day' => 'th', 'expect' => true],
            ['day' => 'thursday', 'expect' => true],
            ['day' => 'f', 'expect' => true],
            ['day' => 'friday', 'expect' => true],
            ['day' => 's', 'expect' => true],
            ['day' => 'saturday', 'expect' => true],
        ];
    }

    /**
     * Tests whether a day is open
     * @covers ::isOpen
     * @dataProvider isOpenProvider
     * @param string $day Day name or shortcode
     * @param boolean $expect
     */
    public function testIsOpen($day, $expect)
    {
        $actual = static::$bc->isOpen($day);
        $this->assertSame($expect, $actual);
    }

    /**
     * Provider for ::getOpenTime ::getCloseTime ::getTimeForTypeOnDay
     * @return array
     */
    public function getTimeProvider()
    {
        return [
            [
                'day' => 'su',
                'expect' => [
                    'open' => null,
                    'close' => null,
                ],
            ],
            [
                'day' => 'sunday',
                'expect' => [
                    'open' => null,
                    'close' => null,
                ],
            ],
            [
                'day' => 'm',
                'expect' => [
                    'open' => null,
                    'close' => null,
                ],
            ],
            [
                'day' => 'monday',
                'expect' => [
                    'open' => null,
                    'close' => null,
                ],
            ],
            [
                'day' => 't',
                'expect' => [
                    'open' => '0930',
                    'close' => '1700',
                ],
            ],
            [
                'day' => 'tuesday',
                'expect' => [
                    'open' => '0930',
                    'close' => '1700',
                ],
            ],
            [
                'day' => 'w',
                'expect' => [
                    'open' => '0700',
                    'close' => '1930',
                ],
            ],
            [
                'day' => 'wednesday',
                'expect' => [
                    'open' => '0700',
                    'close' => '1930',
                ],
            ],
            [
                'day' => 'th',
                'expect' => [
                    'open' => '0000',
                    'close' => '2359',
                ],
            ],
            [
                'day' => 'thursday',
                'expect' => [
                    'open' => '0000',
                    'close' => '2359',
                ],
            ],
            [
                'day' => 'f',
                'expect' => [
                    'open' => '0800',
                    'close' => '1715',
                ],
            ],
            [
                'day' => 'friday',
                'expect' => [
                    'open' => '0800',
                    'close' => '1715',
                ],
            ],
            [
                'day' => 's',
                'expect' => [
                    'open' => '1045',
                    'close' => '1430',
                ],
            ],
            [
                'day' => 'saturday',
                'expect' => [
                    'open' => '1045',
                    'close' => '1430',
                ],
            ],
        ];
    }

    /**
     * Gets the time the day opens
     * @covers ::getOpenTime
     * @dataProvider getTimeProvider
     * @param string $day Day name or shortcode
     * @param array $expect
     */
    public function testGetOpenTime($day, $expect)
    {
        $actual = static::$bc->getOpenTime($day);
        $this->assertSame($expect['open'], $actual);
    }

    /**
     * Gets the time the day closes
     * @covers ::getCloseTime
     * @dataProvider getTimeProvider
     * @param string $day Day name or shortcode
     * @param array $expect
     */
    public function testGetCloseTime($day, $expect)
    {
        $actual = static::$bc->getCloseTime($day);
        $this->assertSame($expect['close'], $actual);
    }

    /**
     * Tests various elements of the hours dropdown list function
     * @covers ::getHoursDropdown
     */
    public function testGetHoursDropdown()
    {
        $data = static::$bc->getHoursDropdown();

        // We should have 24 hours
        $this->assertCount(24, $data);
        $this->assertArrayHasKey(0, $data);
        $this->assertArrayHasKey(8, $data);
        $this->assertArrayHasKey(23, $data);
        $this->assertArrayNotHasKey(24, $data);
        $this->assertSame('00', $data[0]);
        $this->assertSame('08', $data[8]);
        $this->assertSame('23', $data[23]);
    }

    /**
     * Tests various elements of the hours dropdown list function
     * @covers ::getMinutesDropdown
     */
    public function testGetMinutesDropdown()
    {
        $data = static::$bc->getMinutesDropdown();

        // We should have 60 minutes
        $this->assertCount(60, $data);
        $this->assertArrayHasKey(0, $data);
        $this->assertArrayHasKey(8, $data);
        $this->assertArrayHasKey(59, $data);
        $this->assertArrayNotHasKey(60, $data);
        $this->assertSame('00', $data[0]);
        $this->assertSame('08', $data[8]);
        $this->assertSame('59', $data[59]);
    }

    /**
     * Tests that the wrong string passed into the time unit dropdown function
     * returns an empty array. Also adds a simple count test for minutes and
     * hours. All other cases are tested above.
     * @covers ::getTimeUnitDropdown
     */
    public function testGetTimeUnitDropdown()
    {
        $this->assertSame([], static::$bc->getTimeUnitDropdown('foo'));
        $this->assertCount(24, static::$bc->getTimeUnitDropdown('hours'));
        $this->assertCount(60, static::$bc->getTimeUnitDropdown('minutes'));

        // The method DOES expect normalized inputs, so test those
        $this->assertSame([], static::$bc->getTimeUnitDropdown('Hours'));
        $this->assertSame([], static::$bc->getTimeUnitDropdown('MINUTES'));
        $this->assertSame([], static::$bc->getTimeUnitDropdown('hour'));
        $this->assertSame([], static::$bc->getTimeUnitDropdown('minute'));
    }

    /**
     * Gets the time the day opens or closes
     * @covers ::getTimeForTypeOnDay
     * @dataProvider getTimeProvider
     * @param string $day Day name or shortcode
     * @param array $expect
     */
    public function testGetTimeForTypeOnDay($day, $expect)
    {
        $actual = static::$bc->getTimeForTypeOnDay($day, 'open');
        $this->assertSame($expect['open'], $actual);

        $actual = static::$bc->getTimeForTypeOnDay($day, 'close');
        $this->assertSame($expect['close'], $actual);
    }

    /**
     * Provider for ::testGetHoursOpenForDay
     * @return array
     */
    public function getHoursOpenForDayProvider()
    {
        return [
            ['day' => 'su', 'expect' => 0.00],
            ['day' => 'sunday', 'expect' => 0.00],
            ['day' => 'm', 'expect' => 0.00],
            ['day' => 'monday', 'expect' => 0.00],
            ['day' => 't', 'expect' => 7.50],
            ['day' => 'tuesday', 'expect' => 7.50],
            ['day' => 'w', 'expect' => 12.50],
            ['day' => 'wednesday', 'expect' => 12.50],
            ['day' => 'th', 'expect' => 24.00],
            ['day' => 'thursday', 'expect' => 24.00],
            ['day' => 'f', 'expect' => 9.25],
            ['day' => 'friday', 'expect' => 9.25],
            ['day' => 's', 'expect' => 3.75],
            ['day' => 'saturday', 'expect' => 3.75],
            // Tests a garbage day that can't be normalized
            ['day' => 'foo', 'expect' => 0.00],
        ];
    }

    /**
     * Tests calculating the open hours for a day of the week
     * @covers ::getHoursOpenForDay
     * @dataProvider getHoursOpenForDayProvider
     * @param string $day Day name or shortcode
     * @param float $expect
     */
    public function testGetHoursOpenForDay($day, $expect)
    {
        $actual = static::$bc->getHoursOpenForDay($day);
        $this->assertSame($expect, $actual);
    }

    /**
     * Utility method to get the bean we need for testing
     * @return BusinessCenter
     */
    private static function getDecoratedBusinessCenterBean()
    {
        $bean = new \BusinessCenter;

        // Start with explicit open markers
        $bean->is_open_sunday = 0;
        $bean->is_open_monday = 0;
        $bean->is_open_tuesday = 1;
        $bean->is_open_wednesday = 1;
        $bean->is_open_thursday = 1;
        $bean->is_open_friday = 1;
        $bean->is_open_saturday = 1;

        // Now handle hours
        $bean->tuesday_open_hour = '09';
        $bean->tuesday_open_minutes = '30';
        $bean->tuesday_close_hour = '17';
        $bean->tuesday_close_minutes = '00';
        $bean->wednesday_open_hour = '07';
        $bean->wednesday_open_minutes = '00';
        $bean->wednesday_close_hour = '19';
        $bean->wednesday_close_minutes = '30';
        $bean->friday_open_hour = '08';
        $bean->friday_open_minutes = '00';
        $bean->friday_close_hour = '17';
        $bean->friday_close_minutes = '15';
        $bean->saturday_open_hour = '10';
        $bean->saturday_open_minutes = '45';
        $bean->saturday_close_hour = '14';
        $bean->saturday_close_minutes = '30';

        // Simulates a retrieve of the record
        $bean->fill_in_additional_detail_fields();

        return $bean;
    }
}
