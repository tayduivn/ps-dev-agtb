<?php
//FILE SUGARCRM flav=pro ONLY
class Bug36008Test extends Sugar_PHPUnit_Framework_TestCase {
    
    protected $teamsTableName;
    protected $anonymousUser1;
    protected $anonymousUser2;
    protected $anonymousUserIds;
    protected $oldPrivateTeams;
    protected $globalTeam;
    protected $db;
    
    function setUp()
    {
        $this->db = $GLOBALS['db'];
        $team = new Team();
        $this->teamsTableName = $team->getTableName();
        unset($team);
        
        $this->anonymousUser1 = SugarTestUserUtilities::createAnonymousUser();
        $this->anonymousUser2 = SugarTestUserUtilities::createAnonymousUser();
        
        $this->anonymousUserIds = array($this->anonymousUser1->id, $this->anonymousUser2->id);
        $resultOld = $this->db->query(
            'SELECT name, name2, associated_user_id, description 
             FROM ' . $this->teamsTableName . ' 
             WHERE private=1 AND associated_user_id IN(' . implode(',', $this->anonymousUserIds) . ')' );
        if ($resultOld) {
            while ($oldPrivateTeam = $this->db->fetchByAssoc($resultOld)) {
                $this->oldPrivateTeams[] = $oldPrivateTeam;
            } 
        }
       
        $resultGlobal = $this->db->query(
            'SELECT name, name2, associated_user_id, description 
             FROM ' . $this->teamsTableName . ' 
             WHERE id=1');
        $this->globalTeam = $this->db->fetchByAssoc($resultGlobal);
         
        $this->db->query('TRUNCATE TABLE ' . $this->teamsTableName);
        $_POST = array(	
            'module' => 'Administration',	
            'action' => 'RepairTeams',	
            'process' => '1',	
            'silent' => '0',	
            'process_global_team' => 'on',	
            'process_private_team' => 'on',	
            'button' => 'Rebuild'
        );	
        $_REQUEST['silent'] = 0;
        require_once 'modules/Administration/RepairTeams.php';
    }
    
    function testPrivateTeamsRepair()
    {
        $resultNew = $this->db->query(
            'SELECT name, name2, associated_user_id, description 
             FROM ' . $this->teamsTableName . ' 
             WHERE private=1 AND associated_user_id IN(' . implode(',', $this->anonymousUserIds) . ')' );
        if ($resultNew) {
            while ($newPrivateTeam = $this->db->fetchByAssoc($resultNew)) {
                $this->newPrivateTeams[] = $newPrivateTeam;
            } 
        }
        $this->assertEquals($this->oldPrivateTeams, $this->newPrivateTeams);
    }
    
    function testGlobalTeamRepair()
    {
        $resultGlobal = $this->db->query(
            'SELECT name, name2, associated_user_id, description 
             FROM ' . $this->teamsTableName . ' 
             WHERE id=1');
        $newGlobalTeam = $this->db->fetchByAssoc($resultGlobal);
        $this->assertEquals($this->globalTeam, $newGlobalTeam);
        foreach ($this->anonymousUserIds as $id) {
            $teamMembership = new TeamMembership();
            $this->assertTrue($teamMembership->retrieve_by_user_and_team($id, '1'));
        }
    }
    
    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }
}