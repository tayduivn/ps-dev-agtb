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

require_once 'modules/Emails/upgrade/scripts/post/2_AssignSystemTemplate.php';

/**
 * @coversDefaultClass SugarUpgradeAssignSystemTemplate
 */
class AssignSystemTemplateTest extends UpgradeTestCase
{
    /**
     * @covers ::run
     */
    public function testRun()
    {
        $script = $this->upgrader->getScript('post', '2_AssignSystemTemplate');
        $script->db = $GLOBALS['db'];
        $script->from_version = '7.9.0.0';
        $script->run();

        $lostPasswordTemplateId = $GLOBALS['sugar_config']['passwordsetting']['lostpasswordtmpl'];
        $generatePasswordTemplateId = $GLOBALS['sugar_config']['passwordsetting']['generatepasswordtmpl'];

        $lostPasswordTemplateType =
            $GLOBALS['db']->getConnection()
                ->executeQuery("SELECT type FROM email_templates WHERE id='$lostPasswordTemplateId'")
                ->fetchColumn();
        $this->assertEquals('system', $lostPasswordTemplateType, "Lost Password Template Type not 'system'");

        $generatePasswordTemplateType =
            $GLOBALS['db']->getConnection()
                ->executeQuery("SELECT type FROM email_templates WHERE id='$generatePasswordTemplateId'")
                ->fetchColumn();
        $this->assertEquals('system', $generatePasswordTemplateType, "Generate Password Template Type not 'system'");
    }
}
