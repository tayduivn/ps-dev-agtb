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
require_once 'modules/Users/User.php';

class UserTest extends Sugar_PHPUnit_Framework_TestCase
{
	protected $_user = null;

	public function setUp() 
    {
    	$this->_user = SugarTestUserUtilities::createAnonymousUser();
    	$GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
	}
	
	public function tearDown()
	{
	    unset($GLOBALS['current_user']);
	    SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
	}

	public function testSettingAUserPreference() 
    {
        $this->_user->setPreference('test_pref','dog');

        $this->assertEquals('dog',$this->_user->getPreference('test_pref'));
    }
    
    public function testGettingSystemPreferenceWhenNoUserPreferenceExists()
    {
        $GLOBALS['sugar_config']['somewhackypreference'] = 'somewhackyvalue';
        
        $result = $this->_user->getPreference('somewhackypreference');
        
        unset($GLOBALS['sugar_config']['somewhackypreference']);
        
        $this->assertEquals('somewhackyvalue',$result);
    }
    
    /**
     * @ticket 42667
     */
    public function testGettingSystemPreferenceWhenNoUserPreferenceExistsForEmailDefaultClient()
    {
        if ( isset($GLOBALS['sugar_config']['email_default_client']) ) {
            $oldvalue = $GLOBALS['sugar_config']['email_default_client'];
        }
        $GLOBALS['sugar_config']['email_default_client'] = 'somewhackyvalue';
        
        $result = $this->_user->getPreference('email_link_type');
        
        if ( isset($oldvalue) ) {
            $GLOBALS['sugar_config']['email_default_client'] = $oldvalue;
        }
        else {
            unset($GLOBALS['sugar_config']['email_default_client']);
        }
        
        $this->assertEquals('somewhackyvalue',$result);
    }
    
    public function testResetingUserPreferences()
    {
        $this->_user->setPreference('test_pref','dog');

        $this->_user->resetPreferences();
        
        $this->assertNull($this->_user->getPreference('test_pref'));
    }
    
    /**
     * @ticket 36657
     */
    public function testCertainPrefsAreNotResetWhenResetingUserPreferences()
    {
        $this->_user->setPreference('ut','1');
        $this->_user->setPreference('timezone','GMT');

        $this->_user->resetPreferences();
        
        $this->assertEquals('1',$this->_user->getPreference('ut'));
        $this->assertEquals('GMT',$this->_user->getPreference('timezone'));
    }

    public function testDeprecatedUserPreferenceInterface()
    {
        User::setPreference('deprecated_pref','dog',0,'global',$this->_user);
        
        $this->assertEquals('dog',User::getPreference('deprecated_pref','global',$this->_user));
    }
    
    public function testSavingToMultipleUserPreferenceCategories()
    {
        $this->_user->setPreference('test_pref1','dog',0,'cat1');
        $this->_user->setPreference('test_pref2','dog',0,'cat2');
        
        $this->_user->savePreferencesToDB();
        
        $this->assertEquals(
            'cat1',
            $GLOBALS['db']->getOne("SELECT category FROM user_preferences WHERE assigned_user_id = '{$this->_user->id}' AND category = 'cat1'")
            );
        
        $this->assertEquals(
            'cat2',
            $GLOBALS['db']->getOne("SELECT category FROM user_preferences WHERE assigned_user_id = '{$this->_user->id}' AND category = 'cat2'")
            );
    }
}

