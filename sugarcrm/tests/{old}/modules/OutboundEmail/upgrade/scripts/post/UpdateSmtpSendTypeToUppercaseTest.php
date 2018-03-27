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

require_once 'modules/OutboundEmail/upgrade/scripts/post/2_UpdateSmtpSendTypeToUppercase.php';

/**
 * @coversDefaultClass SugarUpgradeUpdateSmtpSendTypeToUppercase
 *
 * Test Update of Outbound Emails with mail_sendtype = 'smtp' to 'SMTP'
 */
class UpdateSmtpSendTypeToUppercaseTest extends UpgradeTestCase
{
    protected function setUp()
    {
        parent::setUp();
        OutboundEmailConfigurationTestHelper::setUp();
    }

    protected function tearDown()
    {
        parent::tearDown();
        OutboundEmailConfigurationTestHelper::tearDown();
    }

    /**
     * @covers ::run
     */
    public function testRun()
    {
        $userInfo = array(
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'johndoe1@abc.xyz',
        );
        $user = SugarTestUserUtilities::createAnonymousUser(true, 0, $userInfo);

        /*-- Create Outbound Email Records with mail_sendtype = 'smtp' --*/
        $oe = OutboundEmailConfigurationTestHelper::createUserOutboundEmailConfiguration($user->id);
        $oe->mail_sendtype = 'smtp';
        $oe->save();

        $row = $this->getMailSendTypeFromOutboundEmail($oe->id);
        $this->assertSame('smtp', $row['mail_sendtype'], 'Unexpected mail_sendtype value - expected Lowercase');

        /*--- Run Upgrader Script ---*/
        $script = $this->upgrader->getScript('post', '2_UpdateSmtpSendTypeToUppercase');
        $script->db = $GLOBALS['db'];
        $script->from_version = '7.9.0.0';
        $script->run();

        $row = $this->getMailSendTypeFromOutboundEmail($oe->id);
        $this->assertSame('SMTP', $row['mail_sendtype'], 'Unexpected mail_sendtype value - expected Uppercase');
    }

    protected function getMailSendTypeFromOutboundEmail($outboundEmailId)
    {
        $sql = 'SELECT id, name, mail_sendtype FROM outbound_email WHERE id = ? AND deleted=0';
        $stmt = DBManagerFactory::getConnection()->executeQuery($sql, [$outboundEmailId]);
        if ($row = $stmt->fetch()) {
            return $row;
        }
        return array();
    }
}
