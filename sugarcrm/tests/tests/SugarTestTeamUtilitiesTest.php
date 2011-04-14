<?php
//FILE SUGARCRM flav=pro ONLY

class SugarTestTeamUtilitiesTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $_before_snapshot = array();
    
    public function setUp() 
    {
        $this->_before_snapshot = $this->_takeTeamDBSnapshot();
    }

    public function tearDown() 
    {
        SugarTestTeamUtilities::removeAllCreatedAnonymousTeams();
    }

    public function _takeTeamDBSnapshot() 
    {
        $snapshot = array();
        $query = 'SELECT * FROM teams';
        $result = $GLOBALS['db']->query($query);
        while ($row = $GLOBALS['db']->fetchByAssoc($result)) {
            $snapshot[] = $row;
        }
        return $snapshot;
    }

    public function testCanCreateAnAnonymousTeam() 
    {
        $team = SugarTestTeamUtilities::createAnonymousTeam();

        $this->assertInstanceOf('Team', $team);

        $after_snapshot = $this->_takeTeamDBSnapshot();
        $this->assertNotEquals($this->_before_snapshot, $after_snapshot, "Simply insure that something was added");
    }

    public function testAnonymousTeamHasARandomTeamName() 
    {
        $first_team = SugarTestTeamUtilities::createAnonymousTeam();
        $this->assertNotEquals($first_team->name, '', 'team name should not be empty');

        $second_team = SugarTestTeamUtilities::createAnonymousTeam();
        $this->assertNotEquals($first_team->name, $second_team->name,
            'each team should have a unique name property');
    }

    public function testCanTearDownAllCreatedAnonymousTeams() 
    {
        for ($i = 0; $i < 5; $i++) {
            SugarTestTeamUtilities::createAnonymousTeam();
        }
        SugarTestTeamUtilities::removeAllCreatedAnonymousTeams();
        
        $this->assertEquals($this->_before_snapshot, $this->_takeTeamDBSnapshot(),
            "removeAllCreatedAnonymousTeams() should have removed the team it added");
    }
}

