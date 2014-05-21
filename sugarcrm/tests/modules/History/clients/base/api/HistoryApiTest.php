<?php
/*********************************************************************************
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
