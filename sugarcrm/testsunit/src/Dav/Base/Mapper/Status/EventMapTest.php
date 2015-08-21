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

use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * Class EventMapTest
 * @package Sugarcrm\SugarcrmTestsUnit\Dav\Base\Helper
 *
 * @coversDefaultClass Sugarcrm\Sugarcrm\Dav\Base\Mapper\Status\EventMap
 */
class EventMapTest extends \PHPUnit_Framework_TestCase
{
    public function getStatusMapProvider()
    {
        return array(
            array(
                'appListString' => array(
                    'meeting_status_dom' => array(
                        'Planned' => 'Scheduled',
                        'Held' => 'Held',
                        'Not Held' => 'Canceled',
                    ),
                ),
                'moduleDefs' => array(
                    'status' =>
                        array(
                            'options' => 'meeting_status_dom',
                        ),
                ),
                'moduleKey' => 'status',
                'mapping' => array(
                    'CANCELLED' => 'Not Held',
                    'CONFIRMED' => 'Planned',
                ),
                'result' => array(
                    'CANCELLED' => 'Not Held',
                    'CONFIRMED' => 'Planned',
                ),
            ),
            array(
                'appListString' => array(),
                'moduleDefs' => array(
                    'status' =>
                        array(
                            'options' => 'meeting_status_dom',
                        ),
                ),
                'moduleKey' => 'status',
                'mapping' => array(
                    'CANCELLED' => 'Not Held',
                    'CONFIRMED' => 'Planned',
                ),
                'result' => array(),
            ),
            array(
                'appListString' => array(
                    'meeting_status_dom' => array(
                        'Planned' => 'Scheduled',
                        'Held' => 'Held',
                        'Not Held' => 'Canceled',
                    ),
                ),
                'moduleDefs' => array(),
                'moduleKey' => 'status',
                'mapping' => array(
                    'CANCELLED' => 'Not Held',
                    'CONFIRMED' => 'Planned',
                ),
                'result' => array(),
            ),
            array(
                'appListString' => array(
                    'meeting_status_dom' => array(
                        'Planned' => 'Scheduled',
                        'Held' => 'Held',
                    ),
                ),
                'moduleDefs' => array(
                    'status' =>
                        array(
                            'options' => 'meeting_status_dom',
                        ),
                ),
                'moduleKey' => 'status',
                'mapping' => array(
                    'CANCELLED' => 'Not Held',
                    'CONFIRMED' => 'Planned',
                ),
                'result' => array(
                    'CONFIRMED' => 'Planned',
                ),
            ),
        );
    }

    /**
     * @param array $appListStrings
     * @param array $moduleDefs
     * @param string $moduleKey
     * @param array $mapping
     * @param array $expectedMapping
     *
     * @covers       \Sugarcrm\Sugarcrm\Dav\Base\Helper\StatusMapHelper::getStatusMap
     *
     * @dataProvider getStatusMapProvider
     */
    public function testGetStatusMap(
        array $appListStrings,
        array $moduleDefs,
        $moduleKey,
        array $mapping,
        array $expectedMapping
    ) {
        $mapperMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Mapper\Status\EventMap')
                           ->disableOriginalConstructor()
                           ->setMethods(array('getAppListStrings', 'getLogger', 'getBean'))
                           ->getMock();

        $eventMock = $this->getMockBuilder('\CalDavEvent')
                          ->disableOriginalConstructor()
                          ->setMethods(array('getBean'))
                          ->getMock();


        $meetingsMock = $this->getMockBuilder('Meetings')
                             ->disableOriginalConstructor()
                             ->setMethods(null)
                             ->getMock();

        $meetingsMock->module_name = 'Meetings';

        $loggerMock = $this->getMockBuilder('LoggerManager')
                           ->disableOriginalConstructor()
                           ->setMethods(array('error'))
                           ->getMock();

        $meetingsMock->field_defs = $moduleDefs;

        $mapperMock->expects($this->once())->method('getAppListStrings')->willReturn($appListStrings);
        $mapperMock->expects($this->any())->method('getLogger')->willReturn($loggerMock);

        $eventMock->expects($this->once())->method('getBean')->willReturn($meetingsMock);

        TestReflection::setProtectedValue($mapperMock, 'statusMap', $mapping);
        TestReflection::setProtectedValue($mapperMock, 'statusField', $moduleKey);

        $result = $mapperMock->getMapping($eventMock);

        $this->assertEquals($expectedMapping, $result);
    }
}
