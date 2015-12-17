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

require_once('modules/Contacts/Contact.php');
require_once('modules/Leads/Lead.php');
require_once('modules/Users/User.php');
/**
 * Class ParticipantsHelperTest
 * @package            Sugarcrm\SugarcrmTestsUnit\Dav\Base\Helper
 *
 * @coversDefaultClass Sugarcrm\Sugarcrm\Dav\Base\Helper\ParticipantsHelper
 */
class ParticipantsHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers  Sugarcrm\Sugarcrm\Dav\Base\Helper\ParticipantsHelper::getInvitesDiff
     * @dataProvider invitesProvider
     * @param $invitesBefore
     * @param $invitesAfter
     * @param $expectedDiff
     */
    public function testGetInvitesDiff($invitesBefore, $invitesAfter, $expectedDiff)
    {
        $participantsMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Helper\ParticipantsHelper')
            ->disableOriginalConstructor()
            ->setMethods(array('getUserPrimaryAddress'))
            ->getMock();
        $dataDiff = $participantsMock->getInvitesDiff($invitesBefore, $invitesAfter);
        $this->assertEquals($expectedDiff, $dataDiff);
    }

    /**
     * @return array
     */
    public function invitesProvider()
    {
        $providedData = array();
        $invitesBefore = array(//start before
            'contacts' => array(
                10 => array(
                    'status' => 'none',
                    'bean' => $this->getInvitesBeanMock('\Contact', 10, array('email' => 'contacts10@loc.loc', 'name' => 'Contacts One'))
                )
            ),
            'leads' => array(),
            'users' => array()
        );
        $invitesAfter =  array(
            'contacts' => array(
                10 => array(
                    'status' => 'none',
                    'bean' => $this->getInvitesBeanMock('\Contact', 10, array('email' => 'contacts10@loc.loc', 'name' => 'Contacts One'))
                )
            ),
            'leads' => array(
                20 => array(
                    'status' => 'accept',
                    'bean' => $this->getInvitesBeanMock('\Lead', 20, array('email' => 'lead20@loc.loc', 'name' => 'Lead One'))
                )
            ),
            'users' => array(
                30 => array(
                    'status' => 'accept',
                    'bean' => $this->getInvitesBeanMock('\User', 30, array('email' => 'user30@loc.loc', 'name' => 'User Foo'))
                )
            )
        );
        $providedData[0] = array(
            $invitesBefore,
            $invitesAfter,
            array(//start expected data
                'added' => array(
                    array('Leads', 20, 'accept', 'lead20@loc.loc', 'Lead One'),
                    array('Users', 30, 'accept', 'user30@loc.loc', 'User Foo')
                ),//added
            )//endExpectedData
        );

        $invitesAfter['contacts'][10]['status'] = 'decline';
        $providedData[1] = array(
            $invitesBefore,
            $invitesAfter,
            array(//start expected data
                'added' => array(
                    array('Leads', 20, $invitesAfter['leads'][20]['status'], 'lead20@loc.loc', 'Lead One'),
                    array('Users', 30, $invitesAfter['leads'][20]['status'], 'user30@loc.loc', 'User Foo')
                ),//added
                'changed' => array(
                    array('Contacts', 10, $invitesAfter['contacts'][10]['status'], 'contacts10@loc.loc', 'Contacts One')
                ),
            )//endExpectedData
        );

        unset($invitesAfter['contacts'][10]);
        $providedData[2] = array(
            $invitesBefore,
            $invitesAfter,
            array(//start expected data
                'added' => array(
                    array('Leads', 20, $invitesAfter['leads'][20]['status'], 'lead20@loc.loc', 'Lead One'),
                    array('Users', 30, $invitesAfter['leads'][20]['status'], 'user30@loc.loc', 'User Foo')
                ),//added
                'deleted' => array(
                    array('Contacts', 10, $invitesBefore['contacts'][10]['status'], 'contacts10@loc.loc', 'Contacts One')
                ),
            )//endExpectedData
        );
        return $providedData;

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
        $inviteMock->emailAddress = $emailAddressMock;
        return $inviteMock;
    }
}
