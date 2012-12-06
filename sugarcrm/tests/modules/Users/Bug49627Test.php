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

require_once('modules/Users/UserViewHelper.php');
require_once 'include/Sugar_Smarty.php';

/**
 * Bug49627Test.php
 *
 * This unit test tests the user type dropdown items created from the UserViewHelper class.
 * It runs tests against the normal user, portal and group user types.
 */
class Bug49627Test extends Sugar_PHPUnit_Framework_TestCase
{

var $normalUser;
//BEGIN SUGARCRM flav=ent ONLY
var $portalUser;
//END SUGARCRM flav=ent ONLY
var $groupUser;


public function setUp()
{
    global $current_user;
    $current_user = SugarTestUserUtilities::createAnonymousUser();

    $this->normalUser = SugarTestUserUtilities::createAnonymousUser(false);
    $this->normalUser->id = create_guid();
    $this->normalUser->user_type = 'RegularUser';

    //BEGIN SUGARCRM flav=ent ONLY
    $this->portalUser = SugarTestUserUtilities::createAnonymousUser(false);
    $this->portalUser->id = create_guid();
    $this->portalUser->is_portal = 1;
    $this->portalUser->user_type = 'PORTAL_ONLY';
    //END SUGARCRM flav=ent ONLY

    $this->groupUser = SugarTestUserUtilities::createAnonymousUser(false);
    $this->groupUser->id = create_guid();
    $this->groupUser->is_group = 1;
    $this->groupUser->user_type = 'GROUP';

    $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
    $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
}

public function tearDown()
{
    SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
}

public function testSetupUserTypeDropdownNormalUser()
{
    $smarty = new Sugar_Smarty();
    $userViewHelper = new UserViewHelperMock($smarty, $this->normalUser);
    $userViewHelper->usertype = 'RegularUser';
    $userViewHelper->setupUserTypeDropdown();
    $dropdown = $userViewHelper->ss->get_template_vars('USER_TYPE_DROPDOWN');
    $user_type_readonly = $userViewHelper->ss->get_template_vars('USER_TYPE_READONLY');
    $this->assertRegExp('/RegularUser/', $dropdown);
    $this->assertRegExp('/RegularUser/', $user_type_readonly);
    $this->assertNotRegExp('/PORTAL_ONLY/', $dropdown);
    $this->assertNotRegExp('/PORTAL_ONLY/', $user_type_readonly);
    $this->assertNotRegExp('/GROUP/', $dropdown);
    $this->assertNotRegExp('/GROUP/', $user_type_readonly);

    $this->normalUser->id = '';
    $userViewHelper = new UserViewHelperMock($smarty, $this->normalUser);
    $userViewHelper->usertype = 'RegularUser';
    $userViewHelper->setupUserTypeDropdown();
    $dropdown = $userViewHelper->ss->get_template_vars('USER_TYPE_DROPDOWN');
    $user_type_readonly = $userViewHelper->ss->get_template_vars('USER_TYPE_READONLY');
    $this->assertRegExp('/RegularUser/', $dropdown);
    $this->assertRegExp('/RegularUser/', $user_type_readonly);
    $this->assertNotRegExp('/PORTAL_ONLY/', $dropdown);
    $this->assertNotRegExp('/PORTAL_ONLY/', $user_type_readonly);
    $this->assertNotRegExp('/GROUP/', $dropdown);
    $this->assertNotRegExp('/GROUP/', $user_type_readonly);
}

public function testSetupUserTypeDropdownGroupUser()
{
    $smarty = new Sugar_Smarty();
    $userViewHelper = new UserViewHelperMock($smarty, $this->groupUser);
    $userViewHelper->usertype = 'GROUP';
    $userViewHelper->setupUserTypeDropdown();
    $dropdown = $userViewHelper->ss->get_template_vars('USER_TYPE_DROPDOWN');
    $user_type_readonly = $userViewHelper->ss->get_template_vars('USER_TYPE_READONLY');
    $this->assertRegExp('/GROUP/', $dropdown);
    $this->assertRegExp('/GROUP/', $user_type_readonly);
    $this->assertNotRegExp('/PORTAL_ONLY/', $dropdown);
    $this->assertNotRegExp('/PORTAL_ONLY/', $user_type_readonly);
    $this->assertNotRegExp('/RegularUser/', $dropdown);
    $this->assertNotRegExp('/RegularUser/', $user_type_readonly);

    $userViewHelper = new UserViewHelperMock($smarty, $this->groupUser);
    $this->groupUser->id = '';
    $userViewHelper->usertype = 'GROUP';
    $userViewHelper->setupUserTypeDropdown();
    $dropdown = $userViewHelper->ss->get_template_vars('USER_TYPE_DROPDOWN');
    $user_type_readonly = $userViewHelper->ss->get_template_vars('USER_TYPE_READONLY');
    $this->assertRegExp('/GROUP/', $dropdown);
    $this->assertRegExp('/GROUP/', $user_type_readonly);
    $this->assertNotRegExp('/PORTAL_ONLY/', $dropdown);
    $this->assertNotRegExp('/PORTAL_ONLY/', $user_type_readonly);
    $this->assertNotRegExp('/RegularUser/', $dropdown);
    $this->assertNotRegExp('/RegularUser/', $user_type_readonly);
}

//BEGIN SUGARCRM flav=ent ONLY
public function testSetupUserTypeDropdownPortalUser()
{
    $smarty = new Sugar_Smarty();
    $userViewHelper = new UserViewHelperMock($smarty, $this->portalUser);
    $userViewHelper->usertype = 'PORTAL_ONLY';
    $userViewHelper->setupUserTypeDropdown();
    $dropdown = $userViewHelper->ss->get_template_vars('USER_TYPE_DROPDOWN');
    $user_type_readonly = $userViewHelper->ss->get_template_vars('USER_TYPE_READONLY');
    $this->assertRegExp('/PORTAL_ONLY/', $dropdown);
    $this->assertRegExp('/PORTAL_ONLY/', $user_type_readonly);
    $this->assertNotRegExp('/RegularUser/', $dropdown);
    $this->assertNotRegExp('/RegularUser/', $user_type_readonly);
    $this->assertNotRegExp('/GROUP/', $dropdown);
    $this->assertNotRegExp('/GROUP/', $user_type_readonly);

    $this->portalUser->id = '';
    $userViewHelper = new UserViewHelperMock($smarty, $this->portalUser);
    $userViewHelper->usertype = 'PORTAL_ONLY';
    $userViewHelper->setupUserTypeDropdown();
    $dropdown = $userViewHelper->ss->get_template_vars('USER_TYPE_DROPDOWN');
    $user_type_readonly = $userViewHelper->ss->get_template_vars('USER_TYPE_READONLY');
    $this->assertRegExp('/PORTAL_ONLY/', $dropdown);
    $this->assertRegExp('/PORTAL_ONLY/', $user_type_readonly);
    $this->assertNotRegExp('/RegularUser/', $dropdown);
    $this->assertNotRegExp('/RegularUser/', $user_type_readonly);
    $this->assertNotRegExp('/GROUP/', $dropdown);
    $this->assertNotRegExp('/GROUP/', $user_type_readonly);

}
//END SUGARCRM flav=ent ONLY

}

//UserViewHelperMock
//This class turns the $ss class variable to have public access
class UserViewHelperMock extends UserViewHelper
{
    var $ss;
}
