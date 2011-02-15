<?php
//FILE SUGARCRM flav=pro ONLY 
class Bug42907Test extends Sugar_PHPUnit_Framework_TestCase
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

?>