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
require_once 'include/api/SugarApi.php';
require_once 'clients/base/api/MassUpdateApi.php';

/**
 * @group ApiTests
 */
class MassUpdateApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    public $massDeleteApiMock;
    public $serviceMock;
    

    public function setUp()
    {
        SugarTestHelper::setUp("current_user");
        $this->massDeleteApiMock = new MassDeleteApiMock();
        $this->serviceMock = SugarTestRestUtilities::getRestServiceMock();
    }

    public function tearDown()
    {
        unset($this->massDelateApiMock);
        unset($this->serviceMock);
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    public function testMassDeleteFilter()
    {
        // test 'date_entered' filter
        $args = array('massupdate_params'=> array('entire'=>1),'module'=>'Accounts');
        $args = $this->massDeleteApiMock->massDelete($this->serviceMock, $args);
        $this->assertLessThanOrEqual(TimeDate::getInstance()->getNow(true), $args['massupdate_params']['filter'][0]['date_entered']['$lt']);
    }
}

class MassDeleteApiMock extends MassUpdateApi
{
    public function massUpdate($api, $args)
    {
        return $args;
    }
}
