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

/**
 *  This test file checks whether Worklog module specified in RPT-784 is
 *  setup correctly
 */
class WorklogTest extends TestCase
{
    /**
     * Checks whether the fields are there in the bean->field_defs
     * @dataProvider CheckWorklogBeanProvider
     * @param SugarBean $bean The $bean to test against
     * @param string $property The property we would like to check
     */
    public function testCheckWorklogBean(SugarBean $bean, string $property)
    {
        $this->assertArrayHasKey($property, $bean->field_defs);
    }

    public function CheckWorklogBeanProvider()
    {
        $bean = BeanFactory::getBean("Worklog");

        return array(
            array($bean, 'id'),
            array($bean, 'date_entered'),
            array($bean, 'entry'),
        );
    }

    /**
     * Check if the database table setup for worklog module is setup
     * correctly
     */
    public function testCheckWorklogModuleDBTableSetup()
    {
        $this->assertTrue(DBManagerFactory::getInstance()->tableExists('worklog')); // verify that the table exists
    }

    /**
     * Checks whether the $required field is in $reality
     * @param string $required The required field name
     * @param array $reality The actual list of field names in worklog DB
     * @dataProvider CheckWorklogDBFieldSetupProvider
     */
    public function testCheckWorklogDBFieldSetup(string $required, array $reality)
    {
        $this->assertArrayHasKey($required, $reality);
    }

    public function CheckWorklogDBFieldSetupProvider()
    {
        $db = DBManagerFactory::getInstance();
        $columns = $db->get_columns('worklog');

        return array(
            array('id', $columns),
            array('date_entered', $columns),
            array('entry', $columns),
        );
    }
}
