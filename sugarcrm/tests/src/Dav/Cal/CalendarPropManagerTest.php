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

namespace Sugarcrm\SugarcrmTests\Dav\Cal;

use Sabre\VObject\Component\VCalendar;

/**
 * Class CalendarPropManagerTest
 * @package            Sugarcrm\SugarcrmTestsUnit\Dav\Cal
 *
 * @coversDefaultClass Sugarcrm\Sugarcrm\Dav\Cal\CalendarPropManager
 */
class CalendarPropManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Sugarcrm\Sugarcrm\Dav\Cal\CalendarPropManager::setPropertyMapHandler
     */
    public function testSetPropertyMapHandler()
    {
        $managerMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Cal\CalendarPropManager')
                            ->setMethods(null)
                            ->getMock();
        $managerMock->setPropertyMapHandler('ATTENDEE', 'Sabre\\VObject\\Property\\ICalendar\\CalAddressTest');
        $this->assertEquals(VCalendar::$propertyMap['ATTENDEE'], 'Sabre\\VObject\\Property\\ICalendar\\CalAddressTest');
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Dav\Cal\CalendarPropManager::setValueMapHandler
     */
    public function testSetValueMapHandler()
    {
        $managerMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Cal\CalendarPropManager')
                            ->setMethods(null)
                            ->getMock();
        $managerMock->setValueMapHandler('CAL-ADDRESS', 'Sabre\\VObject\\Property\\ICalendar\\CalAddressTest');
        $this->assertEquals(VCalendar::$valueMap['CAL-ADDRESS'], 'Sabre\\VObject\\Property\\ICalendar\\CalAddressTest');
    }
}
