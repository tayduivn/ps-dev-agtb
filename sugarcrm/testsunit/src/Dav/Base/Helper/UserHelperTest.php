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
                'principal' => 'principals/user1',
                'userID' => 'user1',
                'principalPrefix' => 'principals',
            ),
            array(
                'principal' => 'user1',
                'userID' => 'user1',
                'principalPrefix' => '',
            ),
        );
    }

    public function getPrincipalArrayByUserProvider()
    {
        return array(
            array(
                'prefixPath' => 'principals',
                'userBean' => array(
                    'id' => 1,
                    'user_name' => 'testuser',
                    'full_name' => 'first second',
                    'email1' => 'testuser@testuser.com'
                ),
                'davArray' => array(
                    'id' => 1,
                    'uri' => 'principals/testuser',
                    '{DAV:}displayname' => 'first second',
                    '{http://sabredav.org/ns}email-address' => 'testuser@testuser.com',
                )
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

        $helperMock->expects($this->once())->method('setPrincipalPrefix')->with($expectedPrincipalPrefix);
        $helperMock->expects($this->once())->method('getUserByUserName')->with($expectedUserName);

        $helperMock->getUserByPrincipalString($principal);
    }

    /**
     * @param $prefixPath Principal prefix path
     * @param array $userBean User bean params
     * @param array $davArray Expected result
     *
     * @dataProvider getPrincipalArrayByUserProvider
     *
     * @covers Sugarcrm\Sugarcrm\Dav\Base\Helper\UserHelper::getPrincipalArrayByUser
     */
    public function testGetPrincipalArrayByUser($prefixPath, array $userBean, array $davArray)
    {
        $helperMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Helper\UserHelper')
                           ->disableOriginalConstructor()
                           ->setMethods(null)
                           ->getMock();

        $userMock = $this->getMockBuilder('User')
                         ->disableOriginalConstructor()
                         ->setMethods(null)
                         ->getMock();

        foreach ($userBean as $key => $value) {
            $userMock->$key = $value;
        }

        $helperMock->setPrincipalPrefix($prefixPath);
        $result = $helperMock->getPrincipalArrayByUser($userMock);

        $this->assertEquals($davArray, $result);
    }
}
