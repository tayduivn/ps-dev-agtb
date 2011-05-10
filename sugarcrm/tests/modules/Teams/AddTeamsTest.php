<?php 
//FILE SUGARCRM flav=pro ONLY
require_once('modules/Teams/TeamSet.php');

/***
 * Test cases for Bug 23871
 */
class addTeamsTest extends Sugar_PHPUnit_Framework_TestCase
{
	private $teamSets, $anotherTeamSets;
	private $teamIds = array();
	private $teamSetsId = '';
	private $teamSetsIdSecondOne = '';
	
	public function setUp()
	{
		$this->teamSets = new TeamSet();
		$this->anotherTeamSets = new TeamSet();
		$this->teamIds[] = '8744c7d9-9e4b-2338-cb76-4ab0a3d0a65f';
		$this->teamIds[] = '8749a110-1d85-4562-fa23-4ab0a3c65e16';
		$this->teamIds[] = '874c1242-4645-898d-238a-4ab0a3f7e7c1';
	}
	
	public function tearDown()
	{
		$q = "DELETE from team_sets where id = '$this->teamSetsId'";
		$GLOBALS['db']->query($q);
		//if the second one doesn't match the first one, delete it
		if ($this->teamSetsId != $this->teamSetsIdSecondOne)
		{
			$q = "DELETE from team_sets where id = '$this->teamSetsIdSecondOne'";
			$GLOBALS['db']->query($q);
		}
		unset($this->teamSets);
		unset($this->anotherTeamSets);
	}
	
	public function testAddTeams()
	{
		$this->teamSetsId = $this->teamSets->addTeams($this->teamIds);
		//For given teamIds, if they already have teamSetsId, we shall get the same team set id
		$this->teamSetsIdSecondOne = $this->anotherTeamSets->addTeams($this->teamIds);
		$this->assertEquals($this->teamSetsIdSecondOne,$this->teamSetsId);
	}
}
?>