<?php
// FILE SUGARCRM flav=pro ONLY
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/en/msa/master_subscription_agreement_11_April_2011.pdf
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
require_once 'modules/Users/User.php';

class Bug49896Test extends Sugar_PHPUnit_Framework_TestCase
{
    var $_passwordSetting;
    var $_currentUser;

    public function setUp()
    {
        if(isset($GLOBALS['sugar_config']['passwordsetting']))
        {
            $this->_passwordSetting = $GLOBALS['sugar_config']['passwordsetting'];
        }
        $GLOBALS['sugar_config']['passwordsetting'] = array('onenumber'=>1,
                'onelower'=>1,
                'oneupper'=>1,
                'onespecial'=>1,
                'minpwdlength'=>6,
                'maxpwdlength'=>15);
        $this->_currentUser = SugarTestUserUtilities::createAnonymousUser(false);        
    }

    public function tearDown()
    {
        if(!empty($this->_passwordSetting))
        {
            $GLOBALS['sugar_config']['passwordsetting'] = $this->_passwordSetting;
        }
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    public function testMinLength()
    {
        $result = $this->_currentUser->check_password_rules('Tes1!');
        $this->assertEquals(false, $result, 'Assert that min length rule is checked');
    }

    public function testMaxLength()
    {
        $result = $this->_currentUser->check_password_rules('Tester123456789!');
        $this->assertEquals(false, $result, 'Assert that max length rule is checked');
    }
        
    public function testOneNumber()
    {
        $result = $this->_currentUser->check_password_rules('Tester!');
        $this->assertEquals(false, $result, 'Assert that one number rule is checked');
    }

    public function testOneLower()
    {
        $result = $this->_currentUser->check_password_rules('TESTER1!');
        $this->assertEquals(false, $result, 'Assert that one lower rule is checked');
    }
    
    public function testOneUpper()
    {
        $result = $this->_currentUser->check_password_rules('tester1!');
        $this->assertEquals(false, $result, 'Assert that one upper rule is checked');
    } 
    
    public function testOneSpecial()
    {
        $result = $this->_currentUser->check_password_rules('Tester1');
        $this->assertEquals(false, $result, 'Assert that one special rule is checked');
    }  
    
    public function testCustomRegex()
    {
        $GLOBALS['sugar_config']['passwordsetting']['customregex'] = '/^T/';
        $result = $this->_currentUser->check_password_rules('tester1!');
        $this->assertEquals(false, $result, 'Assert that custom regex is checked');
    } 

    public function testAllCombinations()
    {
        $result = $this->_currentUser->check_password_rules('Tester1!');
        $this->assertEquals(true, $result, 'Assert that all rules are checked and passed');
    }    
}
?>