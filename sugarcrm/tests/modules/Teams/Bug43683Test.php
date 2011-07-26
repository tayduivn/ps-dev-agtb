<?php
//FILE SUGARCRM flav=pro ONLY 
require_once('modules/Teams/Team.php');
require_once('modules/Teams/TeamMembership.php');

class Bug43683Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var User
     */
    private $user1;
    /**
     * @var User
     */
    private $user2;

    public function setUp() 
    {
        $this->user1 = SugarTestUserUtilities::createAnonymousUser();
        $this->user2 = SugarTestUserUtilities::createAnonymousUser(false);
        $this->user2->reports_to_id = $this->user1->id;
        $this->user2->save();
    }

    public function tearDown() 
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    /**
     * @group	bug43683
     */
    public function testAddUserToTeamWithManagers()
    {
        $team = new Team();
        $team->retrieve($this->user2->getPrivateTeamID());
        $team->add_user_to_team($this->user2->id);
        $team_membership = new TeamMembership();
        $this->assertTrue($team_membership->retrieve_by_user_and_team($this->user1->id, $team->id), 'Implicit membership exists');
    }
}