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
                Email::EMAIL_STATE_ARCHIVED,
                Email::EMAIL_STATE_DRAFT,
            ),
            array(
                Email::EMAIL_STATE_ARCHIVED,
                Email::EMAIL_STATE_READY,
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

    public function testCreateRecord_NoEmailIsCreatedOnFailureToSend()
    {
        $before = $GLOBALS['db']->fetchOne('SELECT COUNT(*) as num FROM emails WHERE deleted=0');

        $api = $this->getMockBuilder('EmailsApi')
            ->setMethods(array('sendEmail'))
            ->getMock();
        $api->method('sendEmail')->willThrowException(new SugarApiExceptionRequestMethodFailure());

        $args = array(
            'module' => 'Emails',
            'name' => 'Sugar Email' . mt_rand(),
            'state' => Email::EMAIL_STATE_READY,
            'assigned_user_id' => $GLOBALS['current_user']->id,
        );

        $caught = false;

        try {
            $api->createRecord($this->service, $args);
        } catch (SugarApiExceptionRequestMethodFailure $e) {
            $caught = true;
        }

        $this->assertTrue($caught, 'SugarApiExceptionRequestMethodFailure was expected');

        $after = $GLOBALS['db']->fetchOne('SELECT COUNT(*) as num FROM emails WHERE deleted=0');
        $this->assertSame($before['num'], $after['num'], 'A new email should not have been created');

        // In reality, an email was created, but it was immediately deleted. SugarTestEmailUtilities has no knowledge of
        // it, so add the ID in order to allow teardown to clean up the database.
        $id = $GLOBALS['db']->fetchOne("SELECT id FROM emails WHERE name='{$args['name']}' AND deleted=1");
        SugarTestEmailUtilities::setCreatedEmail($id);
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

    /**
     * @covers ::sendEmail
     */
    public function testSendEmail_UsesSpecifiedConfiguration()
    {
        $configId = static::$currentUserConfiguration->id;

        $email = $this->createPartialMock('Email', ['sendEmail']);
        $email->expects($this->once())
            ->method('sendEmail')
            ->with($this->callback(function ($config) use ($configId) {
                return $config->getConfigId() === $configId;
            }));
        $email->outbound_email_id = static::$currentUserConfiguration->id;

        $api = new EmailsApi();
        SugarTestReflection::callProtectedMethod($api, 'sendEmail', array($email));
    }

    /**
     * @covers ::sendEmail
     */
    public function testSendEmail_UsesSystemConfiguration()
    {
        $config = OutboundEmailConfigurationPeer::getSystemMailConfiguration($GLOBALS['current_user']);
        $configId = $config->getConfigId();

        $email = $this->createPartialMock('Email', ['sendEmail']);
        $email->expects($this->once())
            ->method('sendEmail')
            ->with($this->callback(function ($config) use ($configId) {
                return $config->getConfigId() === $configId;
            }));

        $api = new EmailsApi();
        SugarTestReflection::callProtectedMethod($api, 'sendEmail', array($email));
    }

    /**
     * @covers ::sendEmail
     */
    public function testSendEmail_CurrentUserHasNoConfigurations_ThrowsException()
    {
        // Make sure the current user doesn't have any configurations. The existing current user does.
        $saveUser = $GLOBALS['current_user'];
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();

        $email = $this->createPartialMock('Email', ['sendEmail']);
        $email->expects($this->never())->method('sendEmail');

        $caught = false;

        try {
            $api = new EmailsApi();
            SugarTestReflection::callProtectedMethod($api, 'sendEmail', [$email]);
        } catch (SugarApiException $e) {
            $caught = true;
        }

        // Restore the current user to the previous user before asserting to guarantee that the next test gets the user
        // it expects.
        $GLOBALS['current_user'] = $saveUser;

        $this->assertTrue($caught);
    }

    /**
     * @covers ::sendEmail
     * @expectedException SugarApiException
     */
    public function testSendEmail_SpecifiedConfigurationCouldNotBeFound()
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
     * @covers ::sendEmail
     * @expectedException SugarApiException
     */
    public function testSendEmail_ConfigurationIsNotComplete()
    {
        $oe = $this->createPartialMock('OutboundEmail', ['isConfigured']);
        $oe->method('isConfigured')->willReturn(false);
        BeanFactory::registerBean($oe);

        $email = $this->createPartialMock('Email', ['sendEmail']);
        $email->expects($this->never())->method('sendEmail');
        $email->outbound_email_id = $oe->id;

        $api = new EmailsApi();
        SugarTestReflection::callProtectedMethod($api, 'sendEmail', [$email]);
    }

    /**
     * @covers ::sendEmail
     * @expectedException SugarApiExceptionError
     */
    public function testSendEmail_UnknownError()
    {
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

    public function smtpServerErrorProvider()
    {
        return array(
            array(
                MailerException::FailedToSend,
                'smtp_server_error',
            ),
            array(
                MailerException::FailedToConnectToRemoteServer,
                'smtp_server_error',
            ),
            array(
                MailerException::InvalidConfiguration,
                'smtp_server_error',
            ),
            array(
                MailerException::InvalidHeader,
                'smtp_payload_error',
            ),
            array(
                MailerException::InvalidEmailAddress,
                'smtp_payload_error',
            ),
            array(
                MailerException::InvalidAttachment,
                'smtp_payload_error',
            ),
            array(
                MailerException::FailedToTransferHeaders,
                'smtp_payload_error',
            ),
            array(
                MailerException::ExecutableAttachment,
                'smtp_payload_error',
            ),
        );
    }

    /**
     * @covers ::sendEmail
     * @dataProvider smtpServerErrorProvider
     */
    public function testSendEmail_SmtpError($errorCode, $expectedErrorLabel)
    {
        $email = $this->getMockBuilder('Email')
            ->disableOriginalConstructor()
            ->setMethods(array('sendEmail'))
            ->getMock();
        $email->expects($this->once())
            ->method('sendEmail')
            ->willThrowException(new MailerException('something happened', $errorCode));

        $api = new EmailsApi();

        try {
            SugarTestReflection::callProtectedMethod($api, 'sendEmail', array($email));
        } catch (SugarApiException $e) {
            $this->assertEquals(
                451,
                $e->httpCode,
                'Should map this MailerException to a SugarApiException with code 451'
            );
            $this->assertEquals(
                $expectedErrorLabel,
                $e->errorLabel,
                "Should classify this error as a {$expectedErrorLabel}"
            );
        }
    }

    /**
     * @covers ::validateEmailAddresses
     */
    public function testValidateEmailAddresses_OneIsValidAndOneIsInvalid()
    {
        $args = array(
            'foo@bar.com',
            'foo',
        );
        $api = new EmailsApi();
        $actual = $api->validateEmailAddresses($this->service, $args);

        $this->assertTrue($actual[$args[0]], "Should have set the value for key '{$args[0]}' to true.");
        $this->assertFalse($actual[$args[1]], "Should have set the value for key '{$args[1]}' to false.");
    }

    public function testFindRecipients_NextOffsetIsLessThanTotalRecords_ReturnsRealNextOffset()
    {
        $args = array(
            'offset' => 0,
            'max_num' => 5,
        );

        $mailApi = $this->createPartialMock('EmailsApi', ['getEmailRecipientsService']);
        $emailRecipientsServiceMock = $this->createPartialMock('EmailRecipientsService', ['findCount', 'find']);
        $emailRecipientsServiceMock->expects($this->any())
            ->method('find')
            ->will($this->returnValue(array_pad(array(10), 10, 0)));

        $mailApi->expects($this->any())
            ->method('getEmailRecipientsService')
            ->will($this->returnValue($emailRecipientsServiceMock));

        $response = $mailApi->findRecipients($this->service, $args);
        $expected = 5;
        $actual = $response['next_offset'];
        $this->assertEquals($expected, $actual, "The next offset should be {$expected}.");
    }

    public function testFindRecipients_NextOffsetIsGreaterThanTotalRecords_ReturnsNextOffsetAsNegativeOne()
    {
        $args = array(
            'offset' => 5,
            'max_num' => 5,
        );

        $mailApi = $this->createPartialMock('EmailsApi', ['getEmailRecipientsService']);
        $emailRecipientsServiceMock = $this->createPartialMock('EmailRecipientsService', ['findCount', 'find']);
        $emailRecipientsServiceMock->expects($this->any())
            ->method('findCount')
            ->will($this->returnValue(4));
        $emailRecipientsServiceMock->expects($this->any())
            ->method('find')
            ->will($this->returnValue(array()));

        $mailApi->expects($this->any())
            ->method('getEmailRecipientsService')
            ->will($this->returnValue($emailRecipientsServiceMock));

        $response = $mailApi->findRecipients($this->service, $args);
        $expected = -1;
        $actual   = $response['next_offset'];
        $this->assertEquals($expected, $actual, 'The next offset should be -1.');
    }

    public function testFindRecipients_OffsetIsEnd_ReturnsNextOffsetAsNegativeOne()
    {
        $args = array(
            'offset' => 'end',
        );

        $mailApi = $this->createPartialMock('EmailsApi', ['getEmailRecipientsService']);
        $emailRecipientsServiceMock = $this->createPartialMock('EmailRecipientsService', ['findCount', 'find']);
        $emailRecipientsServiceMock->expects($this->never())->method('findCount');
        $emailRecipientsServiceMock->expects($this->never())->method('find');

        $mailApi->expects($this->any())
            ->method('getEmailRecipientsService')
            ->will($this->returnValue($emailRecipientsServiceMock));

        $response = $mailApi->findRecipients($this->service, $args);
        $expected = -1;
        $actual   = $response['next_offset'];
        $this->assertEquals($expected, $actual, 'The next offset should be -1.');
    }

    public function testFindRecipients_NoArguments_CallsFindCountAndFindWithDefaults()
    {
        $args = array();

        $mailApi = $this->createPartialMock('EmailsApi', ['getEmailRecipientsService']);
        $emailRecipientsServiceMock = $this->createPartialMock('EmailRecipientsService', ['findCount', 'find']);
        $emailRecipientsServiceMock->expects($this->once())
            ->method('find')
            ->with(
                $this->isEmpty(),
                $this->equalTo('LBL_DROPDOWN_LIST_ALL'),
                $this->isEmpty(),
                $this->equalTo(21),
                $this->equalTo(0)
            )
            ->will($this->returnValue(array()));

        $mailApi->expects($this->any())
            ->method('getEmailRecipientsService')
            ->will($this->returnValue($emailRecipientsServiceMock));

        $mailApi->findRecipients($this->service, $args);
    }

    public function testFindRecipients_HasAllArguments_CallsFindCountAndFindWithArguments()
    {
        $args = array(
            'q' => 'foo',
            'module_list' => 'contacts',
            'order_by' => 'name,email:desc',
            'max_num' => 5,
            'offset' => 3,
        );

        $mailApi = $this->createPartialMock('EmailsApi', ['getEmailRecipientsService']);
        $emailRecipientsServiceMock = $this->createPartialMock('EmailRecipientsService', ['findCount', 'find']);
        $emailRecipientsServiceMock->expects($this->once())
            ->method('find')
            ->with(
                $this->equalTo($args['q']),
                $this->equalTo($args['module_list']),
                $this->equalTo(array('name' => 'ASC', 'email' => 'DESC')),
                $this->equalTo(6),
                $this->equalTo(3)
            )
            ->will($this->returnValue(array()));

        $mailApi->expects($this->any())
            ->method('getEmailRecipientsService')
            ->will($this->returnValue($emailRecipientsServiceMock));

        $mailApi->findRecipients($this->service, $args);
    }
}
