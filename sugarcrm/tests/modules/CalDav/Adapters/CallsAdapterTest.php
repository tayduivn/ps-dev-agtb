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

require_once 'tests/SugarTestCalDavUtilites.php';

use Sugarcrm\SugarcrmTestsUnit\TestReflection;
use Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Calls as CallsAdapter;

/**
 * CalDav bean tests
 * Class CalDavTest
 *
 * @coversDefaultClass \Dav\Cal\Hadlers
 */
class CallsAdapterTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * set up new user
     */
    public function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_list_strings');
        $GLOBALS['current_user']->setPreference('timezone', 'Europe/Moscow');
    }


    /**
     * @param array $changedFields
     * @param array $invites
     * @param array $expectedCalendarStrings
     * @dataProvider callProvider
     * @covers \Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Call::export
     */
    public function testExport($changedFields, $invites, $expectedCalendarStrings)
    {
        /**@var \CalDavEventCollection $eventCollection*/
        $eventCollection = $this->getMockBuilder('\CalDavEventCollection')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $adapter = $this->getMockBuilder('\Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Calls')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $meetingBean = $this->getMockBuilder('\Call')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $meetingBean->id = 1;
        $meetingBean->module_name = 'Calls';

        $exportData = array(
            array(
                $meetingBean->module_name,
                $meetingBean->id,
                '',
                array(),
                false
            ),
            $changedFields,
            $invites,
        );

        $exportResult = $adapter->export($exportData, $eventCollection);
        $this->assertTrue($exportResult);
        $vCalendar = TestReflection::callProtectedMethod($eventCollection, 'getVCalendar', array());
        $calendarText = $vCalendar->serialize();
        foreach ($expectedCalendarStrings as $str) {
            $this->assertContains($str, $calendarText);
        }
    }


    /**
     * @expectedException \Sugarcrm\Sugarcrm\Dav\Cal\Adapter\ExportException
     */
    public function testExportException()
    {
        $changedFields = array(
            'date_start' => array('2015-11-18 18:30:00', '2015-11-18 18:00:00'),
        );
        $eventCollection = $this->getMockBuilder('\CalDavEventCollection')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $adapter = $this->getMockBuilder('\Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Meetings')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $meetingBean = $this->getMockBuilder('\Meeting')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $meetingBean->id = 1;
        $meetingBean->module_name = 'Meetings';

        $exportData = array(
            array(
                $meetingBean->module_name,
                $meetingBean->id,
                '',
                array(),
                false
            ),
            $changedFields,
            array(),
        );
        $eventCollection->getParent()->setStartDate(new \SugarDateTime('2015-11-18 14:20:00'), new DateTimeZone('UTC'));
        $adapter->export($exportData, $eventCollection);
    }


    /**
     * @return array
     */
    public function callProvider()
    {
        return array(
            array(
                'meetingData' => array(
                    'name' => array(
                        'Test Meeting',//after
                    ),
                    'date_start' => array(
                        '2015-11-18 18:30:00',//after
                    ),
                    'date_end' => array(
                        '2015-11-18 19:30:00', //after
                    ),
                    'description' => array(
                        'new Description'
                    )
                ),
                'invites' => array(
                    'added' => array(
                        array('Contacts', 10, 'accept', 'test10@test.loc', 'Lead One'),
                        array('Leads', 20, 'accept', 'test20@test.loc', 'Lead One'),
                        array('Users', 30, 'accept', 'test30@test.loc', 'User Foo')
                    ),//added
                    'deleted' => array(),
                    'changed' => array(),
                ),//invites
                'vCalendar' => array(
                    'SUMMARY:Test Meeting',
                    'DESCRIPTION:new Description',
                    'DTSTART:20151118T183000Z',
                    'DURATION:PT1H',
                    'ATTENDEE;PARTSTAT=ACCEPTED;CN=Lead One:mailto:test10@test.loc',
                    'ATTENDEE;PARTSTAT=ACCEPTED;CN=Lead One:mailto:test20@test.loc',
                    'ATTENDEE;PARTSTAT=ACCEPTED;CN=User Foo:mailto:test30@test.loc'
                ),
            ),
        );
    }
}
