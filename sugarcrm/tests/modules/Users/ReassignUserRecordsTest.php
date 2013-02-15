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

class ReassignUserRecordsTest extends Sugar_PHPUnit_Framework_OutputTestCase {

    private $user1;
    private $user2;
    private $bean;

    public function setUp()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');

        $this->user2 = $GLOBALS['current_user'];
        $this->user2->is_admin = 1;
        $this->user2->save();

        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('mod_strings', array('Users'));

        //Create another user for testing
        $this->user1 = SugarTestUserUtilities::createAnonymousUser();

        //Create a notification bean
        $this->bean = SugarTestNotificationUtilities::createNotification();
        $this->bean->name = 'Notification Test';
        $this->bean->assigned_user_id = $this->user2->id;
        $this->bean->save();
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestNotificationUtilities::removeAllCreatedNotifications();
        unset($_SESSION['reassignRecords']);
        unset($_POST['module']);
        unset($_POST['action']);
        unset($_POST['fromuser']);
        unset($_POST['touser']);
        unset($_POST['moudules']);
        unset($_POST['steponesubmit']);
    }

    /**
     * This method tests the reassignment notification code.  This particular test checks to ensure that the notification bean
     * does not cause problems when reassigning since we need code to filter out team specific fields.
     * @group user_reassignment
     */
    public function testReassignRecordForNotifications()
    {
        //simulate selecting notification module for reassignment
        $_SESSION['reassignRecords']['assignedModuleListCache'] = array('Notifications' => 'Notifications');
        $_SESSION['reassignRecords']['assignedModuleListCacheDisp'] = array ('Notifications' => 'Notifications');

        $_POST['module'] = 'Users';
        $_POST['action'] = 'reassignUserRecords';
        $_POST['fromuser'] = $this->user1->id;
        $_POST['touser'] = $GLOBALS['current_user']->id;
        $_POST['modules'] = array('Notifications');
        $_POST['steponesubmit'] = 'Next';

        global $app_list_strings, $beanFiles, $beanList, $current_user, $mod_strings, $app_strings;
        //Include the reassignUserRecords.php file to run it
        include('modules/Users/reassignUserRecords.php');

        $notificationBean = BeanFactory::getBean('Notifications', $this->bean->id);
        $this->assertEquals($this->user2->id, $notificationBean->assigned_user_id);
    }

}