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
 
/**
 * Bug44607Test
 * 
 * This bug tests the case where a user with a reports to id value that pointed to a user
 * that did not exist in the system or was deleted (record not just deleted flag) would cause
 * the code in Team.php (add_user_to_team) to run in an infinite loop.  Obviously, we do not
 * set the code to run in an infinite loop, but we do test that we get out of it and that no
 * team_membership entries are created for a user that does not exist.
 *
 */
class Bug44607Test extends Sugar_PHPUnit_Framework_TestCase
{
	var $testUser;
	var $testUser2;
	
    public function setUp() 
    {  
       $this->testUser = SugarTestUserUtilities::createAnonymousUser();
       $this->testUser2 = SugarTestUserUtilities::createAnonymousUser();
    }    
    
    public function tearDown() 
    {
       SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
	   $this->testUser = null;
	   $this->testUser2 = null;
    } 	
	
    /**
     * testAddUserToTeam
     * 
     * 
     */
    public function testAddUserToTeam()
    {
        //Create a fake reports_to_id
        $this->testUser->reports_to_id = md5($this->testUser->id);    	

		$team = new Team();
		$team->add_user_to_team($this->testUser->id);

		$results = $GLOBALS['db']->query("SELECT count(*) as total FROM team_memberships WHERE user_id = '{$this->testUser->reports_to_id}'");
		if(!empty($results))
		{
			$row = $GLOBALS['db']->fetchByAssoc($results);
			$this->assertEquals($row['total'], 0, 'Assert that no team_membership entries were created');
		}
		
        $this->testUser->reports_to_id = $this->testUser2->id; 
        $team = new Team();  	
		$team->add_user_to_team($this->testUser->id);
		
    	$results = $GLOBALS['db']->query("SELECT count(*) as total FROM team_memberships WHERE user_id = '{$this->testUser->reports_to_id}'");
		if(!empty($results))
		{
			$row = $GLOBALS['db']->fetchByAssoc($results);
			$this->assertNotEquals($row['total'], 0, 'Assert that team_membership entries were created');
		}		
    }  
}