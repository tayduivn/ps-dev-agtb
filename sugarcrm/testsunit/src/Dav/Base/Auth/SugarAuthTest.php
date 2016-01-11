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

namespace Sugarcrm\SugarcrmTestsUnit\Dav\Base\Auth;

use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * Class SugarAuthTest
 * @package            Sugarcrm\SugarcrmTestsUnit\Dav\Base\Auth
 * @coversDefaultClass Sugarcrm\Sugarcrm\Dav\Base\Auth\SugarAuth
 */
class SugarAuthTest extends \PHPUnit_Framework_TestCase
{

    public function validateUserPassWithCorrectPasswordProvider()
    {
        return array(
            array(
                array(1),
                true,
            ),
            array(
                array(),
                false,
            ),
        );
    }

    /**
     * @param $queryResult
     * @param $expectedResult
     *
     * @covers        Sugarcrm\Sugarcrm\Dav\Base\Auth\SugarAuth::validateUserPass
     *
     * @dataProvider  validateUserPassWithCorrectPasswordProvider
     */
    public function testValidateUserPassWithCorrectPassword($queryResult, $expectedResult)
    {
        $davAuth = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Auth\SugarAuth')
                        ->setMethods(array('getSugarAuthController', 'getCurrentUser', 'getSugarQuery'))
                        ->getMock();

        $userMock = $this->getMockBuilder('\User')
                         ->disableOriginalConstructor()
                         ->setMethods(array('load_relationship'))
                         ->getMock();
        $linkMock = $this->getMockBuilder('\Link2')
                         ->disableOriginalConstructor()
                         ->setMethods(array())
                         ->getMock();
        $userMock->id = 'test';
        $userMock->email_addresses_primary = $linkMock;

        $sugarAuth = $this->getMockBuilder('SugarAuthenticate')
                          ->setMethods(array('login'))
                          ->getMock();

        $sugarAuth->expects($this->once())->method('login')->with('username', 'password', array('noRedirect' => true))
                  ->willReturn(true);

        $davAuth->expects($this->once())->method('getSugarAuthController')->willReturn($sugarAuth);
        $davAuth->expects($this->once())->method('getCurrentUser')->willReturn($userMock);
        $userMock->expects($this->once())->method('load_relationship')->with('email_addresses_primary')
                 ->willReturn(true);
        $linkMock->expects($this->once())->method('getBeans')->with(array('where' => 'primary_address = 1'))
                 ->willReturn($queryResult);

        $authResult = TestReflection::callProtectedMethod($davAuth, 'validateUserPass', array('username', 'password'));
        $this->assertEquals($expectedResult, $authResult);
    }

    /**
     * @covers        Sugarcrm\Sugarcrm\Dav\Base\Auth\SugarAuth::validateUserPass
     */
    public function testValidateUserPassWithIncorrectPassword()
    {
        $davAuth = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Auth\SugarAuth')
                        ->setMethods(array('getSugarAuthController', 'getCurrentUser', 'getSugarQuery'))
                        ->getMock();

        $userMock = $this->getMockBuilder('\User')
                         ->disableOriginalConstructor()
                         ->setMethods(array('load_relationship'))
                         ->getMock();

        $sugarAuth = $this->getMockBuilder('SugarAuthenticate')
                          ->setMethods(array('login'))
                          ->getMock();

        $sugarAuth->expects($this->once())->method('login')->with('username', 'password', array('noRedirect' => true))
                  ->willReturn(false);

        $davAuth->expects($this->once())->method('getSugarAuthController')->willReturn($sugarAuth);
        $davAuth->expects($this->once())->method('getCurrentUser')->willReturn($userMock);
        $userMock->expects($this->never())->method('load_relationship');

        $authResult = TestReflection::callProtectedMethod($davAuth, 'validateUserPass', array('username', 'password'));
        $this->assertFalse($authResult);
    }
}
