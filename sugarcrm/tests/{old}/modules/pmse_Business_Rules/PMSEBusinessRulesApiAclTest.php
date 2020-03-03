<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */


use Sugarcrm\Sugarcrm\ProcessManager;
use PHPUnit\Framework\TestCase;

/**
 * Unit test class to cover ACL testing for SugarBPM Apis
 */
class PMSEBusinessRulesApiAclTest extends TestCase
{
    /**
     * @var PMSEBusinessRules
     */
    private $PMSEBusinessRules;

    /**
     * @var RestService
     */
    private $api;

    protected function setUp() : void
    {
        SugarTestHelper::setUp('current_user');

        $this->PMSEBusinessRules = ProcessManager\Factory::getPMSEObject('PMSEBusinessRules');
        $this->api = new RestService();
        $this->api->getRequest()->setRoute(array('acl' => array()));
    }

    protected function tearDown() : void
    {
        SugarTestACLUtilities::tearDown();
        SugarTestHelper::tearDown();
    }

    public function testBusinessRuleDownload()
    {
        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->PMSEBusinessRules->businessRuleDownload(
            $this->api,
            array('module' => 'pmse_Business_Rules')
        );
    }

    public function testBusinessRulesImport()
    {
        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->PMSEBusinessRules->businessRulesImport($this->api, array('module' => 'pmse_Business_Rules'));
    }

    /**
     * Check if valid user is allowed to pass ACL access
     */
    public function testBusinessRuleDownloadValidUser()
    {
        $GLOBALS['current_user']->is_admin = 1;

        $pmseBusinessRuleExporter = $this->getMockBuilder('PMSEBusinessRuleExporter')
                                         ->setMethods(array('exportProject'))
                                         ->getMock();
        $pmseBusinessRuleExporter
            ->expects($this->any())
            ->method('exportProject')
            ->will($this->returnValue('testPassed'));

        $pmseBusinessRulesApi = $this->getMockBuilder('PMSEBusinessRules')
                                     ->setMethods(array('getPMSEBusinessRuleExporter'))
                                     ->getMock();
        $pmseBusinessRulesApi
            ->expects($this->any())
            ->method('getPMSEBusinessRuleExporter')
            ->will($this->returnValue($pmseBusinessRuleExporter));

        $ret = $pmseBusinessRulesApi->businessRuleDownload(
            $this->api,
            array('module' => 'pmse_Business_Rules', 'record' => 'dummy')
        );

        $this->assertEquals($ret, "testPassed", "ACL access test failed for businessRuleDownload");
    }
}
