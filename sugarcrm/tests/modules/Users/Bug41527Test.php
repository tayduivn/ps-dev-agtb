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

class Bug41527Test extends Sugar_PHPUnit_Framework_OutputTestCase
{
    public $_default_max_tabs_set = false;
    public $_default_max_tabs = '';
    public $_max_tabs_test = 666;

    public function setUp()
    {
        $this->_default_max_tabs_set == isset($GLOBALS['sugar_config']['default_max_tabs']);
        if ($this->_default_max_tabs_set) {
            $this->_default_max_tabs = $GLOBALS['sugar_config']['default_max_tabs'];
        }

        $beanList = array();
        $beanFiles = array();
        require('include/modules.php');
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user']->is_admin = '1';
        $GLOBALS['sugar_config']['default_max_tabs'] = $this->_max_tabs_test;
        if(!isset($GLOBALS['current_language'])) {
            $GLOBALS['current_language'] = 'en_us';
        }
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
        $GLOBALS['mod_strings'] = return_module_language($GLOBALS['current_language'], 'Users');
        $GLOBALS['request_string'] = '';
    }

    public function tearDown()
    {
        if ($this->_default_max_tabs_set) {
            $GLOBALS['sugar_config']['default_max_tabs'] = $this->_default_max_tabs;
        } else {
            unset($GLOBALS['sugar_config']['default_max_tabs']);
        }
        unset($GLOBALS['beanFiles']);
        unset($GLOBALS['beanList']);
        unset($GLOBALS['current_user']);
        unset($GLOBALS['app_list_strings']);
        unset($GLOBALS['app_strings']);
        unset($GLOBALS['request_string']);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestContactUtilities::removeAllCreatedContacts();
    }

    public function testUsingDefaultMaxTabsForOptionsValues()
    {
        global $current_user, $locale, $sugar_config;

        $_REQUEST['module'] = 'Users';
        require('modules/Users/EditView.php');
        $this->expectOutputRegex('/<select name="user_max_tabs".*<option label="' . $this->_max_tabs_test . '" value="' . $this->_max_tabs_test . '".*>' . $this->_max_tabs_test . '<\/option>.*<\/select>/ms');
    }

    /**
     * @ticket 42719
     */
    public function testAllowSettingMaxTabsTo10WhenSettingIsLessThan10()
    {
        global $current_user, $locale, $sugar_config;

        $GLOBALS['sugar_config']['default_max_tabs'] = 7;

        $_REQUEST['module'] = 'Users';
        require('modules/Users/EditView.php');

        $this->expectOutputRegex('/<select name="user_max_tabs".*<option label="10" value="10".*>10<\/option>.*<\/select>/ms');
    }

    /**
     * @ticket 42719
     */
    public function testUsersDefaultMaxTabsSettingHonored()
    {
        global $current_user, $locale, $sugar_config;

        $current_user->setPreference('max_tabs', 3, 0, 'global');

        $_REQUEST['module'] = 'Users';
        $_REQUEST['record'] = $current_user->id;
        require('modules/Users/EditView.php');

        $this->expectOutputRegex('/<select name="user_max_tabs".*<option label="3" value="3" selected="selected">3<\/option>.*<\/select>/ms');
    }
}

