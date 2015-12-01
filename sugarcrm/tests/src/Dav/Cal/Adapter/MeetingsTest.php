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

namespace Sugarcrm\SugarcrmTests\Dav\Base\Helper;

use Sugarcrm\SugarcrmTestsUnit\TestReflection;
use Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Meetings as MeetingAdapter;

/**
 * Class ParticipantsHelperTest
 * @package            Sugarcrm\SugarcrmTestsUnit\Dav\Base\Helper
 *
 * @coversDefaultClass Sugarcrm\Sugarcrm\Dav\Base\Helper\ParticipantsHelper
 */
class MeetingsTest extends \PHPUnit_Framework_TestCase
{
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
                        'before' => '2015-11-18 18:00:00',
                        'after' => '2015-11-18 18:30:00',
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
                        '2015-11-18 18:30:00', //after
                        '2015-11-18 18:00:00',
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
