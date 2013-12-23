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
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */
require_once 'clients/base/api/LocaleApi.php';

/**
 * @group ApiTests
 */
class LocaleApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var LocaleApi
     */
    protected $api;

    /**
     * @var RestService
     */
    protected $serviceMock;

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp("beanList");
        SugarTestHelper::setUp("beanFiles");
        SugarTestHelper::setUp("current_user");
    }

    public function setUp()
    {
        $this->api = new LocaleApi();
        $this->serviceMock = SugarTestRestUtilities::getRestServiceMock();
    }

    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
    }

    public function testRetrieveLocaleOptions()
    {
        $result = $this->api->localeOptions($this->serviceMock, array());

        $this->assertNotEmpty($result);
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('_hash', $result);

        $fields = array('timepref', 'datepref', 'default_locale_name_format', 'timezone');

        foreach($fields as $field) {
            $this->assertArrayHasKey($field, $result);
            $this->assertInternalType('array', $result[$field]);
        }
    }
}