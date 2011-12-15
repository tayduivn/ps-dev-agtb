<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
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

require_once('modules/Users/User.php');
require_once('modules/Users/UserViewHelper.php');

/**
 * Bug #49175
 * When user is admin doesn't display on user detailview
 * @ticket 49175
 */
class Bug49175Test extends  Sugar_PHPUnit_Framework_TestCase
{
    private $user;

    public function setUp()
    {
        $this->user = SugarTestUserUtilities::createAnonymousUser();
    }

    public function userTypes()
    {
        return array(
            array('is_admin' => '1', 'is_group' => '0', 'portal_only' => '0', 'type' => 'Administrator'),
            array('is_admin' => '0', 'is_group' => '1', 'portal_only' => '0', 'type' => 'GROUP'),
            array('is_admin' => '0', 'is_group' => '0', 'portal_only' => '1', 'type' => 'PORTAL_ONLY'),
            array('is_admin' => '0', 'is_group' => '0', 'portal_only' => '0', 'type' => 'RegularUser')
        );
    }

    /**
     * @group 49175
     * @dataProvider userTypes
     */
    public function testGetUserType($is_admin, $is_group, $portal_only, $type)
    {
        $this->user->is_admin = $is_admin;
        $this->user->is_group = $is_group;
        $this->user->portal_only = $portal_only;

        $userViewHelper = new MockUserViewHelper();
        $userViewHelper->setUserType($this->user);
        $this->assertEquals($this->user->user_type, $type);
    }
}

class MockUserViewHelper extends UserViewHelper {

    //override the constructor, don't bother passing Smarty instance, etc.
    public function __construct() {

    }
}

?>
