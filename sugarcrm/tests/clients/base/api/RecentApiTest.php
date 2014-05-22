<?php
/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/

require_once 'clients/base/api/RecentApi.php';

/**
 * @group ApiTests
 */
class RecentApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('current_user');
    }

    protected function tearDown()
    {
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    public function testFilterModules()
    {
        // Employees module is currently handled in a special way, so test it explicitly
        $modules = array('Accounts', 'Employees', 'NonExistingModule');
        $api = new RecentApi();
        $filtered = SugarTestReflection::callProtectedMethod($api, 'filterModules', array($modules));

        $this->assertContains('Accounts', $filtered);
        $this->assertContains('Employees', $filtered);
        $this->assertNotContains('NonExistingModule', $filtered);
    }
}
