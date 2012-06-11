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
 
require_once('modules/SugarFeed/Dashlets/SugarFeedDashlet/SugarFeedDashlet.php');


/*
 * This test simulates a non admin user who has marketing role assigned, and renders the sugarfeeds dashlet.
 * This should not throw an error.  It was previously throwing an error from the group by clause
 * The test will catch a failure if no feed records are returned
 */
class Bug52634Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $user;
    private $l_ids = array();
    public function setUp()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('current_user');

        //set up the current user as a non admin with a marketing role
        $this->user = $GLOBALS['current_user'];

        //select and assign the marketing role
        $role_id = $GLOBALS['db']->getOne("select id from acl_roles where name = 'Marketing Administrator' ");
        $GLOBALS['current_user']->role_id = $role_id;
        $GLOBALS['db']->query("INSERT into acl_roles_users(id,user_id,role_id) values('".create_guid()."','".$GLOBALS['current_user']->id."','".$GLOBALS['current_user']->role_id."')");
        $GLOBALS['current_user']->save();

        //create a couple of leads to populate the sugar feed
        $l1 = SugarTestLeadUtilities::createLead();
        $l2 = SugarTestLeadUtilities::createLead();
        $this->l_ids[] = $l1->id;
        $this->l_ids[] = $l2->id;
    }

    public function tearDown()
    {
        foreach($this->l_ids as $id){
            $GLOBALS['db']->query("DELETE FROM leads WHERE id= '{$id}'");
        }

        SugarTestHelper::tearDown();
    }


    public function testSugarFeedsDashletGroupByWorksWithRole()
    {
        //initialize the sugarfeed dashlet and prepare the request
        $_REQUEST['module']='Home';
        $dashlet = new SugarFeedDashlet('sumID');

        //call the process function which will call the code that we are testing for db errors
        $dashlet->process();

        $this->assertNotEmpty(!empty($dashlet->lvs->data['pageData']['idIndex']), 'No entries were retrieved from sugarfeeds, check sql for "group by" error');

    }
}
?>
