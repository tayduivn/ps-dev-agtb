<?php
//FILE SUGARCRM flav=pro ONLY
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
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

require_once 'modules/Accounts/Account.php';
require_once 'modules/WebLogicHooks/WebLogicHook.php';

class WebLogicHookTest extends Sugar_PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('mod_strings', array('WebLogicHooks'));
    }

    public static function tearDownAfterClass()
    {
        SugarTestWebLogicHookUtilities::removeAllCreatedWebLogicHook();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        parent::tearDownAfterClass();
    }

    /**
     * @ticket SP-942
     */
    public function testWebLogicHookFire()
    {
        $hook = SugarTestWebLogicHookUtilities::createWebLogicHook(false, array(
            'name' => ('Text Hook ' . time()),
            'module_name' => 'Accounts',
            'request_method' => 'POST',
            'url' => 'http://www.example.com',
            'trigger_event' => 'after_save',
        ));

        $account = SugarTestAccountUtilities::createAccount();
        $dispatchOptions = $hook::$dispatchOptions;

        $this->assertEquals('Account', get_class($dispatchOptions['seed']));
        $this->assertEquals($hook->id, $dispatchOptions['id']);
        $this->assertEquals($hook->trigger_event, $dispatchOptions['event']);
        $this->assertNotEmpty($dispatchOptions['seed']);
        $this->assertNotEmpty($dispatchOptions['event']);
        $this->assertNotEmpty($dispatchOptions['arguments']);
        $this->assertNotEmpty($dispatchOptions['id']);
    }
}
