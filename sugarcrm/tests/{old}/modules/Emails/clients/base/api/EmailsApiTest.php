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

require_once 'modules/Emails/clients/base/api/EmailsApi.php';

/**
 * @group api
 * @group email
 */
class EmailsApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    public $emailsApi;
    public $serviceMock;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
    }

    public function setUp()
    {
        parent::setUp();
        $this->emailsApi = new EmailsApi();
        $this->serviceMock = SugarTestRestUtilities::getRestServiceMock();
    }

    public static function tearDownAfterClass()
    {
        // delete any emails created
        $GLOBALS['db']->query("DELETE FROM emails WHERE assigned_user_id = '{$GLOBALS['current_user']->id}'");
        SugarTestEmailUtilities::removeAllCreatedEmails();
        SugarTestHelper::tearDown();
        parent::tearDownAfterClass();
    }

    public function testCreateEmail_SaveAsDraft_EmailCreated()
    {
        $createArgs = array(
            'module' => 'Emails',
            'name' => 'Test Email ' . time(),
            'state' => Email::EMAIL_STATE_DRAFT,
            'assigned_user_id' => $GLOBALS['current_user']->id
        );
        $result = $this->emailsApi->createRecord($this->serviceMock, $createArgs);

        //--- Verify Created Email Record Returned
        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertEquals($createArgs['name'], $result['name']);
        $this->assertEquals($createArgs['state'], $result['state']);
        $this->assertEquals($createArgs['assigned_user_id'], $result['assigned_user_id']);

        //--- Verify Created Email Record Created in Database
        $email = BeanFactory::newBean('Emails');
        $email->retrieve($result['id']);
        $this->assertAttributeNotEmpty('id', $email);
        $this->assertEquals($createArgs['name'], $email->name);
        $this->assertEquals($createArgs['state'], $email->state);
        $this->assertEquals($createArgs['assigned_user_id'], $email->assigned_user_id);
    }

    public function testUpdateDraftEmail_SaveAsDraft_EmailUpdated()
    {
        $createArgs = array(
            'module' => 'Emails',
            'name' => 'Test Email ' . time(),
            'state' => Email::EMAIL_STATE_DRAFT,
            'assigned_user_id' => $GLOBALS['current_user']->id
        );
        $createResult = $this->emailsApi->createRecord($this->serviceMock, $createArgs);
        //--- Verify Created Email Record Returned
        $this->assertNotEmpty($createResult);
        $this->assertArrayHasKey('id', $createResult);

        $updateArgs = array(
            'module' => 'Emails',
            'record' => $createResult['id'],
            'name' => 'Test Email ' . time() + 10,
            'state' => Email::EMAIL_STATE_DRAFT,
        );
        $updateResult = $this->emailsApi->updateRecord($this->serviceMock, $updateArgs);

        //--- Verify Updated Email Record Returned
        $this->assertNotEmpty($updateResult);
        $this->assertArrayHasKey('id', $updateResult);
        $this->assertEquals($createResult['id'], $updateResult['id']);
        $this->assertEquals($updateArgs['name'], $updateResult['name']);
        $this->assertEquals($updateArgs['state'], $updateResult['state']);
        $this->assertEquals($createArgs['assigned_user_id'], $updateResult['assigned_user_id']);

        //--- Verify Email Record Created in Database
        $email = BeanFactory::newBean('Emails');
        $email->retrieve($updateResult['id']);
        $this->assertAttributeNotEmpty('id', $email);
        $this->assertEquals($updateResult['name'], $email->name);
        $this->assertEquals($updateResult['state'], $email->state);
        $this->assertEquals($updateResult['assigned_user_id'], $email->assigned_user_id);
    }

    public function testEmailCreate_StateTransition_ExceptionThrown()
    {
        $createArgs = array(
            'module' => 'Emails',
            'state' => 'SomeBogusToState',
        );
        $this->setExpectedException('SugarApiExceptionInvalidParameter');
        $result = $this->emailsApi->createRecord($this->serviceMock, $createArgs);
        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('id', $result);
    }

    /**
     * @dataProvider emailCreate_StateTransitionProvider
     */
    public function testEmailCreate_StateTransition($toState)
    {
        $createArgs = array(
            'module' => 'Emails',
            'state' => $toState,
        );
        $result = $this->emailsApi->createRecord($this->serviceMock, $createArgs);
        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('id', $result);
    }

    public function emailCreate_StateTransitionProvider()
    {
        return array(
            array(Email::EMAIL_STATE_DRAFT),
            array(Email::EMAIL_STATE_ARCHIVED),
            array(Email::EMAIL_STATE_READY),
            array(Email::EMAIL_STATE_SCHEDULED),
        );
    }

    /**
     * @dataProvider emailUpdate_StateTransitionProvider_ExceptionThrown
     */
    public function testEmailUpdate_StateTransition_ExceptionThrown($fromState, $toState)
    {
        $createArgs = array(
            'module' => 'Emails',
            'state' => $fromState,
        );
        $result = $this->emailsApi->createRecord($this->serviceMock, $createArgs);
        $this->assertTrue(!empty($result['id']), 'Create Email Failed');
        $args['record'] = $result['id'];

        $updateArgs = array(
            'module' => 'Emails',
            'record' => $result['id'],
            'state' => $toState,
        );

        $this->setExpectedException('SugarApiExceptionInvalidParameter');

        $result = $this->emailsApi->updateRecord($this->serviceMock, $updateArgs);
        $this->assertTrue(!empty($result['id']), 'Update Email Failed');

    }

    public function emailUpdate_StateTransitionProvider_ExceptionThrown()
    {
        return array(
            array(Email::EMAIL_STATE_ARCHIVED, Email::EMAIL_STATE_ARCHIVED),
            array(Email::EMAIL_STATE_ARCHIVED, Email::EMAIL_STATE_READY),
            array(Email::EMAIL_STATE_DRAFT, Email::EMAIL_STATE_ARCHIVED),
            array(Email::EMAIL_STATE_SCHEDULED, Email::EMAIL_STATE_DRAFT),
        );
    }

    /**
     * @dataProvider emailUpdate_StateTransitionProvider_ExceptionThrown
     */
    public function testEmailUpdate_StateTransition($fromState, $toState)
    {
        $createArgs = array(
            'module' => 'Emails',
            'state' => $fromState,
        );
        $result = $this->emailsApi->createRecord($this->serviceMock, $createArgs);
        $this->assertTrue(!empty($result['id']), 'Create Email Failed');
        $args['record'] = $result['id'];

        $updateArgs = array(
            'module' => 'Emails',
            'record' => $result['id'],
            'state' => $toState,
        );

        $this->setExpectedException('SugarApiExceptionInvalidParameter');

        $result = $this->emailsApi->updateRecord($this->serviceMock, $updateArgs);
        $this->assertTrue(!empty($result['id']), 'Update Email Failed');

    }

    public function emailUpdate_StateTransitionProvider()
    {
        return array(
            array(Email::EMAIL_STATE_DRAFT, Email::EMAIL_STATE_DRAFT, false),
            array(Email::EMAIL_STATE_DRAFT, Email::EMAIL_STATE_SCHEDULED, false),
            array(Email::EMAIL_STATE_DRAFT, Email::EMAIL_STATE_READY, false),
            array(Email::EMAIL_STATE_SCHEDULED, Email::EMAIL_STATE_SCHEDULED, false),
            array(Email::EMAIL_STATE_SCHEDULED, Email::EMAIL_STATE_ARCHIVED, false),
        );
    }
}
