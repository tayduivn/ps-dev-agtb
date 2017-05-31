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

require_once 'tests/{old}/modules/OutboundEmailConfiguration/OutboundEmailConfigurationTestHelper.php';

/**
 * @coversDefaultClass OutboundEmail
 */
class OutboundEmailTest extends Sugar_PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        OutboundEmailConfigurationTestHelper::backupExistingConfigurations();
        SugarTestHelper::setUp('current_user');
    }

    public static function tearDownAfterClass()
    {
        OutboundEmailConfigurationTestHelper::restoreExistingConfigurations();
        parent::tearDownAfterClass();
    }

    public function smtpEmailPasswordProvider()
    {
        return [
            [
                'My&amp;Password',
                'My&Password',
            ],
            [
                'My&quot;Password',
                'My"Password',
            ],
            [
                'My&#039;Password',
                'My\'Password',
            ],
            [
                'My&lt;Password',
                'My<Password',
            ],
            [
                'My&gt;Password',
                'My>Password',
            ],
        ];
    }

    /**
     * Proves that encoded HTML characters are decoded when saving an SMTP password to the database.
     *
     * @covers ::save
     * @covers ::retrieve
     * @dataProvider smtpEmailPasswordProvider
     */
    public function testSaveOutboundEmailConfigurationWithPassword($encodedPassword, $decodedPassword)
    {
        $configuration = array(
            'name' => 'User Configuration',
            'type' => 'user',
            'user_id' => $GLOBALS['current_user']->id,
            'from_email' => 'foo@bar.com',
            'from_name' => 'Foo Bar',
            'mail_sendtype' => 'SMTP',
            'mail_smtptype' => 'other',
            'mail_smtpserver' => 'smtp.example.com',
            'mail_smtpport' => '25',
            'mail_smtpuser' => 'mickey',
            'mail_smtppass' => $encodedPassword,
            'mail_smtpauth_req' => '1',
            'mail_smtpssl' => '0',
        );
        $configuration = OutboundEmailConfigurationTestHelper::createOutboundEmail($configuration);

        $record = new OutboundEmail();
        $record->retrieve($configuration->id);
        $this->assertSame($decodedPassword, $record->mail_smtppass);
    }
}
