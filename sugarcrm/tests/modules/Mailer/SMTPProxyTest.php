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

require_once 'modules/Mailer/SMTPProxy.php';

/**
 * @group email
 * @group mailer
 */
class SMTPProxyTest extends Sugar_PHPUnit_Framework_TestCase
{
    private static $logger;

    /**
     * Stores the logger so it can be restored.
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        static::$logger = $GLOBALS['log'];
    }

    /**
     * Restores the logger.
     */
    public static function tearDownAfterClass()
    {
        $GLOBALS['log'] = static::$logger;
    }

    public function testSendCommand_NotConnected_WarningIsLogged()
    {
        $GLOBALS['log'] = $this->getMock('SugarMockLogger', array('__call'));
        $GLOBALS['log']->expects($this->any())->method('__call')->with($this->equalTo('warn'));

        $smtpMock = $this->getMock('SMTPProxy', array('connected'));
        $smtpMock->expects($this->any())->method('connected')->willReturn(false);

        $this->assertFalse($smtpMock->hello(), 'Hello should have returned false');
    }

    public function testSendCommand_IsConnected_NothingIsLogged()
    {
        $GLOBALS['log'] = new SugarMockLogger();

        $smtpMock = $this->getMock('SMTPProxy', array('connected', 'client_send', 'get_lines'));
        $smtpMock->expects($this->any())->method('connected')->willReturn(true);
        $smtpMock->expects($this->any())->method('client_send')->willReturn(50);
        $smtpMock->expects($this->any())
            ->method('get_lines')
            ->willReturn('250 Hello relay.example.org, I am glad to meet you');

        $this->assertTrue($smtpMock->hello(), 'Hello should have returned true');
        $this->assertEquals(0, $GLOBALS['log']->getMessageCount(), 'The logger should not have any errors to log');
    }

    public function testSendCommand_RespondsWithError_FatalIsLogged()
    {
        $GLOBALS['log'] = $this->getMock('SugarMockLogger', array('__call'));
        $GLOBALS['log']->expects($this->any())->method('__call')->with($this->equalTo('fatal'));

        $smtpMock = $this->getMock('SMTPProxy', array('connected', 'client_send', 'get_lines'));
        $smtpMock->expects($this->any())->method('connected')->willReturn(true);
        $smtpMock->expects($this->any())->method('client_send')->willReturn(50);
        $smtpMock->expects($this->any())
            ->method('get_lines')
            ->willReturn('421 relay.example.org Service not available, closing transmission channel');

        $this->assertFalse($smtpMock->hello(), 'Hello should have returned false');
    }

    /**
     * TRUE is returned by {@link SMTP::connected()} because that will immediately short-circuit the call to
     * {@link SMTP::connect()}, avoiding anything to do with socket streams, etc. No matter the result of the call to
     * {@link SMTP::connect()}, {@link SMTPProxy::connect()} should call {@link SMTPProxy::handleError()}.
     */
    public function testConnect_CallsHandleError()
    {
        $smtpMock = $this->getMock('SMTPProxy', array('connected', 'handleError'));
        $smtpMock->expects($this->any())->method('connected')->willReturn(true);
        $smtpMock->expects($this->once())->method('handleError');

        $smtpMock->connect('localhost', 25);
    }

    /**
     * {@link SMTPProxy::authenticate()} calls {@link SMTPProxy::handleError()} when {@link SMTP::smtp_conn} is not a
     * resource, which is the case in this test. {@link SMTPProxy::handleError()} would be called by
     * {@link SMTPProxy::sendCommand()} when {@link SMTP::smtp_conn} is a valid resource and the call is allowed to
     * {@link SMTP::authenticate()}.
     */
    public function testAuthenticate_CallsHandleError()
    {
        $smtpMock = $this->getMock('SMTPProxy', array('handleError'));
        $smtpMock->expects($this->once())->method('handleError');

        $smtpMock->authenticate('foo', 'bar');
    }

    public function testTurn_CallsHandleError()
    {
        $smtpMock = $this->getMock('SMTPProxy', array('handleError'));
        $smtpMock->expects($this->once())->method('handleError');

        $smtpMock->turn();
    }
}
