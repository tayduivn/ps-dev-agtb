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
    protected $currency;

    /** @var Opportunity */
    protected $opportunity1;

    /** @var Opportunity */
    protected $opportunity2;

    /** @var Opportunity */
    protected $opportunity3;

    /** @var Report */
    protected $report;

    /** @var DeployedRelationships */
    protected $relationships;

    /** @var OneToOneRelationship */
    protected $relationship;

    /** @var DynamicField  */
    protected $df;

    /** @var TemplateCurrency */
    protected $field;

    /** @var string */
    protected $module_name = 'Opportunities';

    /** @var string */
    protected $bean_name = 'Opportunity';

    /** @var string */
    protected $custom_field_name = 'currency_54193_c';

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    public function setUp()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user', array(true, 1));
        SugarTestHelper::setUp('mod_strings', array($this->module_name));
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');

        $this->createRelationship();

        $this->createCurrency();

        $this->createCustomField();

        // create opportunities
        $this->opportunity1 = $this->createOpportunity('O1', -99, 10, 20);
        $this->opportunity2 = $this->createOpportunity('O2', $this->currency->id, 15, 22.5);
        $this->opportunity3 = $this->createOpportunity('O3', $this->currency->id, 75, 75);

        /** @var Link2 $link */
        $relation_name = $this->relationship->getName();

        // create relationship O1 -> O2
        $this->opportunity1->load_relationship($relation_name);
        $link = $this->opportunity1->{$relation_name};
        $link->add(array($this->opportunity2));

        // create relationship O2 -> O3
        $this->opportunity2->load_relationship($relation_name);
        $link = $this->opportunity2->{$relation_name};
        $link->add(array($this->opportunity3));

        $this->createReport();
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    public function tearDown()
    {
        $this->df->deleteField($this->field);

        VardefManager::clearVardef();
        VardefManager::refreshVardefs($this->module_name, $this->bean_name);

        $this->relationships->delete($this->relationship->getName());
        $this->relationships->save();
        $this->relationships->build();

        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        $this->currency->mark_deleted($this->currency->id);

        SugarTestHelper::tearDown();
    }

    /**
     * Check values in "total" row
     */
    public function testSumOpportunityAmount()
    {
        $this->markTestIncomplete('Needs to be fixed by FRM team.');
        $this->report->run_total_query();
        $total = $this->report->get_summary_total_row();

        $this->assertInternalType('array', $total);
        $this->assertArrayHasKey('cells', $total);
        $this->assertInternalType('array', $total['cells']);
        $total = $total['cells'];

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
     * Creates new opportunity
     *
     * @param string $name
     * @param int    $currency_id
     * @param float  $amount
     * @param float  $custom_value
     *
     * @return Opportunity
     */
    protected function createOpportunity($name, $currency_id, $amount, $custom_value)
    {
        $opportunity = SugarTestOpportunityUtilities::createOpportunity();
        $opportunity->name = $name;
        $opportunity->currency_id = $currency_id;
        $opportunity->amount = $amount;
        $opportunity->{$this->custom_field_name} = $custom_value;
        $opportunity->save();
        return $opportunity;
    }

    /**
     * Create new relationship between module and itself
     */
    protected function createRelationship()
    {
        $this->relationships = new DeployedRelationships($this->module_name);
        $this->relationship = RelationshipFactory::newRelationship(
            array(
                'lhs_module'        => $this->module_name,
                'relationship_type' => 'one-to-many',
                'rhs_module'        => $this->module_name
            )
        );
        $this->relationships->add($this->relationship);
        $this->relationships->save();
        $this->relationships->build();
        SugarTestHelper::setUp(
            'relation',
            array(
                $this->module_name,
                $this->module_name,
            )
        );
    }

    /**
     * Creates custom field of "currency" type
     */
    protected function createCustomField()
    {
        //create a new field for opportunities
        $field = $this->field = get_widget('currency');
        $field->id = $this->custom_field_name;
        $field->name = $this->custom_field_name;
        $field->label = 'LBL_' . strtoupper($this->custom_field_name);

        //add field to metadata
        $this->df = new DynamicField($this->bean_name);
        $this->df->setup(new Opportunity());
        $this->df->addFieldObject($field);
        $this->df->buildCache($this->module_name);

        VardefManager::clearVardef();
        VardefManager::refreshVardefs($this->module_name, $this->bean_name);
    }

    /**
     * Creates custom currency
     */
    protected function createCurrency()
    {
        //create new Currency
        $this->currency = new Currency();
        $this->currency->iso4217 = 'EUR';
        $this->currency->conversion_rate = 0.75;
        $this->currency->save();
    }

    /**
     * Creates report for test
     */
    protected function createReport()
    {
        $relationship_name = $this->relationship->getName();
        $table_key = 'Opportunities:' . $relationship_name;

        $report_def = array(
            'display_columns' => array(
                array(
                    'name'      => 'name',
                    'table_key' => 'self',
                ),
            ),
            'module' => $this->module_name,
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
                    'name'           => $this->custom_field_name,
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
                    'name'           => $this->custom_field_name,
                    'field_type'     => 'currency',
                    'group_function' => 'sum',
                    'table_key'      => $table_key,
                ),
            ),
            'report_name' => 'Bug54193Test',
            'report_type' => 'summary',
            'full_table_list' => array(
                'self' => array(
                    'value'  => $this->module_name,
                    'module' => $this->module_name,
                ),
                $table_key => array(
                    'value'    => $this->module_name,
                    'module'   => $this->module_name,
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
                        'name'           => $this->custom_field_name,
                        'table_key'      => 'self',
                        'qualifier_name' => 'not_empty',
                    ),
                ),
            ),
        );

        $this->report = new Report(json_encode($report_def));
    }
}
