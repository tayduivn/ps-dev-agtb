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
use Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Meetings as MeetingAdapter;

/**
 * MeetingsAdapterTest tests
 * Class MeetingsAdapterTest
 *
 * @coversDefaultClass \Dav\Cal\Adapter\Meetings
 */
class MeetingsAdapterTest extends Sugar_PHPUnit_Framework_TestCase
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
     * @dataProvider prepareExportProvider
     * @param array $changedFields
     * @param array $invites
     * @param array $expectedBeanData
     * @param array $expectedChangedFields
     * @covers       \Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Meetings::prepareForExport
     */
    public function testPrepareForExport($changedFields, $invites, $expectedBeanData, $expectedChangedFields)
    {
        $childExecuteResult = array();
        $idChild = 2;
        while ($idChild < 4) {
            $child = new \stdClass();
            $child->id = $idChild;
            $child->title = 'test title ' . $idChild;
            $child->repeat_parent_id = 1;
            $childExecuteResult[] = $child;
            ++$idChild;
        }
        $childQuery = $this->getMockBuilder('\stdClass')
            ->disableOriginalConstructor()
            ->setMethods(array('execute'))
            ->getMock();
        $childQuery->method('execute')->willReturn($childExecuteResult);

        $calendarEvents = $this->getMockBuilder('\CalendarEvents')
            ->disableOriginalConstructor()
            ->setMethods(array('getChildrenQuery'))
            ->getMock();
        $calendarEvents->method('getChildrenQuery')->willReturn($childQuery);

        $bean = $this->getMockBuilder('\Meeting')
            ->disableOriginalConstructor()
            ->setMethods(array('isUpdate'))
            ->getMock();
        $bean->method('isUpdate')->willReturn(true);
        $bean->id = '1';
        $bean->repeat_parent_id = '';
        $bean->module_name = 'Meetings';

        $participantHelper = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Helper\ParticipantsHelper')
            ->disableOriginalConstructor()
            ->setMethods(array('getInvitesDiff'))
            ->getMock();
        $participantHelper->method('getInvitesDiff')->willReturn($invites);

        $meetingAdapter = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Meetings')
            ->disableOriginalConstructor()
            ->setMethods(array('getParticipantHelper', 'getCalendarEvents'))
            ->getMock();
        $meetingAdapter->method('getParticipantHelper')->willReturn($participantHelper);
        $meetingAdapter->method('getCalendarEvents')->willReturn($calendarEvents);

        $preparedData = TestReflection::callProtectedMethod(
            $meetingAdapter,
            'prepareForExport',
            array($bean, $changedFields, $invites)
        );

        $this->assertEquals($expectedBeanData, $preparedData[0]);
        $this->assertEquals($expectedChangedFields, $preparedData[1]);

    }

    /**
     * @param array $dataChanges
     * @param array $expectedValues
     * @dataProvider dataGetChangesDiff
     * @covers       \Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Meetings::getFieldsDiff
     */
    public function testGetFieldsDiff(array $dataChanges, array $expectedValues)
    {
        $handlerObject = new MeetingAdapter();
        $dataDiff = TestReflection::callProtectedMethod($handlerObject, 'getFieldsDiff', array($dataChanges));
        $this->assertEquals($expectedValues, $dataDiff);
    }

    /**
     * @param array $fetchedRow
     * @param array $expectedMethod
     * @param array $expectedRow
     * @covers       \Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Meetings::getBeanFetchedRow
     * @dataProvider getBeanFetchedRowProvider
     */
    public function testGetBeanFetchedRow(array $fetchedRow, array $expectedMethod, array $expectedRow)
    {
        $adapter = $this->getMockBuilder('\Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Meetings')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $bean = $this->getMockBuilder('\SugarBean')
            ->disableOriginalConstructor()
            ->setMethods(array('retrieve'))
            ->getMock();

        $bean->fetched_row = $fetchedRow;
        $bean->expects($this->exactly($expectedMethod['count']))->method($expectedMethod['name']);

        $result = TestReflection::callProtectedMethod($adapter, 'getBeanFetchedRow', array($bean));

        $this->assertEquals($expectedRow, $result);
    }

    /**
     * @param array $changedFields
     * @param array $invites
     * @param array $expectedCalendarStrings
     * @dataProvider meetingProvider
     * @covers \Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Meetings::export
     */
    public function testExport($changedFields, $invites, $expectedCalendarStrings)
    {
        /**@var \CalDavEventCollection $eventCollection*/
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
            'name' => array('New Name', 'Old Name'),
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
        $eventCollection->getParent()->setTitle('Fake Title');
        $adapter->export($exportData, $eventCollection);
    }

    /**
     * @return array
     */
    public function prepareExportProvider()
    {
        return array(
            array(
                'changedFields' => array(
                    'name' => array(
                        'field_name' => 'name',
                        'data_type' => 'name',
                        'before' => 'Test Email Reminder',
                        'after' => 'Email Reminder Test',
                    ),
                    'date_start' => array(
                        'field_name' => 'date_start',
                        'data_type' => 'datetimecombo',
                        'before' => '2015-11-18 18:00:00',
                        'after' => '2015-11-18 18:30:00',
                    ),
                    'date_end' => array(
                        'field_name' => 'date_end',
                        'data_type' => 'datetimecombo',
                        'before' => '2015-11-18 18:00:00',
                        'after' => '2015-11-18 18:30:00',
                    ),
                    'description' => array(
                        'field_name' => 'description',
                        'data_type' => 'description',
                        'before' => '',
                        'after' => 'new Description',
                    ),
                    'repeat_count' => array(
                        'field_name' => 'repeat_count',
                        'data_type' => 'repeat_count',
                        'before' => '',
                        'after' => '2',
                    ),
                ),//changedFields
                'invites' => array(
                    'contacts' => array(
                        10 => array(
                            'status' => 'none',
                            'bean' => $this->getInvitesBeanMock(
                                '\Contact',
                                10,
                                array('email' => 'contacts10@loc.loc', 'name' => 'Contacts One')
                            )
                        )
                    ),
                    'leads' => array(
                        20 => array(
                            'status' => 'accept',
                            'bean' => $this->getInvitesBeanMock(
                                '\Lead',
                                20,
                                array('email' => 'lead20@loc.loc', 'name' => 'Lead One')
                            )
                        )
                    ),
                    'users' => array(
                        30 => array(
                            'status' => 'accept',
                            'bean' => $this->getInvitesBeanMock(
                                '\User',
                                30,
                                array('email' => 'user30@loc.loc', 'name' => 'User Foo')
                            )
                        )
                    ),
                ),//invites
                'expectedBeanData' => array(
                    'Meetings',
                    '1',//bean id
                    '',//repeat parent id
                    array(//child list
                        0 => 2,
                        1 => 3,
                    ),
                    true,//isUpdate
                ),//bean data
                'expectedChangedFields' => array(
                    'name' => array(
                        0 => 'Email Reminder Test',
                        1 => 'Test Email Reminder',
                    ),
                    'date_start' => array(
                        0 => '2015-11-18 18:30:00',
                        1 => '2015-11-18 18:00:00',
                    ),
                    'date_end' => array(
                        0 => '2015-11-18 18:30:00',
                        1 => '2015-11-18 18:00:00',
                    ),
                    'description' => array(
                        0 => 'new Description',
                    ),
                    'repeat_count' => array(
                        0 => '2',
                    ),
                ),//changed fields
            )
        );
    }

    /**
     * @return array
     */
    public function dataGetChangesDiff()
    {
        return array(
            array(
                'dataChanges' => array(
                    'name' => array(
                        'field_name' => 'name',
                        'data_type' => 'name',
                        'before' => 'Test Email Reminder',
                        'after' => 'Email Reminder Test',
                    ),
                    'date_start' => array(
                        'field_name' => 'date_start',
                        'data_type' => 'datetimecombo',
                        'before' => '2015-11-18 18:00:00',
                        'after' => '2015-11-18 18:30:00',
                    ),
                    'date_end' => array(
                        'field_name' => 'date_end',
                        'data_type' => 'datetimecombo',
                        'before' => '2015-11-18 19:00:00',
                        'after' => '2015-11-18 19:30:00',
                    ),
                    'description' => array(
                        'field_name' => 'description',
                        'data_type' => 'description',
                        'before' => '',
                        'after' => 'new Description',
                    )
                ),
                'expectedValues' => array(
                    'name' => array(
                        'Email Reminder Test',//after
                        'Test Email Reminder',
                    ),
                    'date_start' => array(
                        '2015-11-18 18:30:00',//after
                        '2015-11-18 18:00:00',

                    ),
                    'date_end' => array(
                        '2015-11-18 19:30:00', //after
                        '2015-11-18 19:00:00',
                    ),
                    'description' => array(
                        'new Description'
                    )
                )
            )
        );
    }

    /**
     * @return array
     */
    public function getBeanFetchedRowProvider()
    {
        return array(
            array(
                'fetchedRow' => array(),
                'method' => array('name' => 'retrieve', 'count' => 1),
                'expectedRow' => array(),
            ),
            array(
                'fetchedRow' => array('id' => 1, 'title' => 'Test title'),
                'method' => array('name' => 'retrieve', 'count' => 0),
                'expectedRow' => array('id' => array(1), 'title' => array('Test title')),
            ),
        );
    }

    /**
     * @return array
     */
    public function meetingProvider()
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
                    'ATTENDEE;PARTSTAT=accept;CN=Lead One:mailto:test10@test.loc',
                    'ATTENDEE;PARTSTAT=accept;CN=Lead One:mailto:test20@test.loc',
                    'ATTENDEE;PARTSTAT=accept;CN=User Foo:mailto:test30@test.loc'
                ),
            ),
        );
    }

    /**
     * @param string $moduleName
     * @param string $moduleId
     * @param array $userInfo
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getInvitesBeanMock($moduleName, $moduleId, $userInfo)
    {
        $class = new $moduleName;
        $emailAddressMock = $this->getMockBuilder('\EmailAddresses')
            ->disableOriginalConstructor()
            ->setMethods(array('getPrimaryAddress'))
            ->getMock();

        $emailAddressMock->method('getPrimaryAddress')->willReturn($userInfo['email']);

        $inviteMock = $this->getMockBuilder($moduleName)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $inviteMock->id = $moduleId;
        $inviteMock->full_name = $userInfo['name'];
        $inviteMock->module_name = $class->module_name;
        $inviteMock->emailAdresses = $emailAddressMock;
        return $inviteMock;
    }
}
