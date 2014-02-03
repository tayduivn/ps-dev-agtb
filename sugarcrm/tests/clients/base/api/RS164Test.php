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

require_once 'clients/base/api/ThemeApi.php';

/**
 *  RS164: Prepare Theme Api.
 */
class RS164Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var ThemeApi
     */
    protected $api;

    /**
     * @var RestService
     */
    protected static $rest;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, false));
        self::$rest = SugarTestRestUtilities::getRestServiceMock();
    }

    public static function tearDownAfterClass()
    {
        SugarTestFilterUtilities::removeAllCreatedFilters();
        SugarTestHelper::tearDown();
        parent::tearDownAfterClass();
    }

    protected function setUp()
    {
        parent::setUp();
        $this->api = new ThemeApi();
    }

    public function testGetCSSURLs()
    {
        $result = $this->api->getCSSURLs(
            self::$rest,
            array()
        );
        $this->assertArrayHasKey('url', $result);
    }

    public function testPreviewCSS()
    {
        $this->expectOutputRegex('/padding|margin/');
        $this->api->previewCSS(
            self::$rest,
            array()
        );
    }

    public function testGetCustomThemeVars()
    {
        $result = $this->api->getCustomThemeVars(
            self::$rest,
            array()
        );
        $this->assertNotEmpty($result);
    }

    /**
     * @expectedException SugarApiExceptionNotAuthorized
     */
    public function testUpdateCustomThemeException()
    {
        $this->api->updateCustomTheme(
            self::$rest,
            array()
        );
    }

    public function testUpdateCustomTheme()
    {
        $admin = SugarTestUserUtilities::createAnonymousUser(true, true);
        $rest = SugarTestRestUtilities::getRestServiceMock($admin);
        $result = $this->api->updateCustomTheme(
            $rest,
            array('Border' => '#AAAAAA')
        );
        $this->assertNotEmpty($result);
    }
}
