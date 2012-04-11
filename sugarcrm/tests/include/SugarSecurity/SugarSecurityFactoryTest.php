<?php
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



class SugarSecurityFactoryTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp() 
    {
        $this->markTestSkipped("Have to skip until the SugarSecurity Pull is merged again");
        $this->_user = $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
    }

    public function tearDown()
    {
        if ( file_exists('custom/include/SugarSecurity/SugarSecurityUnitTest.php') ) {
            unlink('custom/include/SugarSecurity/SugarSecurityUnitTest.php');
        }
        if ( isset($this->oldSession) ) {
            $_SESSION = $this->oldSession;
        }
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    public function testFactoryBasic()
    {
        // User is the default type, it should always exist
        $secClass = SugarSecurityFactory::loadClassFromType('User');
        $this->assertEquals('SugarSecurityUser',get_class($secClass));

        $this->oldSession = $_SESSION;
        $_SESSION['authenticated_user_id'] = $this->_user->id;
        $_SESSION['authenticated_user_language'] = 'en_us';
        $_SESSION['sugarSec'] = array('type'=>'User','userId'=>$this->_user->id);

        $secClass2 = SugarSecurityFactory::loadClassFromSession();
        $this->assertEquals('SugarSecurityUser',get_class($secClass2));
    }

    public function testFactoryCustom()
    {
        // Touch a file out there so that it will use our already defined custom class
        if ( ! is_dir('custom/include/SugarSecurity/') ) {
            mkdir('custom/include/SugarSecurity/',0777,true);
        }
        touch('custom/include/SugarSecurity/SugarSecurityUnitTest.php');

        $secClass = SugarSecurityFactory::loadClassFromType('UnitTest');
        $this->assertEquals('SugarSecurityUnitTest',get_class($secClass));

        $this->oldSession = $_SESSION;
        $_SESSION['authenticated_user_id'] = $this->_user->id;
        $_SESSION['authenticated_user_language'] = 'en_us';
        $_SESSION['sugarSec'] = array('type'=>'UnitTest','userId'=>$this->_user->id);

        $secClass2 = SugarSecurityFactory::loadClassFromSession();
        $this->assertEquals('SugarSecurityUnitTest',get_class($secClass2));
    }
}
/*
class SugarSecurityUnitTest extends SugarSecurity {
    function loginUserPass($username, $password, $passwordType = 'PLAIN' ) { return true; }
    function loginOAuth2Token($token) { return true; }
    function loginSingleSignOnToken($token) { return true; }
    function loadFromSession() { return true; }
    function canAccessModule(SugarBean $bean,$accessType='view') { return true; }
    function canAccessField(SugarBean $bean,$fieldName,$accessType) { return true; }
    function hasExtraSecurity(SugarBean $bean,$action='list') { return false; }
    // Soylent test is people!
    function isSugarUser() { return true; }
}*/