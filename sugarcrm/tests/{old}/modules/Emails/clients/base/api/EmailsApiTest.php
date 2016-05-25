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
require_once 'tests/{old}/modules/OutboundEmailConfiguration/OutboundEmailConfigurationTestHelper.php';

/**
 * @coversDefaultClass EmailsApi
 * @group api
 * @group email
 */
class EmailsApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected static $systemConfiguration;
    protected static $currentUserConfiguration;
    protected $service;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');

        OutboundEmailConfigurationTestHelper::backupExistingConfigurations();
        static::$systemConfiguration = OutboundEmailConfigurationTestHelper::createSystemOutboundEmailConfiguration();
        static::$currentUserConfiguration = OutboundEmailConfigurationTestHelper::
        createSystemOverrideOutboundEmailConfiguration($GLOBALS['current_user']->id);
    }

    protected function setUp()
    {
        parent::setUp();
        $this->service = SugarTestRestUtilities::getRestServiceMock();
    }

    public static function tearDownAfterClass()
    {
        SugarTestEmailUtilities::removeAllCreatedEmails();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        OutboundEmailConfigurationTestHelper::restoreExistingConfigurations();
        parent::tearDownAfterClass();
    }

    public function cannotMakeInvalidStateChangeProvider()
    {
        return array(
            array(
                Email::EMAIL_STATE_DRAFT,
                Email::EMAIL_STATE_ARCHIVED,
            ),
            array(
                Email::EMAIL_STATE_SCHEDULED,
                Email::EMAIL_STATE_DRAFT,
            ),
            array(
                Email::EMAIL_STATE_ARCHIVED,
                Email::EMAIL_STATE_DRAFT,
            ),
            array(
                Email::EMAIL_STATE_ARCHIVED,
                Email::EMAIL_STATE_READY,
            ),
            array(
                Email::EMAIL_STATE_ARCHIVED,
                Email::EMAIL_STATE_SCHEDULED,
            ),
        );
    }

    /**
     * @covers ::createRecord
     * @covers ::isValidStateTransition
     * @expectedException SugarApiExceptionInvalidParameter
     */
    public function testCreateRecord_StatusIsInvalid()
    {
        $args = array(
            'module' => 'Emails',
            'name' => 'Sugar Email' . mt_rand(),
            'state' => 'SomeBogusToState',
        );
        $api = new EmailsApi();
        $api->createRecord($this->service, $args);
    }

    /**
     * @expectedException SugarApiExceptionRequestMethodFailure
     */
    public function testCreateRecord_NoEmailIsCreatedOnFailureToSend()
    {
        $before = $GLOBALS['db']->fetchOne('SELECT COUNT(*) as num FROM emails WHERE deleted=0');

        $api = $this->getMockBuilder('EmailsApi')
            ->setMethods(array('sendEmail'))
            ->getMock();
        $api->method('sendEmail')->willThrowException(new SugarApiExceptionRequestMethodFailure());

        $args = array(
            'module' => 'Emails',
            'state' => Email::EMAIL_STATE_READY,
            'assigned_user_id' => $GLOBALS['current_user']->id,
        );
        $api->createRecord($this->service, $args);

        $after = $GLOBALS['db']->fetchOne('SELECT COUNT(*) as num FROM emails WHERE deleted=0');
        $this->assertSame($before['num'], $after['num'], 'A new email should not have been created');
    }

    /**
     * @dataProvider cannotMakeInvalidStateChangeProvider
     * @expectedException SugarApiExceptionInvalidParameter
     * @covers ::updateRecord
     * @covers ::isValidStateTransition
     * @param string $fromState
     * @param string $toState
     */
    public function testUpdateRecord_CannotMakeInvalidStateChange($fromState, $toState)
    {
        $email = SugarTestEmailUtilities::createEmail(null, array('state' => $fromState));

        $args = array(
            'module' => 'Emails',
            'record' => $email->id,
            'state' => $toState,
            'assigned_user_id' => $GLOBALS['current_user']->id,
        );
        $api = new EmailsApi();
        $api->updateRecord($this->service, $args);
    }

    /**
     * Existing Notes records cannot be used as attachments.
     *
     * @covers ::linkRelatedRecords
     */
    public function testLinkRelatedRecords()
    {
        $relateRecordApi = $this->getMockBuilder('EmailsRelateRecordApi')
            ->disableOriginalConstructor()
            ->setMethods(array('createRelatedLinks'))
            ->getMock();
        $relateRecordApi->expects($this->never())
            ->method('createRelatedLinks');

        $api = $this->getMockBuilder('EmailsApi')
            ->disableOriginalConstructor()
            ->setMethods(array('getRelateRecordApi'))
            ->getMock();
        $api->method('getRelateRecordApi')
            ->willReturn($relateRecordApi);

        $email = BeanFactory::newBean('Emails');
        $email->id = create_guid();
        $args = array(
            'attachments' => array(
                'id' => create_guid(),
                'email_id' => $email->id,
                'email_type' => $email->module_name,
                'team_id' => $email->team_id,
                'team_set_id' => $email->team_set_id,
            ),
        );

        SugarTestReflection::callProtectedMethod($api, 'linkRelatedRecords', array($this->service, $email, $args));
    }

    /**
     * The sender cannot be unlinked. The sender can only be replaced.
     *
     * @covers ::linkRelatedRecords
     */
    public function testUnlinkRelatedRecords()
    {
        $relateRecordApi = $this->getMockBuilder('EmailsRelateRecordApi')
            ->disableOriginalConstructor()
            ->setMethods(array('deleteRelatedLink'))
            ->getMock();
        $relateRecordApi->expects($this->never())
            ->method('deleteRelatedLink');

        $api = $this->getMockBuilder('EmailsApi')
            ->disableOriginalConstructor()
            ->setMethods(array('getRelateRecordApi'))
            ->getMock();
        $api->method('getRelateRecordApi')
            ->willReturn($relateRecordApi);

        $email = BeanFactory::newBean('Emails');
        $email->id = create_guid();
        $args = array(
            'accounts_from' => array(
                create_guid(),
            ),
            'contacts_from' => array(
                create_guid(),
            ),
            'email_addresses_from' => array(
                create_guid(),
            ),
            'leads_from' => array(
                create_guid(),
            ),
            'prospects_from' => array(
                create_guid(),
            ),
            'users_from' => array(
                create_guid(),
            ),
        );

        SugarTestReflection::callProtectedMethod($api, 'unlinkRelatedRecords', array($this->service, $email, $args));
    }

    public function testSendEmail_UsesSpecifiedConfiguration()
    {
        $config = OutboundEmailConfigurationPeer::getMailConfigurationFromId(
            $GLOBALS['current_user'],
            static::$currentUserConfiguration->id
        );

        $email = $this->getMockBuilder('Email')
            ->disableOriginalConstructor()
            ->setMethods(array('sendEmail'))
            ->getMock();
        $email->expects($this->once())
            ->method('sendEmail')
            ->with($this->equalTo($config));
        $email->outbound_email_id = static::$currentUserConfiguration->id;

        $api = new EmailsApi();
        SugarTestReflection::callProtectedMethod($api, 'sendEmail', array($email));
    }

    public function testSendEmail_UsesSystemConfiguration()
    {
        $config = OutboundEmailConfigurationPeer::getSystemMailConfiguration($GLOBALS['current_user']);

        $email = $this->getMockBuilder('Email')
            ->disableOriginalConstructor()
            ->setMethods(array('sendEmail'))
            ->getMock();
        $email->expects($this->once())
            ->method('sendEmail')
            ->with($this->equalTo($config));

        $api = new EmailsApi();
        SugarTestReflection::callProtectedMethod($api, 'sendEmail', array($email));
    }

    /**
     * @expectedException SugarApiExceptionError
     */
    public function testSendEmail_NoConfiguration()
    {
        $email = $this->getMockBuilder('Email')
            ->disableOriginalConstructor()
            ->setMethods(array('sendEmail'))
            ->getMock();
        $email->expects($this->never())
            ->method('sendEmail');
        $email->outbound_email_id = create_guid();

        $api = new EmailsApi();
        SugarTestReflection::callProtectedMethod($api, 'sendEmail', array($email));
    }

    /**
     * @expectedException SugarApiExceptionError
     */
    public function testSendEmail_UnknownError()
    {
        $config = OutboundEmailConfigurationPeer::getSystemMailConfiguration($GLOBALS['current_user']);

        $email = $this->getMockBuilder('Email')
            ->disableOriginalConstructor()
            ->setMethods(array('sendEmail'))
            ->getMock();
        $email->expects($this->once())
            ->method('sendEmail')
            ->willThrowException(new Exception('something happened'));

        $api = new EmailsApi();
        SugarTestReflection::callProtectedMethod($api, 'sendEmail', array($email));
    }
}
