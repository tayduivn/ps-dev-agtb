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
    protected $systemConfiguration;
    protected $currentUserConfiguration;
    protected $service;

    protected function setUp()
    {
        parent::setUp();
        OutboundEmailConfigurationTestHelper::setUp();
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');

        $this->systemConfiguration = OutboundEmailConfigurationTestHelper::getSystemConfiguration();
        $this->currentUserConfiguration = OutboundEmailConfigurationTestHelper::
        createSystemOverrideOutboundEmailConfiguration($GLOBALS['current_user']->id);
        OutboundEmailConfigurationTestHelper::setAllowDefaultOutbound(2);

        $this->service = SugarTestRestUtilities::getRestServiceMock();
    }

    protected function tearDown()
    {
        SugarTestEmailUtilities::removeAllCreatedEmails();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        OutboundEmailConfigurationTestHelper::tearDown();
        parent::tearDown();
    }

    public function cannotMakeInvalidStateChangeProvider()
    {
        return array(
            array(
                Email::STATE_DRAFT,
                Email::STATE_ARCHIVED,
            ),
            array(
                Email::STATE_ARCHIVED,
                Email::STATE_DRAFT,
            ),
            array(
                Email::STATE_ARCHIVED,
                Email::STATE_READY,
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
            'state' => Email::STATE_READY,
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
        $configId = $this->currentUserConfiguration->id;

        $email = $this->createPartialMock('Email', ['sendEmail']);
        $email->expects($this->once())
            ->method('sendEmail')
            ->with($this->callback(function ($config) use ($configId) {
                return $config->getConfigId() === $configId;
            }));
        $email->outbound_email_id = $this->currentUserConfiguration->id;

        $service = SugarTestRestUtilities::getRestServiceMock();
        $api = new EmailsApi();
        SugarTestReflection::callProtectedMethod($api, 'sendEmail', [$service, $email]);
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

        $service = SugarTestRestUtilities::getRestServiceMock();
        $api = new EmailsApi();
        SugarTestReflection::callProtectedMethod($api, 'sendEmail', [$service, $email]);
    }

    /**
     * @covers ::sendEmail
     */
    public function testSendEmail_UsesSystemOverrideConfigurationForAdmin()
    {
        OutboundEmailConfigurationTestHelper::setAllowDefaultOutbound(0);

        // Pretend that the current user is the admin with id=1.
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser(true, 1);
        $GLOBALS['current_user']->id = 1;

        // The admin should have a system-override configuration in addition to the system configuration.
        $override = OutboundEmailConfigurationTestHelper::createSystemOverrideOutboundEmailConfiguration(
            $GLOBALS['current_user']->id
        );
        $configId = $override->id;

        $email = $this->createPartialMock('Email', ['sendEmail']);
        $email->expects($this->once())
            ->method('sendEmail')
            ->with($this->callback(function ($config) use ($configId) {
                return $config->getConfigId() === $configId;
            }));

        $service = SugarTestRestUtilities::getRestServiceMock();
        $api = new EmailsApi();
        SugarTestReflection::callProtectedMethod($api, 'sendEmail', [$service, $email]);
    }

    /**
     * @covers ::sendEmail
     */
    public function testSendEmail_CurrentUserHasNoConfigurations_ThrowsException()
    {
        OutboundEmailConfigurationTestHelper::setAllowDefaultOutbound(0);

        // Make sure the current user doesn't have any configurations. The existing current user does.
        $saveUser = $GLOBALS['current_user'];
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();

        $email = $this->createPartialMock('Email', ['sendEmail']);
        $email->expects($this->never())->method('sendEmail');

        $caught = false;

        try {
            $service = SugarTestRestUtilities::getRestServiceMock();
            $api = new EmailsApi();
            SugarTestReflection::callProtectedMethod($api, 'sendEmail', [$service, $email]);
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

        $service = SugarTestRestUtilities::getRestServiceMock();
        $api = new EmailsApi();
        SugarTestReflection::callProtectedMethod($api, 'sendEmail', [$service, $email]);
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

        $service = SugarTestRestUtilities::getRestServiceMock();
        $api = new EmailsApi();
        SugarTestReflection::callProtectedMethod($api, 'sendEmail', [$service, $email]);
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

        $service = SugarTestRestUtilities::getRestServiceMock();
        $api = new EmailsApi();
        SugarTestReflection::callProtectedMethod($api, 'sendEmail', [$service, $email]);
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

        try {
            $service = SugarTestRestUtilities::getRestServiceMock();
            $api = new EmailsApi();
            SugarTestReflection::callProtectedMethod($api, 'sendEmail', [$service, $email]);
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
}
