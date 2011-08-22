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
 * Test cases for Bug 42379
 */
class Bug42379Test extends Sugar_PHPUnit_Framework_TestCase
{
	private $teamSets;
	private $teamIds = array();
    private $teamSetsId = '';

	
	public function setUp()
	{

		$this->teamIds[] = '8744c7d9-9e4b-2338-cb76-4ab0a3d0a65f';
		$this->teamIds[] = '8749a110-1d85-4562-fa23-4ab0a3c65e16';
		$this->teamIds[] = '874c1242-4645-898d-238a-4ab0a3f7e7c1';

        sort($this->teamIds, SORT_STRING);
	}
	
	public function tearDown()
	{
		unset($this->teamSets);
        unset($this->teamIds);
        unset($this->teamSetsId);

	}
	
	public function testGetStatisticsTeamIds()
	{
        $this->teamSets = new TeamSetBug42379Test();

        // we could also call addTeams, which in turn calls getStatistics
        // but this is a more direct test of getStatistics
	    $stats = $this->teamSets->getStatistics($this->teamIds);
        $this->assertEquals($this->teamIds,
                            $stats['team_ids'],
                            "testing to make sure that team IDs are set");

	}

    public function testGetStatisticsWithOneItem(){

        $this->teamSets = new TeamSetBug42379Test();

        // add just one item from TeamIDs
       $this->teamSetsId = $this->teamSets->addTeams( (array_slice($this->teamIds,0,1)) );
       $stats = $this->teamSets->getStatistics((array_slice($this->teamIds,0,1)));
       $this->assertEquals(md5($this->teamIds[0]),
                            $stats['team_md5'],
                            "testing to make sure that 1 team ID gets added properly");
        

    }

    public function testGetStatisticsTeamCount() {

        $this->teamSets = new TeamSetBug42379Test();
        $this->teamSetsId = $this->teamSets->addTeams($this->teamIds);

        $stats = $this->teamSets->getStatistics($this->teamIds);

        $this->assertEquals( count($this->teamIds),
                            $stats['team_count'],
                            "make sure that all teams get added");
    }

    public function testGetStatisticsWithManyItems() {
        $this->teamSets = new TeamSetBug42379Test();
        $this->teamSetsId = $this->teamSets->addTeams($this->teamIds);

        $stats = $this->teamSets->getStatistics($this->teamIds);
        $team_md5 = '';

        foreach ($this->teamIds as $team_id) {

            $team_md5 .= $team_id;

        }
            // run the md5 on the whole string of team_ids         
        $team_md5 = md5($team_md5);


        $this->assertEquals( $team_md5,
                            $stats['team_md5'],
                            "make sure that the resulting md5 matches");
    }


    /* This test doesn't actually test the getStatistics method directly
     * The getStatistics is called by addTeams
     * It simply checks that when adding teams in the TeamSet,
     * the primary team gets selected properly.
     */
    public function testGetStatisticsPrimaryTeamID() {

        $this->teamSets = new TeamSetBug42379Test();
        $this->teamSetsId = $this->teamSets->addTeams($this->teamIds);
        $count = count($this->teamIds);
        $this->assertEquals( $this->teamIds[$count-1],
                            $this->teamSets->getPrimaryTeamId(),
                            "make sure that primary team ID is correctly set when sending multiple team IDs");

    }
    
    /* This test doesn't actually test the getStatistics method at all
     * It simply checks that when adding teams in the TeamSet,
     * the primary team gets selected properly.
     * If the proper team is not selected, then it could mess with getStatistics.
     */
    public function testGetStatisticsPrimaryTeamIDWithOneTeam() {

        $this->teamSets = new TeamSetBug42379Test();
        $this->teamSetsId = $this->teamSets->addTeams((array_slice($this->teamIds,0,1)));
        $this->assertEquals( $this->teamIds[0],
                            $this->teamSets->getPrimaryTeamId(),
                            "make sure that primary team ID is correctly set when sending only 1 team ID");

    }

}

/*
 * Create mock of TeamSet to get access to _getStatistics method for testing
 */
class TeamSetBug42379Test extends TeamSet
{
    public function getStatistics($team_ids)
    {
        return $this->_getStatistics($team_ids);
    }


}
?>