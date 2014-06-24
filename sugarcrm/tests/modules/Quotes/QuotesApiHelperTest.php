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
