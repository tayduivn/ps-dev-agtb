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

use Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\Command\StateAwareRebuild;
use Sugarcrm\Sugarcrm\DependencyInjection\Container;

/**
 * @covers \Sugarcrm\Sugarcrm\Bean\Visibility\Strategy\TeamSecurity\Denormalized
 * @covers NormalizedTeamSecurity
 * @covers TeamSecurity
 */
class TeamSecurityTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var User
     */
    private static $admin;

    /**
     * @var array<string,User>
     */
    private static $users = [];

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp("beanList");
        SugarTestHelper::setUp("beanFiles");

        self::$admin = SugarTestHelper::setUp('current_user', [true, true]);

        self::configure(true, false);

        $command = Container::getInstance()->get(StateAwareRebuild::class);
        $command();

        self::createFixtures();
    }

    public static function tearDownAfterClass()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestTeamUtilities::removeAllCreatedAnonymousTeams();
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestContactUtilities::removeAllCreatedContacts();

        SugarTestHelper::tearDown();
    }

    /**
     * @test
     * @dataProvider sugarQueryProvider
     */
    public function sugarQueryWithRelatedField($useDenorm, $useWhere, $userName, array $expected)
    {
        global $current_user;

        $current_user = self::$users[$userName];

        self::configure($useDenorm, $useWhere);

        $query = new SugarQuery();
        $query->from(BeanFactory::newBean('Contacts'));
        $query->select('last_name', 'account_name');
        $query->where()->equals('created_by', self::$admin->id);
        $query->orderBy('last_name', 'ASC');
        $data = $query->execute();

        $this->assertCount(count($expected), $data);
        $this->assertArraySubset($expected, $data);
    }

    public function sugarQueryProvider()
    {
        $expected = [
            'User #1' => [
                [
                    'last_name' => 'Contact #1',
                    'account_name' => 'Account #1',
                ], [
                    'last_name' => 'Contact #2',
                    'account_name' => 'Account #2',
                ],
            ],
            'User #2' => [
                [
                    'last_name' => 'Contact #2',
                    'account_name' => 'Account #2',
                ],
            ],
            'User #3' => [
                [
                    'last_name' => 'Contact #3',
                    'account_name' => 'Account #3',
                ],
            ],
        ];

        foreach ($expected as $userName => $data) {
            foreach (self::configurationProvider() as $configName => list($useDenorm, $useWhere)) {
                yield sprintf('%s, %s', $userName, $configName) => [$useDenorm, $useWhere, $userName, $data];
            }
        }
    }

    /**
     * @test
     * @dataProvider rowsAndColumnsReportProvider
     */
    public function rowsAndColumnsReportByTwoModules($useDenorm, $useWhere, $userName, array $expected)
    {
        $definition = [
            'display_columns' => [
                [
                    'name' => 'name',
                    'table_key' => 'self',
                ],
                [
                    'name' => 'user_name',
                    'table_key' => 'Opportunities:assigned_user_link',
                ],
            ],
            'module' => 'Opportunities',
            'group_defs' => [],
            'summary_columns' => [],
            'order_by' => [
                [
                    'name' => 'name',
                    'table_key' => 'self',
                ],
            ],
            'report_type' => 'tabular',
            'full_table_list' => [
                'self' => [
                    'value' => 'Opportunities',
                    'module' => 'Opportunities',
                ],
                'Opportunities:assigned_user_link' => [
                    'parent' => 'self',
                    'link_def' => [
                        'name' => 'assigned_user_link',
                        'relationship_name' => 'opportunities_assigned_user',
                        'link_type' => 'one',
                        'table_key' => 'Opportunities:assigned_user_link',
                    ],
                    'module' => 'Users',
                ],
                'Opportunities:created_by_link' => [
                    'parent' => 'self',
                    'link_def' => [
                        'name' => 'created_by_link',
                        'relationship_name' => 'opportunities_created_by',
                        'module' => 'Users',
                        'table_key' => 'Opportunities:created_by_link',
                    ],
                    'module' => 'Users',
                ],
            ],
            'filters_def' => [
                'Filter_1' => [
                    'operator' => 'AND',
                    [
                        'name' => 'user_name',
                        'table_key' => 'Opportunities:created_by_link',
                        'qualifier_name' => 'is',
                        'input_name0' => self::$admin->id,
                    ],
                ],
            ],
        ];

        $data = $this->runReport($definition, $useDenorm, $useWhere, $userName);

        $this->assertCount(count($expected), $data);
        $this->assertArraySubset($expected, $data);
    }

    public static function rowsAndColumnsReportProvider()
    {
        $expected = [
            'User #1' => [
                [
                    'opportunities_name' => 'Opportunity #1',
                ], [
                    'opportunities_name' => 'Opportunity #2',
                ],
            ],
            'User #2' => [
                [
                    'opportunities_name' => 'Opportunity #2',
                ],
            ],
            'User #3' => [
                [
                    'opportunities_name' => 'Opportunity #3',
                ],
            ],
        ];

        foreach ($expected as $userName => $data) {
            foreach (self::configurationProvider() as $configName => list($useDenorm, $useWhere)) {
                yield sprintf('%s, %s', $userName, $configName) => [$useDenorm, $useWhere, $userName, $data];
            }
        }
    }

    /**
     * Runs a report with the given definition run under the given configuration on behalf of the given user
     */
    private function runReport($definition, $useDenorm, $useWhere, $userName)
    {
        global $current_user;

        $current_user = self::$users[$userName];

        self::configure($useDenorm, $useWhere);

        $reporter = new Report(json_encode($definition));
        $reporter->run_query();

        $data = [];

        while (($row = $reporter->db->fetchByAssoc($reporter->result))) {
            $data[] = $row;
        }

        return $data;
    }

    /**
     * Returns named visibility configurations to be tested
     *
     * @return array<string,mixed[]>
     */
    private static function configurationProvider()
    {
        return [
            'denormalized' => [true, false],
            'join' => [false, false],
            'where' => [false, true],
        ];
    }

    /**
     * to create test beans
     */
    private static function createFixtures()
    {
        $user1 = SugarTestUserUtilities::createAnonymousUser();
        $user2 = SugarTestUserUtilities::createAnonymousUser();
        $user3 = SugarTestUserUtilities::createAnonymousUser();

        $team1 = SugarTestTeamUtilities::createAnonymousTeam();
        $team2 = SugarTestTeamUtilities::createAnonymousTeam();
        $team3 = SugarTestTeamUtilities::createAnonymousTeam();

        // User #1 belongs to Teams #1 and #2
        $team1->add_user_to_team($user1->id);
        $team2->add_user_to_team($user1->id);

        // User #2 belongs to Team #2
        $team2->add_user_to_team($user2->id);

        // User #3 belongs to Teams #1 and #2
        $team3->add_user_to_team($user3->id);

        $account1 = self::createAccount('Account #1', $team1);
        self::createContact('Contact #1', $account1, $team1);
        self::createOpportunity('Opportunity #1', $account1, $user1, $team1);

        $account2 = self::createAccount('Account #2', $team2);
        self::createContact('Contact #2', $account2, $team2);
        self::createOpportunity('Opportunity #2', $account2, $user2, $team2);

        $account3 = self::createAccount('Account #3', $team3);
        self::createContact('Contact #3', $account3, $team3);
        self::createOpportunity('Opportunity #3', $account3, $user3, $team3);

        self::$users = [
            'User #1' => $user1,
            'User #2' => $user2,
            'User #3' => $user3,
        ];
    }

    private static function createAccount($name, Team $team)
    {
        return SugarTestAccountUtilities::createAccount('', [
            'name' => $name,
            'team_id' => $team->id,
        ]);
    }

    private static function createContact($name, Account $account, Team $team)
    {
        $contact = SugarTestContactUtilities::createContact('', [
            'last_name' => $name,
            'team_id' => $team->id,
        ]);

        $contact->load_relationship('accounts');
        $contact->accounts->add($account);
    }

    private static function createOpportunity($name, Account $account, User $user, Team $team)
    {
        $bean = SugarTestOpportunityUtilities::createOpportunity('', $account);
        $bean->team_id = $team->id;
        $bean->team_set_id = $account->team_set_id;
        $bean->name = $name;
        $bean->amount = 2000.00;
        $bean->assigned_user_id = $user->id;

        $bean->save();
    }

    /**
     * helper method, to set config flags for different options
     *
     * @param bool $useDenorm
     * @param bool $useWhere
     */
    private static function configure($useDenorm, $useWhere)
    {
        global $sugar_config;

        $sugar_config['perfProfile'] = [
            'TeamSecurity' => [
                'default' => [
                    'use_denorm' => $useDenorm,
                    'where_condition' => $useWhere,
                ],
            ],
        ];

        SugarConfig::getInstance()->clearCache();
    }
}