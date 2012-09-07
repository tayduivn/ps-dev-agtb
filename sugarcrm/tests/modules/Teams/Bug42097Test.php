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

class Bug42097Test extends Sugar_PHPUnit_Framework_TestCase
{
	var $testUser;
	
    public function setUp() 
    {  
       $this->testUser = SugarTestUserUtilities::createAnonymousUser();
    }    
    
    public function tearDown() 
    {
       SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
	   $this->testUser = null;
    } 	
	
    
    /**
     * testRemoveUserFromTeam
     * 
     * This test checks the case where a user is removed from his own private team.  
     * We are expecting an exception to be thrown.
     */    
    public function testRemoveUserFromTeam() 
    {
	   $team = new Team();
	   $team->retrieve($this->testUser->getPrivateTeamID());
	   $exceptionThrown = false;
	   try {
	     $team->remove_user_from_team($this->testUser->id);
	   } catch(Exception $ex) {
	   	 $exceptionThrown = true;
	   }
	   
	   $this->assertTrue($exceptionThrown, 'Assert that an exception was thrown for attempting to remove user off own private team');
    }
    
}
