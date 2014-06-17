<?php
//FILE SUGARCRM flav=ent ONLY
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
}
