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

namespace Sugarcrm\SugarcrmTestsUnit\Dav\Cal\Backend;

use Sabre\CalDAV;

/**
 * Class DataTest
 * @package Sugarcrm\SugarcrmTestsUnit\Dav\Cal\Backend
 *
 * @coversDefaultClass Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData
 */
class CalendarDataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData::deleteCalendar
     *
     * @expectedException \Sabre\DAV\Exception\Forbidden
     */
    public function testDeleteCalendar()
    {
        $sugarCalendar = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData')
                              ->disableOriginalConstructor()
                              ->setMethods(null)
                              ->getMock();

        $sugarCalendar->deleteCalendar(1);
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData::createCalendar
     *
     * @expectedException \Sabre\DAV\Exception\Forbidden
     */
    public function testCreateCalendar()
    {
        $sugarCalendar = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData')
                              ->disableOriginalConstructor()
                              ->setMethods(null)
                              ->getMock();


        $sugarCalendar->createCalendar('principals/testuser', 'testcalendar', array());
    }
}
