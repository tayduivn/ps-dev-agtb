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
class SugarUpgradeEmailTemplatesUpdateHasVariables extends UpgradeScript
{
    public $order = 2180;
    public $type = self::UPGRADE_DB;

    public function run()
    {
        // are we coming from anything before 7.9?
        if (!version_compare($this->from_version, '7.9', '<')) {
            return;
        }

        $this->log('Updating Email Templates has_variables field');

        $sql = 'UPDATE email_templates ' .
                'SET has_variables = 1 ' .
                'WHERE CONCAT(body, " ", body_html) REGEXP "\\\$[a-zA-Z]*\\\_[a-zA-Z0-9_]*"';

        $r = $this->db->query($sql);
        $this->log('SQL Ran, Updated ' . $this->db->getAffectedRowCount($r) . ' Rows');
        $this->log('Done updating Email Templates has_variables field');
    }
}
