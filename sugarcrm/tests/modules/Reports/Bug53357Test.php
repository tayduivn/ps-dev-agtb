<?php
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
require_once 'modules/Reports/templates/templates_list_view.php';

/**
 * @ticket 53357
 */
class Bug53357Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $opportunity_id;
    protected $amount = 123456;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    public function setUp()
    {
        global $beanList, $beanFiles;
        require('include/modules.php');

        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser(true, 1);

        // create an opportunity which will be used to create a report
        $opportunity = new Opportunity();
        $opportunity->assigned_user_id = $GLOBALS['current_user']->id;
        $opportunity->amount_usdollar = $this->amount;

        $this->opportunity_id = $opportunity->save();

        /* amount_usdollar gets smashed during save due to forecasting logic
         * we just want it to have a value, so update database directly */
        $opportunity->db->query("update opportunities set amount_usdollar=".$this->amount." where id='".$this->opportunity_id."'");

    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    public function tearDown()
    {
        $opportunity = new Opportunity();
        $opportunity->mark_deleted($this->opportunity_id);

        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user'], $GLOBALS['beanFiles'],
        $GLOBALS['beanList'], $GLOBALS['app_list_strings']);
    }

    /**
     * Ensure report Grand Totals are correctly calculated and displayed
     */
    public function testGrandTotalsAreCalculated()
    {
        // create matrix report with the following spec:
        // Module: Opportunities
        // Display columns: Opportunity amount
        // Group By columns: Sales Stage and Assigned User Name
        // Filter: the only opportunity which is created in setUp() method
        $report_def = array(
            'module' => 'Opportunities',
            'group_defs' => array(
                array(
                    'name'        => 'user_name',
                    'label'       => 'User Name',
                    'table_key'   => 'Opportunities:assigned_user_link',
                ),
                array(
                    'name'        => 'sales_stage',
                    'label'       => 'Sales Stage',
                    'table_key'   => 'self',
                ),
            ),
            'display_columns' => array(),
            'summary_columns' => array(
                array(
                    'name'      => 'user_name',
                    'label'     => 'User Name',
                    'table_key' => 'Opportunities:assigned_user_link',
                ),
                array(
                    'name'      => 'sales_stage',
                    'label'     => 'Sales Stage',
                    'table_key' => 'self',
                ),
                array(
                    'name'      => 'amount_usdollar',
                    'label'     => 'AVG: Amount',
                    'group_function' => 'avg',
                    'table_key' => 'self',
                ),
            ),
            'report_type' => 'summary',
            'layout_options' => '2x2',
            'full_table_list' => array(
                'self' => array(
                    'value' => 'Opportunities',
                    'module' => 'Opportunities',
                ),
                'Opportunities:assigned_user_link' => array(
                    'name' => 'Opportunities  >  Assigned to User',
                    'parent' => 'self',
                    'link_def' => array(
                        'name' => 'assigned_user_link',
                        'relationship_name' => 'opportunities_assigned_user',
                        'link_type' => 'one',
                        'module' => 'Users',
                        'table_key' => 'Opportunities:assigned_user_link',
                    ),
                    'module' => 'Users',
                ),
            ),
            'filters_def' => array (
                'Filter_1' =>
                array (
                    'operator' => 'AND',
                    0 =>
                    array (
                        'name' => 'id',
                        'table_key' => 'self',
                        'qualifier_name' => 'is',
                        'input_name0' => $this->opportunity_id,
                    ),
                ),
            )
        );

        $json = getJSONobj();
        $report = new Report($json->encode($report_def));

        $args = array();

        ob_start();
        template_summary_list_view($report, $args);
        $output = ob_get_contents();
        ob_end_clean();


        global $locale;

        // prepare expected substring (the formatted value of opportunity amount)
        $substring = currency_format_number($this->amount, array(
                'currency_id'     => $locale->getPrecedentPreference('currency'),
                'convert'         => true,
                'currency_symbol' => $locale->getPrecedentPreference('default_currency_symbol'),
            ));

        // Opportunity amount must appear 4 times in report output:
        // 1. The amount of opportunity itself
        // 2. Grand Total by Sales Stage
        // 3. Grand Total by User
        // 4. The very Grand Total
        $this->assertEquals(4, substr_count($output, $substring));
    }
}