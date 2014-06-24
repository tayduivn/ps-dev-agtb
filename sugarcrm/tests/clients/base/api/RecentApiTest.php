<?php
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
