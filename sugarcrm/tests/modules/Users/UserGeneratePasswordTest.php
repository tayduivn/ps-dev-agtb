<?php
//FILE SUGARCRM flav=pro ONLY
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/
require_once 'modules/Users/User.php';


/**
 * UserGeneratePasswordTest
 *
 * This class runs a series of tests against the generatePassword static function in the Users class.
 * @author Collin Lee
 */
class UserGeneratePasswordTest extends Sugar_PHPUnit_Framework_TestCase
{
    var $_passwordSetting;

    public function setUp()
    {
        if(isset($GLOBALS['sugar_config']['passwordsetting']))
        {
            $this->_passwordSetting = $GLOBALS['sugar_config']['passwordsetting'];
        }
        $GLOBALS['sugar_config']['passwordsetting'] = array('onenumber'=>0,
                                                            'onelower'=>0,
                                                            'oneupper'=>0,
                                                            'onespecial'=>0,
                                                            'minpwdlength'=>6);
    }

    public function tearDown()
    {
        if(!empty($this->_passwordSetting))
        {
            $GLOBALS['sugar_config']['passwordsetting'] = $this->_passwordSetting;
        }
    }

    public function testUserGeneratePasswordOneNumber()
    {
        $GLOBALS['sugar_config']['passwordsetting']['onenumber'] = '1';
        $password = User::generatePassword();
        $this->assertRegExp('/\d/', $password, 'Assert that we have at least one number in the generated password');
    }

    public function testUserGeneratePasswordOneLower()
    {
        $GLOBALS['sugar_config']['passwordsetting']['onelower'] = '1';
        $password = User::generatePassword();
        $this->assertRegExp('/[a-z]/', $password, 'Assert that we have at least one lower case letter in the generated password');
    }

    public function testUserGeneratePasswordOneUpper()
    {
        $GLOBALS['sugar_config']['passwordsetting']['oneupper'] = '1';
        $password = User::generatePassword();
        $this->assertRegExp('/[A-Z]/', $password, 'Assert that we have at least one upper case letter in the generated password');
    }

    public function testUserGeneratePasswordOneSpecial()
    {
        $GLOBALS['sugar_config']['passwordsetting']['onespecial'] = '1';
        $password = User::generatePassword();
        $this->assertRegExp('/[\~\!\@\#\$\%\^\&\*\(\)\_\+\=\-\{\}\|]/', $password, 'Assert that we have at least one special letter in the generated password');
    }

    public function testUserGeneratedPasswordMinimumLength()
    {
        $GLOBALS['sugar_config']['passwordsetting']['minpwdlength'] = 10;
        $password = User::generatePassword();
        $this->assertTrue(strlen($password) == 6, 'Assert that regardless of set length, system-generated passwords are only 6 characters in length');

        $GLOBALS['sugar_config']['passwordsetting']['minpwdlength'] = 5;
        $password = User::generatePassword();
        $this->assertTrue(strlen($password) == 6, 'Assert that regardless of set length, system-generated passwords are only 6 characters in length');
    }

    public function testAllCombinationsEnabled()
    {
        $GLOBALS['sugar_config']['passwordsetting'] = array(
            'onenumber' => '1',
            'onelower' => '1',
            'oneupper' => '1',
            'onespecial' => '1',
            'minpwdlength' => 10,
        );

        $password = User::generatePassword();
        $this->assertRegExp('/\d/', $password, 'Assert that we have at least one number in the generated password');
        $this->assertRegExp('/[a-z]/', $password, 'Assert that we have at least one lower case letter in the generated password');
        $this->assertRegExp('/[A-Z]/', $password, 'Assert that we have at least one upper case letter in the generated password');
        $this->assertRegExp('/[\~\!\@\#\$\%\^\&\*\(\)\_\+\=\-\{\}\|]/', $password, 'Assert that we have at least one special letter in the generated password');
        $this->assertTrue(strlen($password) == 6, 'Assert that regardless of set length, system-generated passwords are only 6 characters in length');
    }
}
