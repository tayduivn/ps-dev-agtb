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
 
require_once('modules/Teams/TeamSet.php');

/***
 * Test cases for Bug 23871
 */
class AddTeamsTest extends Sugar_PHPUnit_Framework_TestCase
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
