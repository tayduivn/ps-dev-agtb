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

class SugarUpgradeAssociateEmailsWithTheirParentNotes extends UpgradeScript
{
    public $order = 2000;
    public $type = self::UPGRADE_DB;

    /**
     * {@inheritdoc}
     *
     * Anytime an Emails record's parent reference changes, a reference to the parent record is added to the
     * emails_beans table. This was not happening when an Emails record's parent was a Notes record. This upgrade script
     * inserts these associations into the emails_beans tables so that the relationship between Emails and Notes behaves
     * the same as for all of Emails' other parent relationships.
     *
     * This upgrade script only runs when upgrading from a version prior to 7.9.
     */
    public function run()
    {
        if (version_compare($this->from_version, '7.9', '<')) {
            $inserts = $this->getNewAssociations();

            if (!empty($inserts)) {
                $sql = "INSERT INTO emails_beans (id, email_id, bean_id, bean_module, date_modified) VALUES " .
                    implode(', ', $inserts);
                $this->db->query($sql);
            }
        }
    }

    /**
     * Returns the values for any new associations that need to be inserted in the emails_beans table.
     *
     * Notes that are already properly associated with Emails are ignored to avoid duplicate rows in the emails_beans
     * table.
     *
     * @return array Array of SQL strings formatted for appending to an INSERT clause.
     */
    protected function getNewAssociations()
    {
        $associations = array();

        $sql = "SELECT id, parent_id FROM emails WHERE parent_type='Notes' AND parent_id IS NOT NULL AND parent_id<>''";
        $results = $this->db->query($sql);

        while ($row = $this->db->fetchByAssoc($results, false)) {
            if (!$this->associationExists($row['id'], $row['parent_id'])) {
                $associations[] = $this->getValues($row['id'], $row['parent_id']);
            }
        }

        return $associations;
    }

    /**
     * Does this combination of Emails ID and Notes ID already exist in the emails_beans table?
     *
     * @param string $emailId
     * @param string $noteId
     * @return bool
     */
    protected function associationExists($emailId, $noteId)
    {
        $sql = "SELECT COUNT(*) as num FROM emails_beans WHERE email_id='{$emailId}' AND bean_id='{$noteId}' AND " .
            "bean_module='Notes'";
        $results = $this->db->query($sql);

        if ($row = $this->db->fetchByAssoc($results, false)) {
            if ($row['num'] > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns a single set of values formatted in SQL for inserting a row into the emails_beans table.
     *
     * @param string $emailId
     * @param string $noteId
     * @return string
     */
    protected function getValues($emailId, $noteId)
    {
        $now = TimeDate::getInstance()->nowDb();
        $values = "('%s', '%s', '%s', 'Notes', '{$now}')";

        return sprintf($values, create_guid(), $emailId, $noteId);
    }
}
