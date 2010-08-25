<?php
//FILE SUGARCRM flav=pro ONLY
require_once('tests/include/workflow/Bug32738/Bug32738Test.php');


class Bug38859Test extends Bug32738Test 
{ 
    
	function test_workflow() {
    	$this->test_account->description = 'Hey Lady!';
    	$this->test_account->team_id = $this->test_team2->id;
    	$this->test_account->team_set_id = $this->test_team2->id;
    	$this->test_account->save();
    	//Assert that the description was changed by the workflow
    	$this->assertTrue($this->test_account->description == 'Hey Man!');
    	//Assert that the team_id change was preserved
    	$this->assertTrue($this->test_account->team_id == $this->test_team2->id);
    	//Assert that the team_set_id change was preserved
    	$this->assertTrue($this->test_account->team_set_id == $this->test_team2->id);
    }
    
}

?>