<?php
//FILE SUGARCRM flav=pro ONLY
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once 'modules/Reports/Report.php';

/**
 * Bug #52757
 * DoNotMoveFrom645: Reports join issues?
 *
 * @author mgusev@sugarcrm.com
 * @ticked 52757
 */
class Bug52757Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var Report
     */
    public $report = null;

    /**
     * Filling default report object
     */
    function setUp()
    {
        $beanList = array();
        $beanFiles = array();
        require('include/modules.php');
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;

        $this->report = new Report();
        $this->report->report_def['full_table_list'] = array(
            'self' => array(
                'module' => 'Accounts'
            ),
            'Accounts:calls' => array(
                'module' => 'Calls',
                'parent' => 'self'
            ),
            'Accounts:calls:assigned_user_link' => array(
                'module' => 'Users',
                'parent' => 'Accounts:calls'
            )
        );
    }

    /**
     * Removing default report object
     */
    function tearDown()
    {
        unset($this->report);
        unset($GLOBALS['beanFiles']);
        unset($GLOBALS['beanList']);
    }

    /**
     * Test presents all information and asserts that all tables are present in full_table_list
     */
    function testReportValidationAllDataArePresent()
    {
        $this->report->report_def['display_columns'] = array(
            array(
                'name' => 'id',
                'table_key' => 'self'
            ),
            array(
                'name' => 'id',
                'table_key' => 'Accounts:calls'
            ),
            array(
                'name' => 'id',
                'table_key' => 'Accounts:calls:assigned_user_link'
            )
        );
        $this->report->report_def['group_defs'] = $this->report->report_def['display_columns'];
        $this->report->report_def['summary_columns'] = $this->report->report_def['display_columns'];

        $this->report->report_def['filters_def'] = array(
            'Filter_1' => array(
                array(
                    'name' => 'id',
                    'table_key' => 'self'
                ),
                array(
                    'name' => 'id',
                    'table_key' => 'Accounts:calls'
                ),
                array(
                    'name' => 'id',
                    'table_key' => 'Accounts:calls:assigned_user_link'
                ),
                'operator' => 'AND'
            )
        );

        $this->report->fixReportDefs();
        $actual = array_keys($this->report->report_def['full_table_list']);
        $expected = array(
            'self',
            'Accounts:calls',
            'Accounts:calls:assigned_user_link'
        );

        $this->assertEquals($expected, $actual, 'List of tables is incorrect');
    }

    /**
     * Test presents account and call only and asserts that Account:calls:assigned_user_link is removed from full_table_list
     */
    function testReportValidationAssignedUserIsNotPresent()
    {
        $this->report->report_def['display_columns'] = array(
            array(
                'name' => 'id',
                'table_key' => 'self'
            ),
            array(
                'name' => 'id',
                'table_key' => 'Accounts:calls'
            )
        );
        $this->report->report_def['group_defs'] = $this->report->report_def['display_columns'];
        $this->report->report_def['summary_columns'] = $this->report->report_def['display_columns'];

        $this->report->report_def['filters_def'] = array(
            'Filter_1' => array(
                array(
                    'name' => 'id',
                    'table_key' => 'self'
                ),
                array(
                    'name' => 'id',
                    'table_key' => 'Accounts:calls'
                ),
                'operator' => 'AND'
            )
        );

        $this->report->fixReportDefs();
        $actual = array_keys($this->report->report_def['full_table_list']);
        $expected = array(
            'self',
            'Accounts:calls'
        );

        $this->assertEquals($expected, $actual, 'List of tables is incorrect');
    }

    /**
     * Test presents account and assigned user only and asserts that all tables are present in full_table_list
     * because assigned user depends on call
     */
    function testReportValidationCallIsNotPresent()
    {
        $this->report->report_def['display_columns'] = array(
            array(
                'name' => 'id',
                'table_key' => 'self'
            ),
            array(
                'name' => 'id',
                'table_key' => 'Accounts:calls:assigned_user_link'
            )
        );
        $this->report->report_def['group_defs'] = $this->report->report_def['display_columns'];
        $this->report->report_def['summary_columns'] = $this->report->report_def['display_columns'];

        $this->report->report_def['filters_def'] = array(
            'Filter_1' => array(
                array(
                    'name' => 'id',
                    'table_key' => 'self'
                ),
                array(
                    'name' => 'id',
                    'table_key' => 'Accounts:calls:assigned_user_link'
                ),
                'operator' => 'AND'
            )
        );

        $this->report->fixReportDefs();
        $actual = array_keys($this->report->report_def['full_table_list']);
        $expected = array(
            'self',
            'Accounts:calls',
            'Accounts:calls:assigned_user_link'
        );

        $this->assertEquals($expected, $actual, 'List of tables is incorrect');
    }

    /**
     * Test presents assigned user only and asserts that all tables are present in full_table_list
     * because assigned user depends on call and call depends on account
     */
    function testReportValidationOnlyAssignedUserIsPresent()
    {
        $this->report->report_def['display_columns'] = array(
            array(
                'name' => 'id',
                'table_key' => 'Accounts:calls:assigned_user_link'
            )
        );
        $this->report->report_def['group_defs'] = $this->report->report_def['display_columns'];
        $this->report->report_def['summary_columns'] = $this->report->report_def['display_columns'];

        $this->report->report_def['filters_def'] = array(
            'Filter_1' => array(
                array(
                    'name' => 'id',
                    'table_key' => 'Accounts:calls:assigned_user_link'
                ),
                'operator' => 'AND'
            )
        );

        $this->report->fixReportDefs();
        $actual = array_keys($this->report->report_def['full_table_list']);
        $expected = array(
            'self',
            'Accounts:calls',
            'Accounts:calls:assigned_user_link'
        );

        $this->assertEquals($expected, $actual, 'List of tables is incorrect');
    }

    /**
     * Test presents account only and asserts that only self table is present in full_table_list
     */
    function testReportValidationOnlyAccountIsPresent()
    {
        $this->report->report_def['display_columns'] = array(
            array(
                'name' => 'id',
                'table_key' => 'self'
            )
        );
        $this->report->report_def['group_defs'] = $this->report->report_def['display_columns'];
        $this->report->report_def['summary_columns'] = $this->report->report_def['display_columns'];

        $this->report->report_def['filters_def'] = array(
            'Filter_1' => array(
                array(
                    'name' => 'id',
                    'table_key' => 'self'
                ),
                'operator' => 'AND'
            )
        );

        $this->report->fixReportDefs();
        $actual = array_keys($this->report->report_def['full_table_list']);
        $expected = array(
            'self'
        );

        $this->assertEquals($expected, $actual, 'List of tables is incorrect');
    }
}
