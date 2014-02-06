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
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */

require_once 'modules/Sync/SyncHelper.php';

/**
 * RS-91: Prepare Sync Module.
 */
class RS91Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, false));
    }

    protected function tearDown()
    {
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    public function testSyncHelper()
    {
        $time = TimeDate::getInstance()->nowDb();
        $result = clean_for_sync('Accounts');
        $this->assertEmpty($result);
        $result = clean_relationships_for_sync('Accounts', 'Contacts');
        $this->assertTrue($result);
        $result = get_altered('Accounts', $time, $time);
        $this->assertEmpty($result['entry_list']);
        $result = get_altered_relationships('Accounts', 'Contacts', $time, $time);
        $this->assertEmpty($result['entry_list']);
    }
}
