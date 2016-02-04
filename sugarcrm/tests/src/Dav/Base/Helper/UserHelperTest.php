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

namespace Sugarcrm\SugarcrmTests\Dav\Base\Helper;

/**
 * Class PrincipalParserTest
 * @package Sugarcrm\SugarcrmTestsUnit\Dav\Base\Helper
 *
 * @coversDefaultClass Sugarcrm\Sugarcrm\Dav\Base\Helper\UserHelper
 */
class UserHelperTest extends \PHPUnit_Framework_TestCase
{
    public function getPrincipalStringByUserProvider()
    {
        return array(
            array(
                'username' => 'user1',
                'principalPrefix' => 'principals',
                'result' => 'principals/user1',
            ),
            array(
                'username' => 'user1',
                'principalPrefix' => 'principals/',
                'result' => 'principals/user1',
            ),
            array(
                'username' => 'user1',
                'principalPrefix' => '',
                'result' => 'user1',
            ),
        );
    }

    public function getUserByPrincipalStringProvider()
    {
        return array(
            array(
                'principal' => 'principals/users/user1',
                'userID' => 'user1',
                'principalPrefix' => 'principals/users',
            ),
            array(
                'principal' => 'principals/contacts/user1',
                'userID' => null,
                'principalPrefix' => '',
            ),
            array(
                'principal' => 'principals/users',
                'userID' => null,
                'principalPrefix' => '',
            ),
            array(
                'principal' => 'user1',
                'userID' => 'user1',
                'principalPrefix' => '',
            ),
        );
    }

    /**
     * @param $username
     * @param $principalPrefix
     * @param $expectedPath
     *
     * @dataProvider getPrincipalStringByUserProvider
     *
     * @covers Sugarcrm\Sugarcrm\Dav\Base\Helper\UserHelper::getPrincipalStringByUser
     */
    public function testGetPrincipalStringByUser($username, $principalPrefix, $expectedPath)
    {
        $helperMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Helper\UserHelper')
                           ->disableOriginalConstructor()
                           ->setMethods(null)
                           ->getMock();

        $userMock = $this->getMockBuilder('User')
                         ->disableOriginalConstructor()
                         ->setMethods(null)
                         ->getMock();

        $helperMock->setPrincipalPrefix($principalPrefix);

        $userMock->user_name = $username;

        $result = $helperMock->getPrincipalStringByUser($userMock);

        $this->assertEquals($expectedPath, $result);
    }

    /**
     * @param $principal
     * @param $expectedUserName
     * @param $expectedPrincipalPrefix
     *
     * @dataProvider getUserByPrincipalStringProvider
     *
     * @covers Sugarcrm\Sugarcrm\Dav\Base\Helper\UserHelper::getUserByPrincipalString
     */
    public function testGetUserByPrincipalString($principal, $expectedUserName, $expectedPrincipalPrefix)
    {
        $helperMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Helper\UserHelper')
                           ->disableOriginalConstructor()
                           ->setMethods(array('setPrincipalPrefix', 'getUserByUserName'))
                           ->getMock();

        if ($expectedUserName) {
            $helperMock->expects($this->once())->method('setPrincipalPrefix')->with($expectedPrincipalPrefix);
            $helperMock->expects($this->once())->method('getUserByUserName')->with($expectedUserName);
        } else {
            $helperMock->expects($this->never())->method('setPrincipalPrefix');
            $helperMock->expects($this->never())->method('getUserByUserName');
        }

        $helperMock->getUserByPrincipalString($principal);
    }
}
