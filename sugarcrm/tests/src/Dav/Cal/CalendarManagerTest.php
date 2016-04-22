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
use Sugarcrm\Sugarcrm\Dav\Cal\CalendarManager;

/**
 * Class CalendarManagerTest
 * @package            Sugarcrm\SugarcrmTestsUnit\Dav\Cal
 *
 * @coversDefaultClass Sugarcrm\Sugarcrm\Dav\Cal\CalendarManager
 */
class CalendarManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Actual class for address
     * @var string
     */
    protected $addressClass;
    /**
     * @inheritdoc;
     */
    protected function setUp()
    {
        parent::setUp();
        $this->addressClass = VCalendar::$propertyMap['ATTENDEE'];
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        parent::tearDown();
        CalendarManager::setPropertyMapHandler('ATTENDEE', $this->addressClass);
        CalendarManager::setValueMapHandler('CAL-ADDRESS', $this->addressClass);
    }

    /**
     * Checks VCalendar property map setting.
     *
     * @covers Sugarcrm\Sugarcrm\Dav\Cal\CalendarManager::setPropertyMapHandler
     */
    public function testSetPropertyMapHandler()
    {
        CalendarManager::setPropertyMapHandler('ATTENDEE', 'Sabre\VObject\Property\ICalendar\CalAddressTest');
        $this->assertEquals(VCalendar::$propertyMap['ATTENDEE'], 'Sabre\VObject\Property\ICalendar\CalAddressTest');
    }

    /**
     * Checks VCalendar value map setting.
     *
     * @covers Sugarcrm\Sugarcrm\Dav\Cal\CalendarManager::setValueMapHandler
     */
    public function testSetValueMapHandler()
    {
        CalendarManager::setValueMapHandler('CAL-ADDRESS', 'Sabre\VObject\Property\ICalendar\CalAddressTest');
        $this->assertEquals(VCalendar::$valueMap['CAL-ADDRESS'], 'Sabre\VObject\Property\ICalendar\CalAddressTest');
    }
}
