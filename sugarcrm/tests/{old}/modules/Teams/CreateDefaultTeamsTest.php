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

use PHPUnit\Framework\TestCase;

class CreateDefaultTeamsTest extends TestCase
{
    private $_user = null;
    private $_contact = null;

    protected function setUp() : void
    {
        // in case these globals are deleted before the test is run
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');

        $this->_user = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user'] = $this->_user;
        $GLOBALS['db']->query("DELETE FROM contacts WHERE first_name = 'Collin' AND last_name = 'Lee'");
    }
    
    protected function tearDown() : void
    {
        unset($GLOBALS['current_user']);
     
        if ($this->_contact instanceof Contact && !empty($this->_contact->id)) {
            $GLOBALS['db']->query("DELETE FROM contacts WHERE id = '{$this->_contact->id}'");
        }
        
        $this->_contact = null;
        
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    /**
     * @dataProvider providerTeamName
     */
    public function testGetCorrectTeamName($team, $expected)
    {
        $this->assertEquals(
            $team->get_summary_text(),
            $expected,
            "{$expected} team name did not match"
        );
    }
    
    public function providerTeamName()
    {
        $team1 = BeanFactory::newBean('Teams');
        $team1->name = 'Will';
        $team1->name_2 = 'Westin';
        
        $team2 = BeanFactory::newBean('Teams');
        $team2->name = 'Will';
        
        return [
            [$team1,'Will Westin'],
            [$team2,'Will'],
        ];
    }
}
