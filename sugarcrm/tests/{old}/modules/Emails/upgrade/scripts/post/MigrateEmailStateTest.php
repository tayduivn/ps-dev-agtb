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

require_once 'tests/{old}/upgrade/UpgradeTestCase.php';
require_once 'modules/Emails/upgrade/scripts/post/2_MigrateEmailState.php';

/**
 * @coversDefaultClass SugarUpgradeMigrateEmailState
 */
class MigrateEmailStateTest extends UpgradeTestCase
{
    public function tearDown()
    {
        SugarTestEmailUtilities::removeAllCreatedEmails();
        parent::tearDown();
    }

    /**
     * @covers ::run
     */
    public function testRun()
    {
        //default type=out, status=sent
        SugarTestEmailUtilities::createEmail();
        SugarTestEmailUtilities::createEmail();
        SugarTestEmailUtilities::createEmail();

        //archived
        SugarTestEmailUtilities::createEmail('', array('status' => 'archived', 'type' => 'archived'));
        SugarTestEmailUtilities::createEmail('', array('status' => 'read', 'type' => 'archived'));
        SugarTestEmailUtilities::createEmail('', array('status' => 'unread', 'type' => 'archived'));

        //inbound
        SugarTestEmailUtilities::createEmail('', array('status' => 'archived', 'type' => 'inbound'));
        SugarTestEmailUtilities::createEmail('', array('status' => 'read', 'type' => 'inbound'));
        SugarTestEmailUtilities::createEmail('', array('status' => 'unread', 'type' => 'inbound'));
        SugarTestEmailUtilities::createEmail('', array('status' => 'replied', 'type' => 'inbound'));

        //draft
        SugarTestEmailUtilities::createEmail('', array('status' => 'draft', 'type' => 'draft'));
        SugarTestEmailUtilities::createEmail('', array('status' => 'draft', 'type' => 'draft'));
        SugarTestEmailUtilities::createEmail('', array('status' => 'draft', 'type' => 'draft'));
        SugarTestEmailUtilities::createEmail('', array('status' => 'read', 'type' => 'draft'));
        SugarTestEmailUtilities::createEmail('', array('status' => 'unread', 'type' => 'draft'));

        //campaign
        SugarTestEmailUtilities::createEmail('', array('status' => 'sent', 'type' => 'archived'));
        SugarTestEmailUtilities::createEmail('', array('status' => 'sent', 'type' => 'campaign'));

        //failure
        SugarTestEmailUtilities::createEmail('', array('status' => 'send_error', 'type' => 'out'));
        SugarTestEmailUtilities::createEmail('', array('status' => 'send_error', 'type' => 'out'));

        $script = $this->upgrader->getScript('post', '2_MigrateEmailState');
        $script->db = $GLOBALS['db'];
        $script->from_version = '7.9.0.0';
        $script->run();

        $expectedArchivedEmails = 12;
        $sql = "SELECT COUNT(id) FROM emails WHERE state='Archived'";
        $num = DBManagerFactory::getConnection()->executeQuery($sql)->fetchColumn();
        $this->assertEquals($expectedArchivedEmails, $num, "There should be {$expectedArchivedEmails} archived emails");

        $expectedDraftEmails = 7;
        $sql = "SELECT COUNT(id) FROM emails WHERE state='Draft'";
        $num = DBManagerFactory::getConnection()->executeQuery($sql)->fetchColumn();
        $this->assertEquals($expectedDraftEmails, $num, "There should be {$expectedDraftEmails} draft emails");
    }
}
