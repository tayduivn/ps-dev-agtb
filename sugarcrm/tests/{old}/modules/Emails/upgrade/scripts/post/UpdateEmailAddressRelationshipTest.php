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
require_once 'modules/Emails/upgrade/scripts/post/2_UpdateEmailAddressRelationshipTable.php';

use Sugarcrm\Sugarcrm\Util\Uuid;

/**
 * @coversDefaultClass SugarUpgradeUpdateEmailAddressRelationshipTable
 */
class UpdateEmailAddressRelationshipTest extends UpgradeTestCase
{
    protected $emailIds = array();

    protected function tearDown()
    {
        if (!empty($this->emailIds)) {
            $emails = "'" . implode("','", $this->emailIds) . "'";
            $GLOBALS['db']->getConnection()->executeQuery(
                "DELETE FROM emails_email_addr_rel WHERE email_id in ({$emails})"
            );
        }
        $this->emailIds = array();
        parent::tearDown();
    }

    /**
     * @covers ::run
     */
    public function testRun()
    {
        $records = 3;
        for ($i = 0; $i < $records; $i++) {
            $emailId = Uuid::uuid1();
            $this->emailIds[] = $emailId;
            $id = Uuid::uuid1();
            $emailAddressId = Uuid::uuid1();
            $values = array($id, $emailId, 'to', $emailAddressId, '', '', 0);
            $sValues = "'" . implode("','", $values) . "', NULL";
            $sql = "INSERT into emails_email_addr_rel VALUES($sValues)";
            $GLOBALS['db']->getConnection()->executeQuery($sql);
        }

        $script = $this->upgrader->getScript('post', '2_UpdateEmailAddressRelationshipTable');
        $script->db = $GLOBALS['db'];
        $script->from_version = '7.9.0.0';
        $script->run();

        $emails = "'" . implode("','", $this->emailIds) . "'";
        $sql = "SELECT COUNT(id) FROM emails_email_addr_rel WHERE email_id in ({$emails})" .
            " AND bean_type='EmailAddresses' AND bean_id=email_address_id";
        $num = $GLOBALS['db']->getConnection()->executeQuery($sql)->fetchColumn();
        $this->assertEquals($records, $num, "Expected {$records} relationships to be updated");
    }
}
