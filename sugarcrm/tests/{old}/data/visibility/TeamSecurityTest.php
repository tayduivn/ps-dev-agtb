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
use Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\State;
use Sugarcrm\Sugarcrm\DependencyInjection\Container;

/**
 * TeamSecurity integration testing
 */
class TeamSecurityTest extends Sugar_PHPUnit_Framework_TestCase
{

    const RECORD_COUNT = 3;

    const TEST_SALES_STAGE = 'Prospecting';
    const TEST_ACCOUNT_TYPE = 'Customer';

    protected static $adminUser;
    protected static $testBeans = [];
    protected static $userList = [];
    protected static $teamList = [];

    protected static $testModules = ['Accounts', 'Contacts', 'Notes', 'Opportunities'];

    protected static $randomId;

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp("beanList");
        SugarTestHelper::setUp("beanFiles");

        // turn on use_denorm and rebuild denorm table
        self::setConfigAndDenormState(true, true, false);
        self::rebuildDenormTable();

        // set admin user
        $GLOBALS['current_user'] = self::getAdminUser();

        // create non-admin users and Teams
        self::createUsersAndTeams();

        // create test beans
        self::createTestBeans();
    }

    public static function tearDownAfterClass()
    {
        self::deleteRelateTestBeans();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestTeamUtilities::removeAllCreatedAnonymousTeams();
        SugarTestRevenueLineItemUtilities::removeAllCreatedRevenueLineItems();
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestNoteUtilities::removeAllCreatedNotes();

        global $sugar_config;
        self::setConfigAndDenormState(false, false, false);
        unset($sugar_config['perfProfile']);

        SugarTestHelper::tearDown();
    }

    /**
     *  test TeamSecurityDenorm via Filter Api, this is for testing SugarQuery
     *
     * @dataProvider providerTestTeamSecurityFromFilterApi
     */
    public function testTeamSecurityFromFilterApi($request, $userIndex, $states, $expected, $unexpected)
    {
        global $current_user;

        // set current user
        $current_user = self::$userList[$userIndex];

        // set states
        self::setConfigAndDenormState($states['useDenorm'], false, $states['useWhere']);

        // call FilterApi
        $filterApi = new FilterApi();
        $serviceMock = SugarTestRestUtilities::getRestServiceMock();
        $results = $filterApi->filterList($serviceMock, $request);

        // verify individual records
        $this->assertArraySubset($expected, $results['records']);
        foreach ($unexpected as $record) {
            foreach ($record as $key => $value) {
                $this->assertFalse($this->findRecord($results['records'], $key, $value));
            }
        }
    }

    public function providerTestTeamSecurityFromFilterApi()
    {
        return [
            // test Accounts, denorm on
            [
                [
                    '__sugar_url' => 'v11/Accounts',
                    'view' => 'list',
                    'fields' => 'following,my_favorite',
                    'max_num' => -1,
                    'module' => 'Accounts',
                ],
                0,
                ['useDenorm' => true, 'useWhere' => false],
                [
                    ['name' => self::createName('Accounts', 1)],
                    ['name' => self::createName('Accounts', 0)],
                ],
                [['name' => self::createName('Accounts', 2)]],
            ],
            // test Accounts, denorm off, use where off
            [
                [
                    '__sugar_url' => 'v11/Accounts',
                    'view' => 'list',
                    'fields' => 'following,my_favorite',
                    'max_num' => -1,
                    'module' => 'Accounts',
                ],
                0,
                ['useDenorm' => false, 'useWhere' => false],
                [
                    ['name' => self::createName('Accounts', 1)],
                    ['name' => self::createName('Accounts', 0)],
                ],
                [['name' => self::createName('Accounts', 2)]],
            ],
            // test Accounts, denorm off, use where on
            [
                [
                    '__sugar_url' => 'v11/Accounts',
                    'view' => 'list',
                    'fields' => 'following,my_favorite',
                    'max_num' => -1,
                    'module' => 'Accounts',
                ],
                0,
                ['useDenorm' => false, 'useWhere' => true],
                [
                    ['name' => self::createName('Accounts', 1)],
                    ['name' => self::createName('Accounts', 0)],
                ],
                [['name' => self::createName('Accounts', 2)]],
            ],
            // test Contacts, use denorm on
            [
                [
                    '__sugar_url' => 'v11/Contacts',
                    'view' => 'list',
                    'fields' => 'following,my_favorite',
                    'max_num' => -1,
                    'module' => 'Contacts',
                ],
                1,
                ['useDenorm' => true, 'useWhere' => false],
                [['first_name' => self::createName('Contacts', 1)]],
                [
                    ['first_name' => self::createName('Contacts', 2)],
                    ['first_name' => self::createName('Contacts', 0)],
                ],
            ],
            // test Contacts, use denorm off, use where off
            [
                [
                    '__sugar_url' => 'v11/Contacts',
                    'view' => 'list',
                    'fields' => 'following,my_favorite',
                    'max_num' => -1,
                    'module' => 'Contacts',
                ],
                1,
                ['useDenorm' => false, 'useWhere' => false],
                [['first_name' => self::createName('Contacts', 1)]],
                [
                    ['first_name' => self::createName('Contacts', 2)],
                    ['first_name' => self::createName('Contacts', 0)],
                ],
            ],
            // test Contacts, use denorm off, use where on
            [
                [
                    '__sugar_url' => 'v11/Contacts',
                    'view' => 'list',
                    'fields' => 'following,my_favorite',
                    'max_num' => -1,
                    'module' => 'Contacts',
                ],
                1,
                ['useDenorm' => false, 'useWhere' => true],
                [['first_name' => self::createName('Contacts', 1)]],
                [
                    ['first_name' => self::createName('Contacts', 2)],
                    ['first_name' => self::createName('Contacts', 0)],
                ],
            ],
            // test Notes, use denorm on
            [
                [
                    '__sugar_url' => 'v11/Notes',
                    'view' => 'list',
                    'fields' => 'following,my_favorite',
                    'max_num' => -1,
                    'module' => 'Notes',
                ],
                2,
                ['useDenorm' => true, 'useWhere' => false],
                [['name' => self::createName('Notes', 2)]],
                [
                    ['name' => self::createName('Notes', 1)],
                    ['name' => self::createName('Notes', 0)],
                ],
            ],
            // test Notes, use denorm off, use where off
            [
                [
                    '__sugar_url' => 'v11/Notes',
                    'view' => 'list',
                    'fields' => 'following,my_favorite',
                    'max_num' => -1,
                    'module' => 'Notes',
                ],
                2,
                ['useDenorm' => false, 'useWhere' => false],
                [['name' => self::createName('Notes', 2)]],
                [
                    ['name' => self::createName('Notes', 1)],
                    ['name' => self::createName('Notes', 0)],
                ],
            ],
            // test Notes, use denorm off, use where on
            [
                [
                    '__sugar_url' => 'v11/Notes',
                    'view' => 'list',
                    'fields' => 'following,my_favorite',
                    'max_num' => -1,
                    'module' => 'Notes',
                ],
                2,
                ['useDenorm' => false, 'useWhere' => true],
                [['name' => self::createName('Notes', 2)]],
                [
                    ['name' => self::createName('Notes', 1)],
                    ['name' => self::createName('Notes', 0)],
                ],
            ],
        ];
    }

    /**
     *  test TeamSecurity via Report, this is for testing sql statement
     *
     * @dataProvider providerTestTeamSecurityReport
     */
    public function testTeamSecurityFromReport($reportDef, $userIndex, $states, $expectedSubset, $unexpected)
    {
        global $current_user;
        $current_user = self::$userList[$userIndex];

        // set states
        self::setConfigAndDenormState($states['useDenorm'], false, $states['useWhere']);
        $results = $this->runReport($reportDef);

        // verify individual records
        $this->assertArraySubset($expectedSubset, $results);

        foreach ($unexpected as $record) {
            foreach ($record as $key => $value) {
                $this->assertFalse($this->findRecord($results, $key, $value), "unexpected $key => $value");
            }
        }
    }

    public function providerTestTeamSecurityReport()
    {
        // Customer Account List
        $customerAccountReportDef = [
            'display_columns' => [
                [
                    'name' => 'name',
                    'label' => 'Account Name',
                    'table_key' => 'self',
                ],
                [
                    'name' => 'description',
                    'label' => 'Description',
                    'table_key' => 'self',
                ],
                [
                    'name' => 'account_type',
                    'label' => 'Type',
                    'table_key' => 'self',
                ],
                [
                    'name' => 'full_name',
                    'label' => 'Assigned to',
                    'table_key' => 'Accounts:assigned_user_link',
                ],
            ],
            'summary_columns' => [],
            'filters_def' => [
                'Filter_1' => [
                    0 => [
                        'name' => 'account_type',
                        'table_key' => 'self',
                        'qualifier_name' => 'is',
                        'input_name0' => self::TEST_ACCOUNT_TYPE,
                        'column_name' => 'self:account_type',
                        'id' => 'rowid0',
                    ],
                    1 => [
                        'name' => 'user_name',
                        'table_key' => 'Accounts:created_by_link',
                        'qualifier_name' => 'is',
                        'input_name0' => [self::getAdminUser()->id],
                    ],
                    'operator' => 'AND',
                ],
            ],
            'group_defs' => [],
            'module' => 'Accounts',
            'report_name' => 'Customer Account List',
            'order_by' => [],
            'report_type' => 'tabular',
            'chart_type' => 'none',
            'full_table_list' => [
                'self' => [
                    'value' => 'Accounts',
                    'module' => 'Accounts',
                    'label' => 'Accounts',
                    'children' => [
                        'member_of' => 'member_of',
                        'team_link' => 'team_link',
                        'created_by_link' => 'created_by_link',
                        'modified_user_link' => 'modified_user_link',
                        'assigned_user_link' => 'assigned_user_link',
                    ],
                ],
                'Accounts:assigned_user_link' => [
                    'label' => 'Assigned to User',
                    'link_def' => [
                        'relationship_name' => 'accounts_assigned_user',
                        'name' => 'assigned_user_link',
                        'link_type' => 'one',
                        'label' => 'Assigned to User',
                        'table_key' => 'Accounts:assigned_user_link',
                        'bean_is_lhs' => 0,
                    ],
                    'parent' => 'self',
                    'optional' => 1,
                    'module' => 'Users',
                    'name' => 'Accounts > Users',
                ],
                'Accounts:created_by_link' => [
                    'name' => 'Accounts  >  Created User',
                    'parent' => 'self',
                    'link_def' => [
                        'name' => 'created_by_link',
                        'relationship_name' => 'accounts_created_by',
                        'bean_is_lhs' => false,
                        'link_type' => 'one',
                        'label' => 'Created User',
                        'module' => 'Users',
                        'table_key' => 'Accounts:created_by_link',
                    ],
                    'dependents' => [
                        'Filter.1_table_filter_row_3',
                    ],
                    'module' => 'Users',
                    'label' => 'Created User',
                ],
            ],
        ];

        $allOpenOpportunitiesReportDef = [
            'display_columns' => [
                [
                    'name' => 'name',
                    'label' => 'Opportunity Name',
                    'table_key' => 'self',
                ],
                [
                    'name' => 'opportunity_type',
                    'label' => 'Type',
                    'table_key' => 'self',
                ],
                [
                    'name' => 'sales_stage',
                    'label' => 'Sales Stage',
                    'table_key' => 'Opportunities:revenuelineitems',
                ],
                [
                    'name' => 'date_closed',
                    'label' => 'Expected Close Date',
                    'table_key' => 'self',
                ],
                [
                    'name' => 'amount_usdollar',
                    'label' => 'Amount',
                    'table_key' => 'self',
                ],
                [
                    'name' => 'user_name',
                    'label' => 'User Name',
                    'table_key' => 'Opportunities:assigned_user_link',
                ],
            ],
            'module' => 'Opportunities',
            'group_defs' => [],
            'summary_columns' => [],
            'order_by' => [
                [
                    'name' => 'date_closed',
                    'vname' => 'Expected Close Date',
                    'type' => 'date',
                    'audited' => '1',
                    'importable' => 'required',
                    'table_key' => 'self',
                    'sort_dir' => 'a',
                ],
            ],
            'report_name' => 'All Open Opportunities',
            'chart_type' => 'none',
            'do_round' => 1,
            'numerical_chart_column' => '',
            'numerical_chart_column_type' => '',
            'assigned_user_id' => '1',
            'report_type' => 'tabular',
            'full_table_list' => [
                'self' => [
                    'value' => 'Opportunities',
                    'module' => 'Opportunities',
                    'label' => 'Opportunities',
                ],
                'Opportunities:assigned_user_link' => [
                    'name' => 'Opportunities  >  Assigned to User',
                    'parent' => 'self',
                    'link_def' => [
                        'name' => 'assigned_user_link',
                        'relationship_name' => 'opportunities_assigned_user',
                        'bean_is_lhs' => false,
                        'link_type' => 'one',
                        'label' => 'Assigned to User',
                        'table_key' => 'Opportunities:assigned_user_link',
                    ],
                    'dependents' => [
                        'display_cols_row_6',
                        'display_cols_row_6',
                    ],
                    'module' => 'Users',
                    'label' => 'Assigned to User',
                ],
                'Opportunities:created_by_link' => [
                    'name' => 'Opportunities  >  Created User',
                    'parent' => 'self',
                    'link_def' => [
                        'name' => 'created_by_link',
                        'relationship_name' => 'opportunities_created_by',
                        'bean_is_lhs' => false,
                        'link_type' => 'one',
                        'label' => 'Created User',
                        'module' => 'Users',
                        'table_key' => 'Opportunities:created_by_link',
                    ],
                    'dependents' => [
                        'Filter.1_table_filter_row_3',
                    ],
                    'module' => 'Users',
                    'label' => 'Created User',
                ],
                'Opportunities:revenuelineitems' => [
                    'name' => 'Opportunities  >  Revenue Line Items',
                    'parent' => 'self',
                    'link_def' => [
                        'name' => 'revenuelineitems',
                        'relationship_name' => 'opportunities_revenuelineitems',
                        'bean_is_lhs' => true,
                        'link_type' => 'many',
                        'label' => 'Revenue Line Items',
                        'module' => 'RevenueLineItems',
                        'table_key' => 'Opportunities:revenuelineitems',
                    ],
                    'module' => 'RevenueLineItems',
                    'label' => 'Revenue Line Items',
                ],
            ],
            'filters_def' => [
                'Filter_1' => [
                    'operator' => 'AND',
                    0 => [
                        'name' => 'sales_stage',
                        'table_key' => 'Opportunities:revenuelineitems',
                        'qualifier_name' => 'one_of',
                        'runtime' => 1,
                        'input_name0' => [
                            'Prospecting',
                            'Qualification',
                            'Needs Analysis',
                            'Value Proposition',
                            'Id. Decision Makers',
                            'Perception Analysis',
                            'Proposal/Price Quote',
                            'Negotiation/Review',
                        ],
                    ],
                    1 => [
                        'name' => 'date_closed',
                        'table_key' => 'self',
                        'qualifier_name' => 'not_empty',
                        'runtime' => 1,
                        'input_name0' => 'undefined',
                        'input_name1' => 'on',
                    ],
                    2 => [
                        'name' => 'user_name',
                        'table_key' => 'Opportunities:created_by_link',
                        'qualifier_name' => 'is',
                        'input_name0' => [self::getAdminUser()->id],
                    ],
                ],
            ],
        ];

        return [
            // Customer Accounts List Report
            // denorm on
            [
                $customerAccountReportDef,
                0,
                ['useDenorm' => true, 'useWhere' => false],
                [
                    ['accounts_name' => self::createName('Accounts', 0)],
                    ['accounts_name' => self::createName('Accounts', 1)],
                ],
                [['accounts_name' => self::createName('Accounts', 2)]],
            ],
            // denorm off, use Where off
            [
                $customerAccountReportDef,
                0,
                ['useDenorm' => false, 'useWhere' => false],
                [
                    ['accounts_name' => self::createName('Accounts', 0)],
                    ['accounts_name' => self::createName('Accounts', 1)],
                ],
                [['accounts_name' => self::createName('Accounts', 2)]],
            ],
            // denorm off, use Where on
            [
                $customerAccountReportDef,
                0,
                ['useDenorm' => false, 'useWhere' => true],
                [
                    ['accounts_name' => self::createName('Accounts', 0)],
                    ['accounts_name' => self::createName('Accounts', 1)],
                ],
                [['accounts_name' => self::createName('Accounts', 2)]],
            ],
            // denorm on
            [
                $customerAccountReportDef,
                1,
                ['useDenorm' => true, 'useWhere' => false],
                [['accounts_name' => self::createName('Accounts', 1)]],
                [
                    ['accounts_name' => self::createName('Accounts', 0)],
                    ['accounts_name' => self::createName('Accounts', 2)],
                ],
            ],
            // denorm on useWhere off
            [
                $customerAccountReportDef,
                1,
                ['useDenorm' => true, 'useWhere' => false],
                [['accounts_name' => self::createName('Accounts', 1)]],
                [
                    ['accounts_name' => self::createName('Accounts', 0)],
                    ['accounts_name' => self::createName('Accounts', 2)],
                ],
            ],
            // denorm off, useWhere on
            [
                $customerAccountReportDef,
                1,
                ['useDenorm' => false, 'useWhere' => true],
                [['accounts_name' => self::createName('Accounts', 1)]],
                [
                    ['accounts_name' => self::createName('Accounts', 0)],
                    ['accounts_name' => self::createName('Accounts', 2)],
                ],
            ],
            // denorm on
            [
                $customerAccountReportDef,
                2,
                ['useDenorm' => true, 'useWhere' => false],
                [['accounts_name' => self::createName('Accounts', 2)]],
                [
                    ['accounts_name' => self::createName('Accounts', 0)],
                    ['accounts_name' => self::createName('Accounts', 1)],
                ],
            ],
            // denorm off use where off
            [
                $customerAccountReportDef,
                2,
                ['useDenorm' => false, 'useWhere' => false],
                [['accounts_name' => self::createName('Accounts', 2)]],
                [
                    ['accounts_name' => self::createName('Accounts', 0)],
                    ['accounts_name' => self::createName('Accounts', 1)],
                ],
            ],
            // denorm off, use where on
            [
                $customerAccountReportDef,
                2,
                ['useDenorm' => false, 'useWhere' => true],
                [['accounts_name' => self::createName('Accounts', 2)]],
                [
                    ['accounts_name' => self::createName('Accounts', 0)],
                    ['accounts_name' => self::createName('Accounts', 1)],
                ],
            ],
            // All Open opportunities
            // denorm on
            [
                $allOpenOpportunitiesReportDef,
                0,
                ['useDenorm' => true, 'useWhere' => false],
                [
                    ['opportunities_name' => self::createName('Opportunities', 0)],
                    ['opportunities_name' => self::createName('Opportunities', 1)],
                ],
                [['opportunities_name' => self::createName('Opportunities', 2)]],
            ],
            // denorm off use where off
            [
                $allOpenOpportunitiesReportDef,
                0,
                ['useDenorm' => false, 'useWhere' => false],
                [
                    ['opportunities_name' => self::createName('Opportunities', 0)],
                    ['opportunities_name' => self::createName('Opportunities', 1)],
                ],
                [['opportunities_name' => self::createName('Opportunities', 2)]],
            ],
            // denorm off use where on
            [
                $allOpenOpportunitiesReportDef,
                0,
                ['useDenorm' => false, 'useWhere' => true],
                [
                    ['opportunities_name' => self::createName('Opportunities', 0)],
                    ['opportunities_name' => self::createName('Opportunities', 1)],
                ],
                [['opportunities_name' => self::createName('Opportunities', 2)]],
            ],
            // denorm on
            [
                $allOpenOpportunitiesReportDef,
                1,
                ['useDenorm' => true, 'useWhere' => false],
                [['opportunities_name' => self::createName('Opportunities', 1)]],
                [
                    ['opportunities_name' => self::createName('Opportunities', 0)],
                    ['opportunities_name' => self::createName('Opportunities', 2)],
                ],
            ],
            // denorm off, use where off
            [
                $allOpenOpportunitiesReportDef,
                1,
                ['useDenorm' => false, 'useWhere' => false],
                [['opportunities_name' => self::createName('Opportunities', 1)]],
                [
                    ['opportunities_name' => self::createName('Opportunities', 0)],
                    ['opportunities_name' => self::createName('Opportunities', 2)],
                ],
            ],
            // denorm off, use where on
            [
                $allOpenOpportunitiesReportDef,
                1,
                ['useDenorm' => false, 'useWhere' => true],
                [['opportunities_name' => self::createName('Opportunities', 1)]],
                [
                    ['opportunities_name' => self::createName('Opportunities', 0)],
                    ['opportunities_name' => self::createName('Opportunities', 2)],
                ],
            ],
            // denoram on
            [
                $allOpenOpportunitiesReportDef,
                2,
                ['useDenorm' => true, 'useWhere' => false],
                [['opportunities_name' => self::createName('Opportunities', 2)]],
                [
                    ['opportunities_name' => self::createName('Opportunities', 0)],
                    ['opportunities_name' => self::createName('Opportunities', 1)],
                ],
            ],
            // denoram off, use where off
            [
                $allOpenOpportunitiesReportDef,
                2,
                ['useDenorm' => false, 'useWhere' => false],
                [['opportunities_name' => self::createName('Opportunities', 2)]],
                [
                    ['opportunities_name' => self::createName('Opportunities', 0)],
                    ['opportunities_name' => self::createName('Opportunities', 1)],
                ],
            ],
            // denoram off, use where on
            [
                $allOpenOpportunitiesReportDef,
                2,
                ['useDenorm' => false, 'useWhere' => true],
                [['opportunities_name' => self::createName('Opportunities', 2)]],
                [
                    ['opportunities_name' => self::createName('Opportunities', 0)],
                    ['opportunities_name' => self::createName('Opportunities', 1)],
                ],
            ],
        ];
    }

    /**
     * helper method, run report and return array of the results
     *
     * @param string $reportDef, report def
     * @return array
     */
    protected function runReport($reportDef)
    {
        $sortModuleField = [
            'Accounts' => 'primaryid',
            'Opportunities' => 'l2_id',
        ];

        $sortArray = [];
        $module = $reportDef['module'];

        // create a report
        $reporter = new Report(json_encode($reportDef));
        $reporter->run_query();

        $result = [];
        while ($row = $reporter->db->fetchByAssoc($reporter->result)) {
            $result[] = $row;
            if (isset($sortModuleField[$module])) {
                $sortArray[] = $row[$sortModuleField[$module]];
            }
        }

        if (!empty($sortArray)) {
            array_multisort($sortArray, SORT_ASC, $result);
        }
        return $result;
    }

    /**
     * find record
     * @param array $result
     * @param string $field, field name to check
     * @param string $value, expected value
     * @return bool
     */
    protected function findRecord(array $result, $field, $value)
    {
        foreach ($result as $row) {
            if ($row[$field] === $value) {
                return true;
            }
        }
        return false;
    }

    /**
     * to create a random name field value
     * @param $module
     * @param $idx
     * @return string
     */
    protected static function createName($module, $idx)
    {
        if (empty(self::$randomId)) {
            self::$randomId = mt_rand(100000000, 1000000000);
        }

        return $module . '_' . $idx . '_' . self::$randomId;
    }

    /**
     * to get/create an admin user
     * @return null|SugarBean
     */
    protected static function getAdminUser()
    {
        if (empty(self::$adminUser)) {
            self::$adminUser = SugarTestUserUtilities::createAnonymousUser(true, true);
        }
        return self::$adminUser;
    }

    /**
     * helper method, to set config flags for different options
     *
     * @param bool $useDenorm
     * @param bool $inlineUpdate
     * @param bool $useWhere
     */
    protected static function setConfigAndDenormState($useDenorm, $inlineUpdate, $useWhere)
    {
        global $sugar_config;

        $sugar_config['perfProfile'] = [
            'TeamSecurity' => [
                'default' => [
                    'use_denorm' => $useDenorm,
                    'where_condition' => $useWhere,
                ],
                'inline_update' => $inlineUpdate,
            ],
        ];

        SugarConfig::getInstance()->clearCache();
    }

    /**
     * rebuild denoram table
     */
    protected static function rebuildDenormTable()
    {
        $rebuildCmd = Container::getInstance()->get(StateAwareRebuild::class);
        $rebuildCmd();
    }

    /**
     * create user and teams
     *
     * self::$teamList[0] has user self::$userList[0]
     * self::$teamList[1] has user self::$userList[0] and self::$userList[1]
     * self::$teamList[2] has user self::$userList[2] only
     *
     */
    protected static function createUsersAndTeams()
    {
        $globalteam = new Team();
        $globalteam->retrieve('1');
        for ($i = 0; $i < self::RECORD_COUNT; $i++) {
            self::$userList[$i] = SugarTestUserUtilities::createAnonymousUser();
            self::$teamList[$i] = SugarTestTeamUtilities::createAnonymousTeam();
            // add user to team
            $globalteam->add_user_to_team(self::$userList[$i]->id);
            self::$teamList[$i]->add_user_to_team(self::$userList[$i]->id);
            // add user 0 to team 1
            if ($i === 1) {
                self::$teamList[$i]->add_user_to_team(self::$userList[$i-1]->id);
            }
        }
    }

    /**
     * to create test beans
     */
    protected static function createTestBeans()
    {
        foreach (self::$testModules as $module) {
            for ($i = 0; $i < self::RECORD_COUNT; $i++) {
                if ($module === 'Accounts') {
                    $bean = SugarTestAccountUtilities::createAccount(
                        '',
                        [
                            'name' => self::createName($module, $i),
                            'account_type' => self::TEST_ACCOUNT_TYPE,
                            'team_id' => self::$teamList[$i]->id,
                        ]
                    );
                } elseif ($module === 'Contacts') {
                    $bean = SugarTestContactUtilities::createContact(
                        '',
                        [
                            'first_name' => self::createName($module, $i),
                            'last_name' => self::createName($module, $i),
                            'team_id' => self::$teamList[$i]->id,
                        ]
                    );
                    // add account relationship
                    $bean->load_relationship('accounts');
                    $bean->accounts->add(self::$testBeans['Accounts'][$i]);
                    $bean->save();
                } elseif ($module === 'Notes') {
                    $bean = SugarTestNoteUtilities::createNote(
                        '',
                        [
                            'name' => self::createName($module, $i),
                            'parent_type' => 'Accounts',
                            'parent_id' => self::$testBeans['Accounts'][$i]->id,
                            'team_id' => self::$teamList[$i]->id,
                        ]
                    );
                } elseif ($module === 'Opportunities') {
                    $bean = SugarTestOpportunityUtilities::createOpportunity('', self::$testBeans['Accounts'][$i]);
                    $bean->team_id = self::$teamList[$i]->id;
                    $bean->team_set_id = self::$testBeans['Accounts'][$i]->team_set_id;
                    // make sure it is open
                    $bean->name = self::createName($module, $i);
                    $bean->amount = 2000.00;
                    $bean->sales_stage = self::TEST_SALES_STAGE;
                    $bean->date_closed = TimeDate::getInstance()->getNow()->modify("+1 days")->asDbDate();
                    $bean->assigned_user_id = self::$userList[$i]->id;

                    $bean->save();
                    // create RLI
                    $rli = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
                    $rli->team_id = self::$teamList[$i]->id;
                    $rli->team_set_id = self::$testBeans['Accounts'][$i]->team_set_id;
                    $rli->opportunity_id = $bean->id;
                    $rli->date_closed = $bean->date_closed;
                    //$bean->load_relationship('revenuelineitems');
                    $bean->revenuelineitems->add($rli);
                    $rli->save();
                    $bean->save();
                }
                self::$testBeans[$module][] = $bean;
            }
        }
    }

    /**
     * to delete created related records from db
     */
    protected static function deleteRelateTestBeans()
    {
        $beanIds = array();
        $tableName = null;
        foreach (self::$testBeans['Contacts'] as $bean) {
            $beanIds[] = $bean->id;
            $tableName = $bean->table_name;
        }

        if (!empty($tableName)) {
            $beanIdsStr = "('" . implode("','", $beanIds) . "')";
            $deleteQuery = "DELETE FROM accounts_contacts WHERE contact_id IN {$beanIdsStr}";
            $GLOBALS['db']->query($deleteQuery);
        }
    }
}
