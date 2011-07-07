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
require_once('modules/Users/authentication/AuthenticationController.php');

class AuthenticateTest extends Sugar_PHPUnit_Framework_TestCase
{
	protected $_user = null;

	public function setUp() 
    {
    	$GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
    	$this->sugar_config_old = $GLOBALS['sugar_config'];
    	$_REQUEST['user_name'] = 'foo';
    	$_REQUEST['user_password'] = 'bar';
    	$_SESSION['authenticated_user_id'] = true;
    	$_SESSION['hasExpiredPassword'] = false;
    	$_SESSION['isMobile'] = null;
	}
	
	public function tearDown()
	{
	    unset($GLOBALS['current_user']);
	    $GLOBALS['sugar_config'] = $this->sugar_config_old;
	    unset($_REQUEST['login_module']);
        unset($_REQUEST['login_action']);
        unset($_REQUEST['login_record']);
        unset($_REQUEST['user_name']);
        unset($_REQUEST['user_password']);
        unset($_SESSION['authenticated_user_id']);
        unset($_SESSION['hasExpiredPassword']);
        unset($_SESSION['isMobile']);
	}
	
	public function testLoginRedirectIfAuthenicationFails()
	{
	    $_SESSION['authenticated_user_id'] = null;
	    
	    $authController = $this->getMock('AuthenticationController');
        
	    $url = '';
        require('modules/Users/Authenticate.php');
        
        $this->assertEquals(
            'Location: index.php?module=Users&action=Login',
            $url
            );
	}
	
	public function testDefaultAuthenicationRedirect() 
    {
        unset($GLOBALS['sugar_config']['default_module']);
        unset($GLOBALS['sugar_config']['default_action']);
        unset($_REQUEST['login_module']);
        unset($_REQUEST['login_action']);
        unset($_REQUEST['login_record']);
        
        $authController = $this->getMock('AuthenticationController');
        
        $url = '';
        require('modules/Users/Authenticate.php');
        
        $this->assertEquals(
            'Location: index.php?module=Home&action=index',
            $url
            );
    }
    
    public function testDefaultAuthenicationRedirectGivenLoginParameters() 
    {
        unset($GLOBALS['sugar_config']['default_module']);
        unset($GLOBALS['sugar_config']['default_action']);
        $_REQUEST['login_module'] = 'foo';
        $_REQUEST['login_action'] = 'bar';
        $_REQUEST['login_record'] = '123';
        
        $authController = $this->getMock('AuthenticationController');
        
        $url = '';
        require('modules/Users/Authenticate.php');
        
        $this->assertEquals(
            'Location: index.php?module=foo&action=bar&record=123',
            $url
            );
    }
    
    public function testDefaultAuthenicationRedirectGivenDefaultSettings() 
    {
        $GLOBALS['sugar_config']['default_module'] = 'dog';
        $GLOBALS['sugar_config']['default_action'] = 'cat';
        unset($_REQUEST['login_module']);
        unset($_REQUEST['login_action']);
        unset($_REQUEST['login_record']);
        
        $authController = $this->getMock('AuthenticationController');
        
        $url = '';
        require('modules/Users/Authenticate.php');
        
        $this->assertEquals(
            'Location: index.php?module=dog&action=cat',
            $url
            );
    }
    
    //BEGIN SUGARCRM flav=pro ONLY
    public function testMobileAuthenicationRedirect() 
    {
        unset($_REQUEST['login_module']);
        unset($_REQUEST['login_action']);
        unset($_REQUEST['login_record']);
        $_SESSION['isMobile'] = true;
        $GLOBALS['current_user']->setPreference('wireless_last_module','dogpoo');
        $authController = $this->getMock('AuthenticationController');
        
        $url = '';
        require('modules/Users/Authenticate.php');
        
        $this->assertEquals(
            'Location: index.php?module=dogpoo&action=wirelessmodule&mobile=1',
            $url
            );
    }
    
    public function testMobileAuthenicationRedirectIgnorredIfLoginParameters() 
    {
        $_REQUEST['login_module'] = 'cat';
        $_REQUEST['login_action'] = 'mouse';
        $_REQUEST['login_record'] = '456';
        $_SESSION['isMobile'] = true;
        $authController = $this->getMock('AuthenticationController');
        
        $url = '';
        require('modules/Users/Authenticate.php');
        
        $this->assertEquals(
            'Location: index.php?module=cat&action=mouse&record=456&mobile=1',
            $url
            );
    }
    //END SUGARCRM flav=pro ONLY
}
