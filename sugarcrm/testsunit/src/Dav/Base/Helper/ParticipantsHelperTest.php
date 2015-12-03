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

use Sabre\VObject;
use Sugarcrm\Sugarcrm\Dav;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * Class ParticipantsHelperTest
 * @package            Sugarcrm\SugarcrmTestsUnit\Dav\Base\Helper
 *
 * @coversDefaultClass Sugarcrm\Sugarcrm\Dav\Base\Helper\ParticipantsHelper
 */
class ParticipantsHelperTest extends \PHPUnit_Framework_TestCase
{
    protected function getEventTemplateObject($template)
    {
        $calendarData = file_get_contents(dirname(__FILE__) . '/EventsTemplates/' . $template . '.ics');
        $vEvent = VObject\Reader::read($calendarData);

        return $vEvent->VEVENT->ATTENDEE;
    }

    public function prepareForSugarProvider()
    {
        return array(
            array(
                'vCalendar' => $this->getEventTemplateObject('vevent'),
                'ids' => array(1, 2, 3, 4),
                'result' => array(
                    'Users' => array(
                        '1' => array(
                            'email' => 'sally@example.com',
                            'accept_status' => 'none',
                            'cn' => '',
                            'role' => 'REQ-PARTICIPANT',
                        ),
                        '2' => array(
                            'email' => 'test@test.com',
                            'accept_status' => 'accept',
                            'cn' => 'Test Test',
                            'role' => 'CHAIR',
                        ),
                    ),
                    'Contacts' => array(
                        '3' => array(
                            'email' => 'test1@test.com',
                            'accept_status' => 'decline',
                            'cn' => 'Test1 Test1',
                            'role' => 'OPT-PARTICIPANT',
                        ),
                    ),
                    'Leads' => array(
                        '4' => array(
                            'email' => 'test3@test.com',
                            'accept_status' => 'decline',
                            'cn' => 'Test3 Test3',
                            'role' => 'OPT-PARTICIPANT',
                        ),
                    )
                ),
            ),
        );
    }

    public function prepareForDavProvider()
    {
        return array(
            array(
                'davResult' => array(
                    'Users' => array(
                        '2a' => array(
                            'email' => 'test@test.com',
                            'accept_status' => 'accept',
                            'cn' => 'Test Test',
                            'role' => 'CHAIR',
                        ),
                        '3a' => array(
                            'email' => 'test1@test.com',
                            'accept_status' => 'decline',
                            'cn' => 'Test1 Test1',
                            'role' => 'OPT-PARTICIPANT',
                        ),
                    ),
                    'Leads' => array(
                        '4a' => array(
                            'email' => 'test3@test.com',
                            'accept_status' => 'accept',
                            'cn' => 'Test Test',
                            'role' => 'CHAIR',
                        ),
                    ),

                ),
                'usersResult' => array(
                    '2a' => array(
                        'email' => 'test@test.com',
                        'accept_status' => 'accept',
                        'cn' => 'Test Test',
                        'role' => 'CHAIR',
                    ),
                    '3a' => array(
                        'email' => 'test1@test.com',
                        'accept_status' => 'decline',
                        'cn' => 'Test1 Test1',
                        'role' => 'OPT-PARTICIPANT',
                    ),
                ),
                'leadsResult' => array(
                    '4a' => array(
                        'email' => 'test3@test.com',
                        'accept_status' => 'accept',
                        'cn' => 'Test Test',
                        'role' => 'CHAIR',
                    ),
                    '5a' => array(
                        'email' => 'test4@test.com',
                        'accept_status' => 'accept',
                        'cn' => 'test4 test4',
                        'role' => 'CHAIR',
                    ),
                ),
                'convertResult' => array(
                    Dav\Base\Constants::PARTICIPIANT_ADDED => array(
                        'mailto:test4@test.com' => array(
                            'PARTSTAT' => 'ACCEPTED',
                            'CN' => 'test4 test4',
                            'ROLE' => 'REQ-PARTICIPANT',
                            'davLink' => null,
                            'RSVP' => 'TRUE',
                            'X-SUGARUID' => '5a',
                            'X-SUGAR-MODULE' => 'Leads',
                        ),
                    ),
                ),
            ),
            array(
                'davResult' => array(),
                'usersResult' => array(),
                'leadsResult' => array(),
                'convertResult' => array(),
            ),
            array(
                'davResult' => array(),
                'usersResult' => array(
                    '2a' => array(
                        'email' => 'test@test.com',
                        'accept_status' => 'accept',
                        'cn' => 'Test Test',
                        'role' => null,
                    ),
                    '3a' => array(
                        'email' => 'test1@test.com',
                        'accept_status' => 'decline',
                        'cn' => 'Test1 Test1',
                        'role' => null,
                    ),
                ),
                'leadsResult' => array(),
                'convertResult' => array(
                    Dav\Base\Constants::PARTICIPIANT_ADDED => array(
                        'mailto:test@test.com' => array(
                            'PARTSTAT' => 'ACCEPTED',
                            'CN' => 'Test Test',
                            'ROLE' => 'REQ-PARTICIPANT',
                            'davLink' => null,
                            'RSVP' => 'TRUE',
                            'X-SUGARUID' => '2a',
                            'X-SUGAR-MODULE' => 'Users',

                        ),
                        'mailto:test1@test.com' => array(
                            'PARTSTAT' => 'DECLINED',
                            'CN' => 'Test1 Test1',
                            'ROLE' => 'REQ-PARTICIPANT',
                            'davLink' => null,
                            'RSVP' => 'TRUE',
                            'X-SUGARUID' => '3a',
                            'X-SUGAR-MODULE' => 'Users',
                        ),
                    ),
                ),
            ),
            array(
                'davResult' => array(
                    'Users' => array(
                        '2a' => array(
                            'email' => 'test@test.com',
                            'accept_status' => 'accept',
                            'cn' => 'Test Test',
                            'role' => 'CHAIR',
                        ),
                        '3a' => array(
                            'email' => 'test1@test.com',
                            'accept_status' => 'decline',
                            'cn' => 'Test1 Test1',
                            'role' => 'OPT-PARTICIPANT',
                        ),
                    ),
                ),
                'usersResult' => array(),
                'leadsResult' => array(),
                'convertResult' => array(
                    Dav\Base\Constants::PARTICIPIANT_DELETED => array(
                        'mailto:test@test.com' => array(
                            'PARTSTAT' => null,
                            'CN' => null,
                            'ROLE' => '',
                            'davLink' => null,
                            'RSVP' => 'TRUE',
                            'X-SUGARUID' => '2a',
                            'X-SUGAR-MODULE' => 'Users',
                        ),
                        'mailto:test1@test.com' => array(
                            'PARTSTAT' => null,
                            'CN' => null,
                            'ROLE' => '',
                            'davLink' => null,
                            'RSVP' => 'TRUE',
                            'X-SUGARUID' => '3a',
                            'X-SUGAR-MODULE' => 'Users',
                        ),
                    ),
                ),
            ),
            array(
                'davResult' => array(
                    'Users' => array(
                        '1a' => array(
                            'email' => 'test0@test.com',
                            'accept_status' => 'accept',
                            'cn' => 'Test Test',
                            'role' => 'CHAIR',
                        ),
                        '2a' => array(
                            'email' => 'test@test.com',
                            'accept_status' => 'accept',
                            'cn' => 'Test Test',
                            'role' => 'CHAIR',
                        ),
                        '3a' => array(
                            'email' => 'test1@test.com',
                            'accept_status' => 'decline',
                            'cn' => 'Test1 Test1',
                            'role' => 'OPT-PARTICIPANT',
                        ),
                        '4a' => array(
                            'email' => 'test4@test.com',
                            'accept_status' => 'decline',
                            'cn' => 'Test1 Test1',
                            'role' => 'OPT-PARTICIPANT',
                        ),
                        '14a' => array(
                            'email' => 'test14@test.com',
                            'accept_status' => 'decline',
                            'cn' => 'Test1 Test1',
                            'role' => 'OPT-PARTICIPANT',
                        ),
                    ),
                ),
                'usersResult' => array(
                    '1a' => array(
                        'email' => 'test10@test.com',
                        'accept_status' => 'decline',
                        'cn' => 'Test1 Test1',
                        'role' => 'OPT-PARTICIPANT',
                    ),
                    '3a' => array(
                        'email' => 'test2@test.com',
                        'accept_status' => 'decline',
                        'cn' => 'Test1 Test1',
                        'role' => 'OPT-PARTICIPANT',
                    ),
                ),
                'leadsResult' => array(
                    '4a' => array(
                        'email' => 'test4@test.com',
                        'accept_status' => 'decline',
                        'cn' => 'Test1 Test1',
                        'role' => 'OPT-PARTICIPANT',
                    ),
                    '5a' => array(
                        'email' => 'test5@test.com',
                        'accept_status' => 'none',
                        'cn' => 'Test5 Test5',
                        'role' => null,
                    ),
                    '14a' => array(
                        'email' => 'test14@test.com',
                        'accept_status' => 'decline',
                        'cn' => 'Test1 Test1',
                        'role' => 'OPT-PARTICIPANT',
                    ),
                ),
                'convertResult' => array(
                    Dav\Base\Constants::PARTICIPIANT_DELETED => array(
                        'mailto:test@test.com' => array(
                            'PARTSTAT' => null,
                            'CN' => null,
                            'ROLE' => '',
                            'davLink' => null,
                            'X-SUGARUID' => '2a',
                            'RSVP' => 'TRUE',
                            'X-SUGAR-MODULE' => 'Users',
                        ),
                    ),
                    Dav\Base\Constants::PARTICIPIANT_MODIFIED => array(
                        'mailto:test10@test.com' => array(
                            'PARTSTAT' => 'DECLINED',
                            'CN' => 'Test Test',
                            'ROLE' => 'CHAIR',
                            'davLink' => 'mailto:test0@test.com',
                            'X-SUGARUID' => '1a',
                            'RSVP' => 'TRUE',
                            'X-SUGAR-MODULE' => 'Users',
                        ),
                        'mailto:test2@test.com' => array(
                            'PARTSTAT' => 'DECLINED',
                            'CN' => 'Test1 Test1',
                            'ROLE' => 'OPT-PARTICIPANT',
                            'davLink' => 'mailto:test1@test.com',
                            'X-SUGARUID' => '3a',
                            'RSVP' => 'TRUE',
                            'X-SUGAR-MODULE' => 'Users',
                        ),
                    ),
                    Dav\Base\Constants::PARTICIPIANT_ADDED => array(
                        'mailto:test5@test.com' => array(
                            'PARTSTAT' => 'NEEDS-ACTION',
                            'CN' => 'Test5 Test5',
                            'ROLE' => 'REQ-PARTICIPANT',
                            'davLink' => null,
                            'X-SUGARUID' => '5a',
                            'RSVP' => 'TRUE',
                            'X-SUGAR-MODULE' => 'Leads',
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * @param VObject\Property\ICalendar\CalAddress $calAddress
     * @param array $userIds
     * @param array $expectedResult
     *
     * @covers       Sugarcrm\Sugarcrm\Dav\Base\Helper\ParticipantsHelper::prepareForSugar
     *
     * @dataProvider prepareForSugarProvider
     */
    public function testPrepareForSugar(
        VObject\Property\ICalendar\CalAddress $calAddress,
        array $userIds,
        array $expectedResult
    ) {
        $participantsMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Helper\ParticipantsHelper')
                                 ->disableOriginalConstructor()
                                 ->setMethods(array('getEmailAddressBean'))
                                 ->getMock();

        $eventMock = $this->getMockBuilder('\CalDavEvent')
                          ->disableOriginalConstructor()
                          ->setMethods(null)
                          ->getMock();

        TestReflection::setProtectedValue($participantsMock, 'statusMapper', new Dav\Base\Mapper\Status\AcceptedMap());

        $emailAddressMock = $this->getMockBuilder('\SugarEmailAddress')
                                 ->disableOriginalConstructor()
                                 ->setMethods(array('getRelatedId'))
                                 ->getMock();

        $participantsMock->expects($this->once())->method('getEmailAddressBean')->willReturn($emailAddressMock);

        $emailAddressMock->expects($this->at(0))->method('getRelatedId')->willReturn(array($userIds[0]));
        $emailAddressMock->expects($this->at(1))->method('getRelatedId')->willReturn(array($userIds[1]));
        $emailAddressMock->expects($this->at(2))->method('getRelatedId')->willReturn(array($userIds[2]));
        $emailAddressMock->expects($this->at(3))->method('getRelatedId')->willReturn(array($userIds[3]));

        $result = $participantsMock->prepareForSugar($eventMock, $calAddress);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @param array $davResult   CalDav participants
     * @param array $usersResult SugarCRM users
     * @param array $leadsResult SugarCRM leads
     * @param array $expectedResult
     *
     * @covers       Sugarcrm\Sugarcrm\Dav\Base\Helper\ParticipantsHelper::prepareForDav
     *
     * @dataProvider prepareForDavProvider
     */
    public function testPrepareForDav(
        array $davResult,
        array $usersResult,
        array $leadsResult,
        array $expectedResult
    ) {
        $participantsMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Helper\ParticipantsHelper')
                                 ->disableOriginalConstructor()
                                 ->setMethods(array('getUserPrimaryAddress'))
                                 ->getMock();

        $searchFactoryMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Principal\Search\Factory')
                                  ->disableOriginalConstructor()
                                  ->setMethods(array('getModulesForSearch'))
                                  ->getMock();

        $eventMock = $this->getMockBuilder('\CalDavEvent')
                          ->disableOriginalConstructor()
                          ->setMethods(array('getParticipants'))
                          ->getMock();

        $meetingsMock = $this->getMockBuilder('\Meeting')
                             ->disableOriginalConstructor()
                             ->setMethods(array('load_relationship'))
                             ->getMock();

        $userRelationShipMock = $this->getMockBuilder('Link2')
                                     ->disableOriginalConstructor()
                                     ->setMethods(array('getBeans', 'load'))
                                     ->getMockForAbstractClass();

        $leadsRelationShipMock = $this->getMockBuilder('Link2')
                                     ->disableOriginalConstructor()
                                     ->setMethods(array('getBeans', 'load'))
                                     ->getMockForAbstractClass();

        $currentBean = 0;

        foreach ($usersResult as $key => $value) {
            $participantsMock->expects($this->at($currentBean))->method('getUserPrimaryAddress')
                             ->willReturn($value['email']);
            $currentBean ++;
        }

        foreach ($leadsResult as $key => $value) {
            $participantsMock->expects($this->at($currentBean))->method('getUserPrimaryAddress')
                             ->willReturn($value['email']);
            $currentBean ++;
        }

        $users = array();
        foreach ($usersResult as $userId => $userInfo) {
            $userMock = $this->getMockBuilder('\User')
                             ->disableOriginalConstructor()
                             ->setMethods(null)
                             ->getMock();

            $userMock->id = $userId;
            $userMock->email1 = $userInfo['email'];
            $userMock->full_name = $userInfo['cn'];
            $userMock->module_name = 'Users';
            $userRelationShipMock->rows[$userId]['accept_status'] = $userInfo['accept_status'];
            $users[$userId] = $userMock;
        }

        $leads = array();
        foreach ($leadsResult as $userId => $leadInfo) {
            $leadMock = $this->getMockBuilder('\Lead')
                             ->disableOriginalConstructor()
                             ->setMethods(null)
                             ->getMock();

            $leadMock->id = $userId;
            $leadMock->email1 = $leadInfo['email'];
            $leadMock->full_name = $leadInfo['cn'];
            $leadMock->module_name = 'Leads';
            $leadsRelationShipMock->rows[$userId]['accept_status'] = $leadInfo['accept_status'];
            $leads[$userId] = $leadMock;
        }

        $meetingsMock->users = $userRelationShipMock;
        $meetingsMock->leads = $leadsRelationShipMock;

        $meetingsMock->expects($this->at(0))->method('load_relationship')->with('users')->willReturn(true);
        $meetingsMock->expects($this->at(1))->method('load_relationship')->with('contacts')->willReturn(false);
        $meetingsMock->expects($this->at(2))->method('load_relationship')->with('leads')->willReturn(true);
        $meetingsMock->expects($this->at(3))->method('load_relationship')->with('prospect')->willReturn(false);

        $userRelationShipMock->expects($this->any())->method('getBeans')->willReturn($users);
        $leadsRelationShipMock->expects($this->any())->method('getBeans')->willReturn($leads);

        $searchFactoryMock->expects($this->once())->method('getModulesForSearch')->willReturn(array(
            'Users',
            'Contacts',
            'Leads',
            'Prospect'
        ));

        TestReflection::setProtectedValue($participantsMock, 'statusMapper', new Dav\Base\Mapper\Status\AcceptedMap());
        TestReflection::setProtectedValue($participantsMock, 'searchFactory', $searchFactoryMock);

        $eventMock->expects($this->once())->method('getParticipants')->willReturn($davResult);

        $result = $participantsMock->prepareForDav($meetingsMock, $eventMock, 'ATTENDEE');

        $this->assertEquals($expectedResult, $result);
    }
}
