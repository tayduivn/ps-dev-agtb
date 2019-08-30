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
 * Set portal_active to false for contacts without associated account.
 */
class SugarUpgradeDisablePortalForContactsWithoutAccount extends UpgradeDBScript
{
    public $order = 9999;

    /**
     * Execute upgrade tasks
     * This script sets portal_active to false for contacts without associated account.
     * @see UpgradeScript::run()
     */
    public function run()
    {
        // run on ent upgrade from pre-9.2.0
        if (version_compare($this->from_version, '9.2.0', '<') &&
            $this->toFlavor('ent') && $this->fromFlavor('ent')) {
            $this->log('Updating portal_active to false for contacts without associated account.');

            $query = new SugarQuery();
            $contact = BeanFactory::newBean('Contacts');
            $query->select(['c.id']);
            $query->from($contact, ['alias' => 'c', 'add_deleted' => true]);
            $query->joinTable('accounts_contacts', ['alias' => 'ac', 'joinType' => 'LEFT'])->on()
                ->equalsField('c.id', 'ac.contact_id')->equals('ac.primary_account', '1')
                ->equals('ac.deleted', '0');
            $query->where()->equals('c.portal_active', '1')->isNull('ac.account_id');
            $rows = $query->execute();
    
            foreach ($rows as $row) {
                $sql = 'UPDATE contacts SET portal_active = 0 WHERE id = ?';
                $this->executeUpdate($sql, [$row['id']]);
            }
        }
    }
}
