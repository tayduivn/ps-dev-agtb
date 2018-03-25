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

namespace Sugarcrm\SugarcrmTests\Security\Teams;

use BeanFactory;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Security\Teams\TeamSet;

/**
 * @covers \Sugarcrm\Sugarcrm\Security\Teams\TeamSet
 */
class TeamSetTest extends TestCase
{
    /**
     * @test
     */
    public function withTeam()
    {
        $team1 = $this->createTeam();
        $team2 = $this->createTeam();

        $teamSet1 = new TeamSet($team1);
        $teamSet12 = new TeamSet($team1, $team2);

        $this->assertEquals($teamSet12, $teamSet1->withTeam($team2));
    }

    /**
     * @test
     */
    public function withoutTeam()
    {
        $team1 = $this->createTeam();
        $team2 = $this->createTeam();

        $teamSet2 = new TeamSet($team2);
        $teamSet12 = new TeamSet($team1, $team2);

        $this->assertEquals($teamSet2, $teamSet12->withoutTeam($team1));
    }

    /**
     * @test
     */
    public function withExistingTeam()
    {
        $team1 = $this->createTeam();
        $team2 = $this->createTeam();

        $teamSet12 = new TeamSet($team1, $team2);

        $this->assertSame($teamSet12, $teamSet12->withTeam($team1));
    }

    /**
     * @test
     */
    public function withoutNonExistingTeam()
    {
        $team1 = $this->createTeam();
        $team2 = $this->createTeam();

        $teamSet1 = new TeamSet($team1);

        $this->assertSame($teamSet1, $teamSet1->withoutTeam($team2));
    }

    /**
     * @test
     */
    public function persistEmpty()
    {
        $teamSet = new TeamSet();

        $this->expectException(\DomainException::class);
        $teamSet->persist();
    }

    /**
     * @test
     */
    public function persistSameId()
    {
        $team1 = $this->createTeam();
        $team2 = $this->createTeam();

        $teamSet12 = new TeamSet($team1, $team2);
        $teamSet12Id = $teamSet12->persist();

        $teamSet21 = new TeamSet($team2, $team1);
        $teamSet21Id = $teamSet21->persist();

        $this->assertSame($teamSet12Id, $teamSet21Id);
    }

    /**
     * @return \Team
     */
    private function createTeam()
    {
        $team = BeanFactory::newBean('Teams');
        $team->id = create_guid();

        return $team;
    }
}
