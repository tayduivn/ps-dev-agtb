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
}
