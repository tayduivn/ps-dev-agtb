<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2013 SugarCRM Inc. All rights reserved.
 */

/**
 * Class DropDownUpdateSQLTest
 */
class DropDownUpdateSQLTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
    }

    public function tearDown()
    {
        SugarBean::clearLoadedDef('Lead');
        SugarTestLeadUtilities::removeAllCreatedLeads();
        SugarTestHelper::tearDown();
    }

    /**
     * Test if DBManager updateSQL properly processes dropdown fields
     * based on the vardefs
     *
     * @dataProvider dropDownUpdateSQLDataProvider
     */
    public function testDropDownUpdateSQL($fieldDefs, $value, $expected)
    {
        $dbManagerFactory = new DBManagerFactory();
        $dbManager = $dbManagerFactory->getInstance();

        $bean = SugarTestLeadUtilities::createLead();
        $bean->field_defs = $fieldDefs;
        $bean->$fieldDefs['status']['name'] = $value;

        $sql = $dbManager->updateSQL($bean);

        $this->assertContains($expected, $sql);
    }

    public static function dropDownUpdateSQLDataProvider()
    {
        return array(
            array(
                array(
                    'id' => array(
                        'name' => 'id',
                    ),
                    'status' => array(
                        'name' => 'status',
                        'type' => 'enum',
                        'options' => 'lead_status_dom',
                        'default' => 'In Process',
                        'required' => true,
                    ),
                ),
                '',
                "status='In Process'",
            ),
            array(
                array(
                    'id' => array(
                        'name' => 'id',
                    ),
                    'status' => array(
                        'name' => 'status',
                        'type' => 'enum',
                        'options' => 'lead_status_dom',
                        'default' => 'In Process',
                        'required' => true,
                    ),
                ),
                'Value',
                "status='Value'",
            ),
            array(
                array(
                    'id' => array(
                        'name' => 'id',
                    ),
                    'status' => array(
                        'name' => 'status',
                        'type' => 'enum',
                        'options' => 'lead_status_dom',
                        'default' => 'In Process',
                        'required' => false,
                    ),
                ),
                '',
                "status=NULL",
            ),
            array(
                array(
                    'id' => array(
                        'name' => 'id',
                    ),
                    'status' => array(
                        'name' => 'status',
                        'type' => 'enum',
                        'options' => 'lead_status_dom',
                        'default' => 'In Process',
                        'required' => false,
                    ),
                ),
                'Value',
                "status='Value'",
            ),
        );
    }
}
