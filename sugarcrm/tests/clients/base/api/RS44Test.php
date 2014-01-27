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

require_once 'clients/base/api/RegisterLeadApi.php';

/**
 *  RS-44: Prepare RegisterLead Api.
 */
class RS44Test extends Sugar_PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, false));
    }

    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
        parent::tearDownAfterClass();
    }

    public function testCreateLead()
    {
        $api = new RegisterLeadApi();
        $rest = SugarTestRestUtilities::getRestServiceMock();

        $result = $api->createLeadRecord($rest, array('last_name' => 'RS44Test'));
        $this->assertNotEmpty($result);

        $bean = BeanFactory::getBean('Leads', $result);
        $this->assertEquals('RS44Test', $bean->last_name);
    }
}
