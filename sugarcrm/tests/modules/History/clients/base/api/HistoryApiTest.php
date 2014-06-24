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
require_once('include/api/RestService.php');
require_once("modules/History/clients/base/api/HistoryApi.php");

/**
 * @group ApiTests
 */
class HistoryApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->filterApi = new HistoryApiMock();
        $this->serviceMock = SugarTestRestUtilities::getRestServiceMock();
    }

    public function tearDown()
    {
        unset($this->filterApi);
        unset($this->serviceMock);
    }

    public function testFilterSetup()
    {
        $return = $this->filterApi->filterList(
            $this->serviceMock,
            array(
                'module_list' => 'Notes,Tasks,Meetings',
                'filter' => array(),
            ),
            'list'
        );
        $expected = array('Notes' => array(), 'Tasks' => array(), 'Meetings' => array());
        $this->assertEquals($expected, $return);
    }
}

class HistoryApiMock extends HistoryApi
{
    protected function filterModuleList(ServiceBase $api, array $args, array $moduleList, $acl = 'list')
    {
        return array(
            'Notes' => array(),
            'Tasks' => array(),
            'Meetings' => array(),
        );
    }
}
