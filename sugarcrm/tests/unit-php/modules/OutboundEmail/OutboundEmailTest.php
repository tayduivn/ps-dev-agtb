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

namespace Sugarcrm\SugarcrmTestsUnit\modules\OutboundEmail;

/**
 * @coversDefaultClass \OutboundEmail
 */
class OutboundEmailTest extends \PHPUnit_Framework_TestCase
{
    public function isConfiguredProvider()
    {
        $server = 'smtp.example.com';
        $username = 'julio';
        $password = 'xhjd7h3kHjkhas';

        return [
            [
                $server,
                true,
                $username,
                $password,
                true,
            ],
            [
                $server,
                false,
                $username,
                $password,
                true,
            ],
            [
                $server,
                false,
                $username,
                null,
                true,
            ],
            [
                $server,
                false,
                null,
                $password,
                true,
            ],
            [
                $server,
                false,
                null,
                null,
                true,
            ],
            [
                null,
                false,
                null,
                null,
                false,
            ],
            [
                null,
                true,
                $username,
                $password,
                false,
            ],
            [
                $server,
                true,
                null,
                $password,
                false,
            ],
            [
                $server,
                true,
                $username,
                null,
                false,
            ],
            [
                $server,
                true,
                null,
                null,
                false,
            ],
        ];
    }

    /**
     * @covers ::isConfigured
     * @dataProvider isConfiguredProvider
     * @param $server
     * @param $auth
     * @param $username
     * @param $password
     * @param $expected
     */
    public function testIsConfigured($server, $auth, $username, $password, $expected)
    {
        $oe = \BeanFactory::newBean('OutboundEmail');
        $oe->mail_smtpserver = $server;
        $oe->mail_smtpauthreq = $auth;
        $oe->mail_smtpuser = $username;
        $oe->mail_smtppass = $password;
        $actual = $oe->isConfigured();
        $this->assertSame($expected, $actual);
    }
}
