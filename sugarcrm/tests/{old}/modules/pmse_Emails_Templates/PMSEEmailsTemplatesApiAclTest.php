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
class PMSEEmailsTemplatesApiAclTest extends TestCase
{
    /**
     * @var PMSEEmailsTemplates
     */
    private $PMSEEmailsTemplates;

    /**
     * @var RestService
     */
    private $api;

    protected function setUp() : void
    {
        SugarTestHelper::setUp('current_user');

        $this->PMSEEmailsTemplates = ProcessManager\Factory::getPMSEObject('PMSEEmailsTemplates');
        $this->api = new RestService();
        $this->api->getRequest()->setRoute(array('acl' => array()));
    }

    protected function tearDown() : void
    {
        SugarTestACLUtilities::tearDown();
        SugarTestHelper::tearDown();
    }

    public function testEmailTemplateDownload()
    {
        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->PMSEEmailsTemplates->emailTemplateDownload(
            $this->api,
            array('module' => 'pmse_Emails_Templates')
        );
    }

    public function testFindVariables()
    {
        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->PMSEEmailsTemplates->findVariables(
            $this->api,
            array('module' => 'pmse_Emails_Templates')
        );
    }

    public function testRetrieveRelatedBeans()
    {
        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->PMSEEmailsTemplates->retrieveRelatedBeans(
            $this->api,
            array('module' => 'pmse_Emails_Templates')
        );
    }

    public function testEmailTemplatesImport()
    {
        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->PMSEEmailsTemplates->emailTemplatesImport(
            $this->api,
            array('module' => 'pmse_Emails_Templates')
        );
    }

    /**
     * Check if valid user is allowed to pass ACL access
     */
    public function testFindVariablesValidUser()
    {
        $GLOBALS['current_user']->is_admin = 1;
        $ret = $this->PMSEEmailsTemplates->findVariables(
            $this->api,
            array('module' => 'pmse_Emails_Templates')
        );
        $this->assertTrue(is_array($ret), "ACL access test failed for findVariables");
    }
}
