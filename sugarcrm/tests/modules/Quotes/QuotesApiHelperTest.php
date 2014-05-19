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
 * Copyright (C) 2004-2014 SugarCRM Inc.  All rights reserved.
 */

require_once('modules/Quotes/QuotesApiHelper.php');
class QuotesApiHelperTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var QuotesApiHelper
     */
    protected $helper;

    public function setUp()
    {
        parent::setUp();

        $mock_service = new QuotesServiceMock();
        $mock_service->user = SugarTestHelper::setUp('current_user');

        $this->helper = $this->getMock('QuotesApiHelper', array('execute'), array($mock_service));
    }

    public function tearDown()
    {
        unset($this->helper);
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    public function testFormatForApiCallsFillInAdditionalDetailsOnBean()
    {
        $bean = $this->getMockBuilder('Quote')
            ->setMethods(array('fill_in_additional_detail_fields'))
            ->getMock();

        $bean->expects($this->atLeastOnce())
            ->method('fill_in_additional_detail_fields');

        /* @var $bean Quote */
        $this->helper->formatForApi($bean);
    }
}

class QuotesServiceMock extends ServiceBase
{
    public function execute() {}

    protected function handleException(Exception $exception) {}
}
