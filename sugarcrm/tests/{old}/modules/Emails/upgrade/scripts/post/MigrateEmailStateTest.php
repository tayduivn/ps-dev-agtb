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
        SugarTestEmailUtilities::createEmail();
        SugarTestEmailUtilities::createEmail();
        SugarTestEmailUtilities::createEmail();
        SugarTestEmailUtilities::createEmail('', array('status' => 'sent', 'type' => 'out'));
        SugarTestEmailUtilities::createEmail('', array('status' => 'unread', 'type' => 'inbound'));
        SugarTestEmailUtilities::createEmail('', array('status' => 'draft', 'type' => 'draft'));
        SugarTestEmailUtilities::createEmail('', array('status' => 'draft', 'type' => 'draft'));
        SugarTestEmailUtilities::createEmail('', array('status' => 'draft', 'type' => 'out'));
        SugarTestEmailUtilities::createEmail('', array('status' => 'draft', 'type' => 'archived'));
        SugarTestEmailUtilities::createEmail('', array('status' => 'draft', 'type' => 'draft'));
        SugarTestEmailUtilities::createEmail('', array('status' => 'send_error', 'type' => 'out'));
        SugarTestEmailUtilities::createEmail('', array('status' => 'send_error', 'type' => 'out'));

        $script = $this->upgrader->getScript('post', '2_MigrateEmailState');
        $script->db = $GLOBALS['db'];
        $script->from_version = '7.8.0.0';
        $script->run();

        $num = $GLOBALS['db']->getOne("SELECT COUNT(id) FROM emails WHERE state='Archived'");
        $this->assertEquals(5, $num, 'There should be 5 archived emails');

        $num = $GLOBALS['db']->getOne("SELECT COUNT(id) FROM emails WHERE state='Draft'");
        $this->assertEquals(7, $num, 'There should be 7 draft emails');
    }
}
