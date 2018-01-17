<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

use Sugarcrm\Sugarcrm\Security\Teams\TeamSet;

/**
 * @covers TeamSetManager
 * @uses \Sugarcrm\Sugarcrm\Security\Teams\TeamSet
 */
class TeamSetManagerTest extends Sugar_PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
    }

    public static function tearDownAfterClass()
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestTeamUtilities::removeAllCreatedAnonymousTeams();

        parent::tearDownAfterClass();
    }

    public function testGetTeamsForUser()
    {
        $contact = SugarTestContactUtilities::createContact();
        $team1 = SugarTestTeamUtilities::createAnonymousTeam(null, [
            'name' => 'Accounting',
        ]);
        $team2 = SugarTestTeamUtilities::createAnonymousTeam(null, [
            'name' => 'Science',
        ]);

        unset($contact->teams);
        $contact->load_relationship('teams');
        $contact->teams->add([$team1, $team2]);

        $teams = TeamSetManager::getUnformattedTeamsFromSet($contact->team_set_id);

        $this->assertArraySubset([
            [
                'id' => $team1->id,
                'name' => 'Accounting',
            ],
            [
                'id' => $team1->global_team,
                'name' => 'Global',
            ],
            [
                'id' => $team2->id,
                'name' => 'Science',
            ],
        ], $teams);
    }

    /**
     * @test
     */
    public function reassignRecords()
    {
        $contact1 = SugarTestContactUtilities::createContact();
        $contact2 = SugarTestContactUtilities::createContact();
        $contact3 = SugarTestContactUtilities::createContact();
        $contact4 = SugarTestContactUtilities::createContact();

        $team1 = SugarTestTeamUtilities::createAnonymousTeam();
        $team2 = SugarTestTeamUtilities::createAnonymousTeam();
        $team3 = SugarTestTeamUtilities::createAnonymousTeam();

        $this->assignRecordToTeams($contact1, $team1);
        $this->assignRecordToTeams($contact2, $team2, $team1);
        $this->assignRecordToTeams($contact3, $team2);
        $this->assignRecordToTeams($contact4, $team3);

        $teamSet13 = new TeamSet($team1, $team3);
        $teamSet13Id = $teamSet13->persist();

        TeamSetManager::reassignRecords([$team2], $team3);

        $fetchedContact1 = $this->fetchBean($contact1);
        $fetchedContact2 = $this->fetchBean($contact2);
        $fetchedContact3 = $this->fetchBean($contact3);

        // Contact #1 should not be affected
        $this->assertSame($contact1->team_id, $fetchedContact1->team_id);
        $this->assertSame($contact1->team_set_id, $fetchedContact1->team_set_id);

        // Contact #2 should be assigned to the newly created team set [Team 1, Team 3]
        $this->assertSame($team3->id, $fetchedContact2->team_id);
        $this->assertSame($teamSet13Id, $fetchedContact2->team_set_id);

        // Contact #3 should belong to the same team set as Contact #4 [Team 3]
        $this->assertSame($contact4->team_id, $fetchedContact3->team_id);
        $this->assertSame($contact4->team_set_id, $fetchedContact3->team_set_id);

        $this->assertTeamSetIsRemoved($contact2->team_set_id);
        $this->assertTeamSetIsRemoved($contact3->team_set_id);
        $this->assertTeamSetIsUnique($contact4);
    }

    private function assignRecordToTeams(SugarBean $bean, Team $pprimaryTeam, Team ...$otherTeams)
    {
        // unset the link to see the changes immediately reflected on the bean
        unset($bean->teams);

        $bean->load_relationship('teams');
        $bean->team_id = $pprimaryTeam->id;
        $bean->save();

        $bean->teams->replace($otherTeams);
    }

    private function fetchBean(SugarBean $bean)
    {
        return BeanFactory::retrieveBean($bean->module_name, $bean->id, [
            'use_cache' => false,
            'disable_row_level_security' => 1,
        ]);
    }

    private function assertTeamSetIsUnique(SugarBean $bean)
    {
        $query = 'SELECT COUNT(*) FROM team_sets_modules WHERE team_set_id = ? AND module_table_name = ?';
        $conn = DBManagerFactory::getConnection();
        $stmt = $conn->executeQuery($query, [$bean->team_set_id, $bean->table_name]);

        $this->assertEquals(1, $stmt->fetchColumn());
    }

    private function assertTeamSetIsRemoved($id)
    {
        $query = 'SELECT COUNT(*) FROM team_sets_modules WHERE team_set_id = ?';
        $conn = DBManagerFactory::getConnection();
        $stmt = $conn->executeQuery($query, [$id]);

        $this->assertEquals(0, $stmt->fetchColumn());
    }
}
