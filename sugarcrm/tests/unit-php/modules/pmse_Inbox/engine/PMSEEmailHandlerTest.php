<?php
//FILE SUGARCRM flav=ent ONLY
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
namespace Sugarcrm\SugarcrmTestsUnit\modules\pmse_Inbox\engine;

/**
 * @coversDefaultClass \PMSEEmailHandler
 */
class PMSEEmailHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::isUserActiveForEmail
     * @dataProvider providerIsUserActiveForEmail
     */
    public function providerIsUserActiveForEmail()
    {
        return array(
            array(
                array(
                    'full_name' => 'John Doe',
                    'email1' => 'john.doe@example.com',
                    'status' => 'Active',
                    'employee_status' => 'Active',
                ),
                true,
            ),
            array(
                array(
                    'full_name' => '',
                    'email1' => 'jane.elliot@abc.com',
                    'status' => 'Active',
                    'employee_status' => 'Active',
                ),
                false,
            ),
            array(
                array(
                    'full_name' => 'Bob Smith',
                    'email1' => '',
                    'status' => 'Active',
                    'employee_status' => 'Active',
                ),
                false,
            ),
            array(
                array(
                    'full_name' => 'Mary Jones',
                    'email1' => 'mjones@xyz.com',
                    'status' => 'Inactive',
                    'employee_status' => 'Active',
                ),
                false,
            ),
            array(
                array(
                    'full_name' => 'Jim Brown',
                    'email1' => 'jim.brown@foo.com',
                    'status' => 'Active',
                    'employee_status' => '',
                ),
                false,
            ),
        );
    }

    /**
     * @covers ::isUserActiveForEmail
     * @dataProvider providerIsUserActiveForEmail
     */
    public function testIsUserActiveForEmail($userAttr, $expected)
    {
        $user = $this->createMock('\User');
        foreach ($userAttr as $prop => $val) {
            $user->$prop = $val;
        }

        $emailHandlerMock = $this->getMockBuilder('\PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $result = $emailHandlerMock->isUserActiveForEmail($user);
        $this->assertEquals($expected, $result);
    }
}
