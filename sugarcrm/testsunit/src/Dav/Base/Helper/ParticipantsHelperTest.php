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
                'ids' => array(1, 2, 3),
                'result' => array(
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
                    '3' => array(
                        'email' => 'test1@test.com',
                        'accept_status' => 'decline',
                        'cn' => 'Test1 Test1',
                        'role' => 'OPT-PARTICIPANT',
                    ),
                ),
            ),
        );
    }

    public function prepareForDavProvider()
    {
        return array(
            array(
                'davResult' => array(
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
                'beansResult' => array(
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
                'convertResult' => array(),
            ),
            array(
                'davResult' => array(),
                'beansResult' => array(),
                'convertResult' => array(),
            ),
            array(
                'davResult' => array(),
                'beansResult' => array(
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
                'convertResult' => array(
                    Dav\Base\Constants::PARTICIPIANT_ADDED => array(
                        'mailto:test@test.com' => array(
                            'PARTSTAT' => 'ACCEPTED',
                            'CN' => 'Test Test',
                            'ROLE' => '',
                            'davLink' => null,
                            'X-SUGARUID' => '2a',
                            'RSVP' => 'TRUE',
                        ),
                        'mailto:test1@test.com' => array(
                            'PARTSTAT' => 'DECLINED',
                            'CN' => 'Test1 Test1',
                            'ROLE' => '',
                            'davLink' => null,
                            'X-SUGARUID' => '3a',
                            'RSVP' => 'TRUE',
                        ),
                    ),
                ),
            ),
            array(
                'davResult' => array(
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
                'beansResult' => array(),
                'convertResult' => array(
                    Dav\Base\Constants::PARTICIPIANT_DELETED => array(
                        'mailto:test@test.com' => array(
                            'PARTSTAT' => null,
                            'CN' => null,
                            'ROLE' => '',
                            'davLink' => null,
                            'X-SUGARUID' => '2a',
                            'RSVP' => 'TRUE',
                        ),
                        'mailto:test1@test.com' => array(
                            'PARTSTAT' => null,
                            'CN' => null,
                            'ROLE' => '',
                            'davLink' => null,
                            'X-SUGARUID' => '3a',
                            'RSVP' => 'TRUE',
                        ),
                    ),
                ),
            ),
            array(
                'davResult' => array(
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
                'beansResult' => array(
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
                        ),
                        'mailto:test2@test.com' => array(
                            'PARTSTAT' => 'DECLINED',
                            'CN' => 'Test1 Test1',
                            'ROLE' => 'OPT-PARTICIPANT',
                            'davLink' => 'mailto:test1@test.com',
                            'X-SUGARUID' => '3a',
                            'RSVP' => 'TRUE',
                        ),
                    ),
                    Dav\Base\Constants::PARTICIPIANT_ADDED => array(
                        'mailto:test5@test.com' => array(
                            'PARTSTAT' => 'NEEDS-ACTION',
                            'CN' => 'Test5 Test5',
                            'ROLE' => null,
                            'davLink' => null,
                            'X-SUGARUID' => '5a',
                            'RSVP' => 'TRUE',
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

        $mapperMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Mapper\Status\AcceptedMap')
                           ->disableOriginalConstructor()
                           ->setMethods(array('getMapping'))
                           ->getMock();

        $eventMock = $this->getMockBuilder('\CalDavEvent')
                          ->disableOriginalConstructor()
                          ->setMethods(null)
                          ->getMock();

        TestReflection::setProtectedValue($participantsMock, 'statusMapper', $mapperMock);

        $mapperMock->expects($this->any())
                   ->method('getMapping')
                   ->willReturn(TestReflection::getProtectedValue($mapperMock, 'statusMap'));

        $emailAddressMock = $this->getMockBuilder('\SugarEmailAddress')
                                 ->disableOriginalConstructor()
                                 ->setMethods(array('getRelatedId'))
                                 ->getMock();

        $participantsMock->expects($this->once())->method('getEmailAddressBean')->willReturn($emailAddressMock);

        $emailAddressMock->expects($this->at(0))->method('getRelatedId')->willReturn(array($userIds[0]));
        $emailAddressMock->expects($this->at(1))->method('getRelatedId')->willReturn(array($userIds[1]));
        $emailAddressMock->expects($this->at(2))->method('getRelatedId')->willReturn(array($userIds[2]));

        $result = $participantsMock->prepareForSugar($eventMock, $calAddress);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @param array $davResult   CalDav participants
     * @param array $beansResult SugarCRM participants
     * @param array $expectedResult
     *
     * @covers       Sugarcrm\Sugarcrm\Dav\Base\Helper\ParticipantsHelper::prepareForDav
     *
     * @dataProvider prepareForDavProvider
     */
    public function testPrepareForDav(array $davResult, array $beansResult, array $expectedResult)
    {
        $participantsMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Helper\ParticipantsHelper')
                                 ->disableOriginalConstructor()
                                 ->setMethods(array('getUserPrimaryAddress'))
                                 ->getMock();

        $mapperMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Mapper\Status\AcceptedMap')
                           ->disableOriginalConstructor()
                           ->setMethods(array('getMapping'))
                           ->getMock();

        $eventMock = $this->getMockBuilder('\CalDavEvent')
                          ->disableOriginalConstructor()
                          ->setMethods(array('getBean', 'getParticipants'))
                          ->getMock();

        $meetingsMock = $this->getMockBuilder('\Meeting')
                             ->disableOriginalConstructor()
                             ->setMethods(array('load_relationship'))
                             ->getMock();

        $userRelationShipMock = $this->getMockBuilder('Link2')
                                     ->disableOriginalConstructor()
                                     ->setMethods(array('getBeans', 'load'))
                                     ->getMockForAbstractClass();

        $currentBean = 0;

        foreach ($beansResult as $key => $value) {
            $participantsMock->expects($this->at($currentBean))->method('getUserPrimaryAddress')
                             ->willReturn($value['email']);
            $currentBean ++;
        }

        $users = array();
        foreach ($beansResult as $userId => $userInfo) {
            $userMock = $this->getMockBuilder('\User')
                             ->disableOriginalConstructor()
                             ->setMethods(null)
                             ->getMock();

            $userMock->id = $userId;
            $userMock->email1 = $userInfo['email'];
            $userMock->full_name = $userInfo['cn'];
            $userRelationShipMock->rows[$userId]['accept_status'] = $userInfo['accept_status'];
            $users[$userId] = $userMock;
        }

        $meetingsMock->users = $userRelationShipMock;
        $meetingsMock->expects($this->once())->method('load_relationship')->willReturn(true);

        $userRelationShipMock->expects($this->once())->method('getBeans')->willReturn($users);

        TestReflection::setProtectedValue($participantsMock, 'statusMapper', $mapperMock);

        $mapperMock->expects($this->any())
                   ->method('getMapping')
                   ->willReturn(TestReflection::getProtectedValue($mapperMock, 'statusMap'));

        $eventMock->expects($this->once())->method('getParticipants')->willReturn($davResult);
        $eventMock->expects($this->once())->method('getBean')->willReturn($meetingsMock);

        $result = $participantsMock->prepareForDav($eventMock, 'ATTENDEE');

        $this->assertEquals($expectedResult, $result);
    }
}
