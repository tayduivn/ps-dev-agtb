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


/**
 * @group email
 * @group mailer
 */
class PHPMailerProxyTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function testCreateHeader_GeneratesMessageID()
    {
        $hostname = 'mycompany.com';

        $mailer = $this->getMockBuilder('PHPMailerProxy')
            ->setMethods(array(
                'headerLine',
                'addrFormat',
                'addrAppend',
                'encodeHeader',
                'secureHeader',
                'serverHostname',
                'getMailMIME',
            ))
            ->getMock();
        $mailer->method('headerLine')->willReturn('');
        $mailer->method('addrFormat')->willReturn('');
        $mailer->method('addrAppend')->willReturn('');
        $mailer->method('encodeHeader')->willReturn('');
        $mailer->method('secureHeader')->willReturn('');
        $mailer->method('serverHostname')->willReturn($hostname);
        $mailer->method('getMailMIME')->willReturn('');

        $mailer->createHeader();
        $this->assertRegExp('/\<\d+\.[a-fA-F0-9]+@' . $hostname . '\>/', $mailer->MessageID);
        $this->assertSame($mailer->MessageID, $mailer->getLastMessageID());
    }

    public function testCreateHeader_UsesExistingMessageID()
    {
        $mailer = $this->getMockBuilder('PHPMailerProxy')
            ->setMethods(
                array(
                    'headerLine',
                    'addrFormat',
                    'addrAppend',
                    'encodeHeader',
                    'secureHeader',
                    'getMailMIME',
                )
            )
            ->getMock();
        $mailer->method('headerLine')->willReturn('');
        $mailer->method('addrFormat')->willReturn('');
        $mailer->method('addrAppend')->willReturn('');
        $mailer->method('encodeHeader')->willReturn('');
        $mailer->method('secureHeader')->willReturn('');
        $mailer->method('getMailMIME')->willReturn('');

        $mailer->MessageID = 'foo';
        $mailer->createHeader();
        $this->assertSame('foo', $mailer->MessageID);
        $this->assertSame($mailer->MessageID, $mailer->getLastMessageID());
    }
}
