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
 * @package Sugarcrm\SugarcrmTestsUnit\Dav\Base\Auth
 * @coversDefaultClass Sugarcrm\Sugarcrm\Dav\Base\Auth\SugarAuth
 */
class SugarAuthTest extends \PHPUnit_Framework_TestCase
{

    public function validateUserPassProvider()
    {
        return array(
            array(
                true
            ),
            array(
                false
            ),
        );
    }

    /**
     * @param $sugarLoginResult
     * @covers       Sugarcrm\Sugarcrm\Dav\Base\Auth\SugarAuth::validateUserPass
     *
     * @dataProvider validateUserPassProvider
     */
    public function testValidateUserPass($sugarLoginResult)
    {
        $davAuth = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Auth\SugarAuth')
                        ->setMethods(array('getSugarAuthController'))
                        ->getMock();

        $sugarAuth = $this->getMockBuilder('SugarAuthenticate')
                          ->setMethods(array('login'))
                          ->getMock();

        $sugarAuth->expects($this->once())->method('login')->with('username', 'password', array('noRedirect' => true))
                  ->willReturn($sugarLoginResult);

        $davAuth->expects($this->once())->method('getSugarAuthController')->willReturn($sugarAuth);

        $authResult = TestReflection::callProtectedMethod($davAuth, 'validateUserPass', array('username', 'password'));
        $this->assertEquals($sugarLoginResult, $authResult);
    }
}
