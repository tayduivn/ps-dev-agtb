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

require_once 'clients/base/api/ExportApi.php';
require_once 'clients/base/api/RecordListApi.php';

/**
 * RS192: Prepare Export Api.
 */
class RS192Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var SugarApi
     */
    protected $api;

    /**
     * @var SugarApi
     */
    protected $recordList;

    /**
     * @var bool;
     */
    protected static $encode;

    /**
     * @var string
     */
    protected $listId;

    /**
     * @var array
     */
    protected $records;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        self::$encode = DBManagerFactory::getInstance()->getEncode();
        DBManagerFactory::getInstance()->setEncode(false);
        SugarTestHelper::setUp('current_user', array(true, false));
    }

    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
        DBManagerFactory::getInstance()->setEncode(self::$encode);
        parent::tearDownAfterClass();
    }

    protected function setUp()
    {
        parent::setUp();

        $this->api = new ExportApi();
        $this->recordList = new RecordListApi();
        $this->records = array();
        $account = SugarTestAccountUtilities::createAccount();
        array_push($this->records, $account->id);
        $account = SugarTestAccountUtilities::createAccount();
        array_push($this->records, $account->id);
        SugarTestAccountUtilities::createAccount();
    }

    protected function tearDown()
    {
        $this->recordList->recordListDelete(
            SugarTestRestUtilities::getRestServiceMock(),
            array('module' => 'Accounts', 'record_list_id' => $this->listId)
        );
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        parent::tearDown();
    }

    public function testExactExport()
    {
        $result = $this->recordList->recordListCreate(
            SugarTestRestUtilities::getRestServiceMock(),
            array('module' => 'Accounts', 'records' => $this->records)
        );
        $this->listId = $result['id'];
        $strCount = $this->getExportStringCount($this->listId);
        $this->assertEquals(3, $strCount);
    }

    public function testAllExport()
    {
        $result = $this->recordList->recordListCreate(
            SugarTestRestUtilities::getRestServiceMock(),
            array('module' => 'Accounts', 'records' => array())
        );
        $this->listId = $result['id'];
        $strCount = $this->getExportStringCount($this->listId);
        $this->assertGreaterThan(3, $strCount);
    }

    protected function getExportStringCount($listId)
    {
        $result = $this->api->export(
            SugarTestRestUtilities::getRestServiceMock(),
            array('module' => 'Accounts', 'record_list_id' => $listId)
        );
        $cnt = 0;
        foreach (explode("\r\n", $result) as $str) {
            if (!empty($str)) {
                $cnt ++;
            }
        }
        return $cnt;
    }
}
