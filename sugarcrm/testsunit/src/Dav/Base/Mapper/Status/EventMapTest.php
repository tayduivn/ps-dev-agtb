<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

namespace Sugarcrm\SugarcrmTestsUnit\Dav\Base\Helper;

/**
 * Class EventMapTest
 * @package Sugarcrm\SugarcrmTestsUnit\Dav\Base\Helper
 *
 * @coversDefaultClass Sugarcrm\Sugarcrm\Dav\Base\Mapper\Status\EventMap
 */
class EventMapTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Provider for testGetCalDavValue.
     *
     * @return array
     */
    public function fromBeanProvider()
    {
        return array(
            array(
                'beanStatus' => 'Held',
                'calDavStatus' => 'CANCELLED',
                'expectedStatus' => 'CONFIRMED',
            ),
            array(
                'beanStatus' => 'Planned',
                'calDavStatus' => null,
                'expectedStatus' => 'CONFIRMED',
            ),
            array(
                'beanStatus' => 'Not Held',
                'calDavStatus' => 'CONFIRMED',
                'expectedStatus' => 'CANCELLED',
            ),
            array(
                'beanStatus' => null,
                'calDavStatus' => null,
                'expectedStatus' => null,
            ),
        );
    }

    /**
     * Provider for testGetSugarValue.
     *
     * @return array
     */
    public function fromCalDavProvider()
    {
        return array(
            array(
                'calDavStatus' => 'CONFIRMED',
                'beanStatus' => 'Not Held',
                'expectedStatus' => 'Planned',
            ),
            array(
                'calDavStatus' => 'CONFIRMED',
                'beanStatus' => 'Held',
                'expectedStatus' => 'Held',
            ),
            array(
                'calDavStatus' => 'CANCELLED',
                'beanStatus' => null,
                'expectedStatus' => 'Not Held',
            ),
            array(
                'calDavStatus' => null,
                'beanStatus' => null,
                'expectedStatus' => null,
            ),
        );
    }

    /**
     * Test convert event status from Bean to CalDav.
     *
     * @param string $beanStatus
     * @param string|null $calDavStatus
     * @param string $expectedStatus
     *
     * @covers Sugarcrm\Sugarcrm\Dav\Base\Mapper\Status\EventMap::getCalDavValue
     * @dataProvider fromBeanProvider
     */
    public function testGetCalDavValue($beanStatus, $calDavStatus, $expectedStatus)
    {
        $mapMock = $this->getMock('Sugarcrm\Sugarcrm\Dav\Base\Mapper\Status\EventMap', null);
        $this->assertEquals($expectedStatus, $mapMock->getCalDavValue($beanStatus, $calDavStatus));
    }

    /**
     * Test convert event status from CalDav to Bean.
     *
     * @param string $calDavStatus
     * @param string|null $beanStatus
     * @param string $expectedStatus
     *
     * @covers Sugarcrm\Sugarcrm\Dav\Base\Mapper\Status\EventMap::getSugarValue
     * @dataProvider fromCalDavProvider
     */
    public function testGetSugarValue($calDavStatus, $beanStatus, $expectedStatus)
    {
        $mapMock = $this->getMock('Sugarcrm\Sugarcrm\Dav\Base\Mapper\Status\EventMap', null);
        $this->assertEquals($expectedStatus, $mapMock->getSugarValue($calDavStatus, $beanStatus));
    }
}
