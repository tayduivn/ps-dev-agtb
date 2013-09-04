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
require_once 'include/generic/SugarWidgets/SugarWidgetReportField.php';

/**
 * @ticket 54193
 */
class Bug54193Test extends Sugar_PHPUnit_Framework_TestCase
{
    /** @var Currency */
    protected static $currency;

    /** @var DeployedRelationships */
    protected static $relationships;

    /** @var OneToOneRelationship */
    protected static $relationship;

    /** @var DynamicField  */
    protected static $df;

    /** @var TemplateCurrency */
    protected static $field;

    /** @var string */
    protected static $module_name = 'Opportunities';

    /** @var string */
    protected static $bean_name = 'Opportunity';

    /** @var string */
    protected static $custom_field_name = 'currency_54193_c';

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user', array(true, 1));
        SugarTestHelper::setUp('mod_strings', array(self::$module_name));
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');

        self::createRelationship();

        self::createCurrency();

        self::createCustomField();

        // create opportunities
        $opportunity1 = self::createOpportunity('O1', -99, 10, 20);
        $opportunity2 = self::createOpportunity('O2', self::$currency->id, 15, 22.5);
        $opportunity3 = self::createOpportunity('O3', self::$currency->id, 75, 75);

        /** @var Link2 $link */
        $relation_name = self::$relationship->getName();

        // create relationship O1 -> O2
        $opportunity1->load_relationship($relation_name);
        $link = $opportunity1->{$relation_name};
        $link->add(array($opportunity2));

        // create relationship O2 -> O3
        $opportunity2->load_relationship($relation_name);
        $link = $opportunity2->{$relation_name};
        $link->add(array($opportunity3));
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    public static function tearDownAfterClass()
    {
        self::$df->deleteField(self::$field);

        VardefManager::clearVardef();
        VardefManager::refreshVardefs(self::$module_name, self::$bean_name);

        self::$relationships->delete(self::$relationship->getName());
        self::$relationships->save();
        self::$relationships->build();

        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        self::$currency->mark_deleted(self::$currency->id);

        SugarTestHelper::tearDown();
    }

    /**
     * Tests system currency
     *
     * @group reports
     */
    public function testSystemCurrency()
    {
        // updated tests but leaving Incomplete message intact
        $this->markTestIncomplete('Needs to be fixed by FRM team.');
        global $current_user;
        $current_user->setPreference('currency', -99);

        $report = $this->getReport();
        $total = $this->getReportTotal($report);

        $GLOBALS['log']->fatal(var_export($total, true));

        // Amount (US Dollar): $130 = $10 + 15€ + 75€
        $this->assertEquals('$130.00', $total[0]);

        // Custom field: $150 = $20 + 22.5€ + 75€
        $this->assertEquals('$150.00', $total[1]);

        // Amount (US Dollar) in related table: $120 = 15€ + 75€ + 0
        $this->assertEquals('$120.00', $total[2]);

        // Custom field in related table: $130 = 22.5€ + 75€ + 0
        $this->assertEquals('$130.00', $total[3]);
    }

    /**
     * Tests custom currency
     *
     * @group reports
     */
    public function testCustomCurrency()
    {
        // updated tests but leaving Incomplete message intact
        $this->markTestIncomplete('Needs to be fixed by FRM team.');
        global $current_user;
        $current_user->setPreference('currency', self::$currency->id);

        $report = $this->getReport();
        $total = $this->getReportTotal($report);

        // Amount (US Dollar): €97.50 = $10 + 15€ + 75€
        $this->assertEquals('€97.50', $total[0]);

        // Custom field: €112.50 = $20 + 22.5€ + 75€
        $this->assertEquals('€112.50', $total[1]);

        // Amount (US Dollar) in related table: €90.00 = 15€ + 75€ + 0
        $this->assertEquals('€90.00', $total[2]);

        // Custom field in related table: €97.50 = 22.5€ + 75€ + 0
        $this->assertEquals('€97.50', $total[3]);
    }

    /**
     * Creates new opportunity
     *
     * @param string $name
     * @param int    $currency_id
     * @param float  $amount
     * @param float  $custom_value
     *
     * @return Opportunity
     */
    protected static function createOpportunity($name, $currency_id, $amount, $custom_value)
    {
        $opportunity = SugarTestOpportunityUtilities::createOpportunity();
        $opportunity->name = $name;
        $opportunity->currency_id = $currency_id;
        $opportunity->amount = $amount;
        $opportunity->{self::$custom_field_name} = $custom_value;
        $opportunity->save();
        return $opportunity;
    }

    /**
     * Create new relationship between module and itself
     */
    protected static function createRelationship()
    {
        self::$relationships = new DeployedRelationships(self::$module_name);
        self::$relationship = RelationshipFactory::newRelationship(
            array(
                'lhs_module'        => self::$module_name,
                'relationship_type' => 'one-to-many',
                'rhs_module'        => self::$module_name
            )
        );
        self::$relationships->add(self::$relationship);
        self::$relationships->save();
        self::$relationships->build();
        SugarTestHelper::setUp(
            'relation',
            array(
                self::$module_name,
                self::$module_name,
            )
        );
    }

    /**
     * Creates custom field of "currency" type
     */
    protected static function createCustomField()
    {
        //create a new field for opportunities
        $field = self::$field = get_widget('currency');
        $field->id = self::$custom_field_name;
        $field->name = self::$custom_field_name;
        $field->label = 'LBL_' . strtoupper(self::$custom_field_name);

        //add field to metadata
        self::$df = new DynamicField(self::$bean_name);
        self::$df->setup(new Opportunity());
        self::$df->addFieldObject($field);
        self::$df->buildCache(self::$module_name);

        VardefManager::clearVardef();
        VardefManager::refreshVardefs(self::$module_name, self::$bean_name);
    }

    /**
     * Creates custom currency
     */
    protected static function createCurrency()
    {
        //create new Currency
        self::$currency = new Currency();
        self::$currency->iso4217 = 'EUR';
        self::$currency->conversion_rate = 0.75;
        self::$currency->save();
    }

    /**
     * Creates report for test
     *
     * @return Report
     */
    protected function getReport()
    {
        $relationship_name = self::$relationship->getName();
        $table_key = 'Opportunities:' . $relationship_name;

        $report_def = array(
            'display_columns' => array(
                array(
                    'name'      => 'name',
                    'table_key' => 'self',
                ),
            ),
            'module' => self::$module_name,
            'group_defs' => array(
                array(
                    'name'            => 'date_closed',
                    'column_function' => 'day',
                    'qualifier'       => 'day',
                    'table_key'       => 'self',
                    'type'            => 'date',
                ),
            ),
            'summary_columns' => array(
                array(
                    'name'           => 'amount',
                    'field_type'     => 'currency',
                    'group_function' => 'sum',
                    'table_key'      => 'self',
                ),
                array(
                    'name'           => self::$custom_field_name,
                    'field_type'     => 'currency',
                    'group_function' => 'sum',
                    'table_key'      => 'self',
                ),
                array(
                    'name'           => 'amount',
                    'field_type'     => 'currency',
                    'group_function' => 'sum',
                    'table_key'      => $table_key,
                ),
                array(
                    'name'           => self::$custom_field_name,
                    'field_type'     => 'currency',
                    'group_function' => 'sum',
                    'table_key'      => $table_key,
                ),
            ),
            'report_name' => 'Bug54193Test',
            'report_type' => 'summary',
            'full_table_list' => array(
                'self' => array(
                    'value'  => self::$module_name,
                    'module' => self::$module_name,
                ),
                $table_key => array(
                    'value'    => self::$module_name,
                    'module'   => self::$module_name,
                    'parent'   => 'self',
                    'optional' => true,
                    'link_def' => array(
                        'name'              => $relationship_name,
                        'table_key'         => $table_key,
                        "relationship_name" => $relationship_name,
                    ),
                ),
            ),
            'filters_def' => array(
                'Filter_1' => array(
                    'operator' => 'AND',
                    array(
                        'name'           => self::$custom_field_name,
                        'table_key'      => 'self',
                        'qualifier_name' => 'not_empty',
                    ),
                ),
            ),
        );

        return new Report(json_encode($report_def));
    }

    /**
     * Returns total values of the report and makes basic assertions
     *
     * @param Report $report
     * @return array
     */
    protected function getReportTotal(Report $report)
    {
        $report->run_total_query();
        $total = $report->get_summary_total_row();

        $this->assertInternalType('array', $total);
        $this->assertArrayHasKey('cells', $total);
        $this->assertInternalType('array', $total['cells']);
        return $total['cells'];
    }
}
