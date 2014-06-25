<?php
//FILE SUGARCRM flav=ent ONLY
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

require_once('include/database/IBMDB2Manager.php');

class IBMDB2ManagerTest extends Sugar_PHPUnit_Framework_TestCase
{
    static public function setUpBeforeClass()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
    }

    static public function tearDownAfterClass()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        unset($GLOBALS['app_strings']);
    }

    public function setUp()
    {
        $this->_db = new IBMDB2Manager();
    }

    /**
     * @ticket PAT-389
     */
    public function testAddColumnSQL()
    {
        $fieldDef = array(
            'name' => 'testColumn',
            'required' => true,
            'type' => 'bool',
            'isnull' => false
        );
        $this->assertNotContains('NOT NULL', $this->_db->addColumnSQL('testTable', $fieldDef), 'New column should be nullable if required but no default');
        $fieldDef['default'] = 0;
        $this->assertContains('NOT NULL', $this->_db->addColumnSQL('testTable', $fieldDef), 'New column should be not null if required with default');
    }

    public function providerConvert()
    {
        $returnArray = array(
            array(
                array('1.23', 'round', array(6)),
                "round(1.23, 6)"
            )
        );
        return $returnArray;
    }

    /**
     * @dataProvider providerConvert
     */
    public function testConvert(array $parameters, $result)
    {
        $this->assertEquals($result, call_user_func_array(array($this->_db, "convert"), $parameters));
    }
}
