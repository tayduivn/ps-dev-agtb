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

require_once 'modules/Emails/EmailRecipientRelationship.php';

/**
 * Class EmailSenderRelationship
 *
 * Represents a table-based one-to-many relationship between Emails and modules that can be senders of an email. In
 * particular, each email can have one sender, each one coming from a different module. Emails should be on the left
 * side of the relationship.
 */
class EmailSenderRelationship extends EmailRecipientRelationship
{
    public $type = 'one-to-many';

    /**
     * If an email already has a sender, then the sender is removed before the new one is added.
     *
     * When an email address is being linked and the existing row has the same email address:
     *
     * - The existing row is replaced by the new row if the new row represents a record.
     * - The new row is not added if it represents an email address and the existing row represents a record.
     *
     * {@inheritdoc}
     */
    public function add($lhs, $rhs, $additionalFields = array())
    {
        LoggerManager::getLogger()->debug('EmailSenderRelationship::add()');

        $dataToInsert = $this->getRowToInsert($lhs, $rhs, $additionalFields);
        LoggerManager::getLogger()->debug("Adding to {$this->name}");
        LoggerManager::getLogger()->debug('data=' . var_export($dataToInsert, true));

        $currentRows = $this->getCurrentRows($lhs);

        // There can only be one. But just in case something weird happens, we'll iterate through the rows.
        foreach ($currentRows as $currentRow) {
            LoggerManager::getLogger()->debug('current_row=' . var_export($currentRow, true));

            if ($this->compareRow($currentRow, $dataToInsert, array('id', 'date_modified', 'email_address_id'))) {
                LoggerManager::getLogger()->debug(
                    'It is either a no-op or an update to email_address_id, so the framework can handle it'
                );
                continue;
            }

            if ($dataToInsert['bean_type'] !== 'EmailAddresses') {
                LoggerManager::getLogger()->debug('Replace the current row with the new row');

                if (!$this->removeRowBeingReplaced($lhs, $currentRow)) {
                    LoggerManager::getLogger()->error('Failed to remove current_row=' . var_export($currentRow, true));
                    return false;
                }

                continue;
            }

            // Equality is checked loosely because null and empty strings need to be considered the same.
            $hasEmailAddressCollision = $currentRow['email_address_id'] == $dataToInsert['email_address_id'];

            if ($hasEmailAddressCollision) {
                LoggerManager::getLogger()->debug('The email_address_id columns collide');
            }

            if ($hasEmailAddressCollision && $currentRow['bean_type'] !== 'EmailAddresses') {
                LoggerManager::getLogger()->debug('Preserve $currentRow[bean_type] and $currentRow[bean_id]');
                return false;
            }

            $message = 'Replace the current row with the new row because ';

            if ($hasEmailAddressCollision) {
                $message .= 'we want the bean_type and bean_id data from the new row';
            } else {
                $message .= 'we want the new email address';
            }

            LoggerManager::getLogger()->debug($message);

            if (!$this->removeRowBeingReplaced($lhs, $currentRow)) {
                LoggerManager::getLogger()->error('Failed to remove current_row=' . var_export($currentRow, true));
                return false;
            }
        }

        return parent::add($lhs, $rhs, $additionalFields);
    }

    /**
     * Removes any existing row for a particular email where the role is "from."
     *
     * {@inheritdoc}
     */
    public function removeAll($link)
    {
        LoggerManager::getLogger()->debug('EmailSenderRelationship::removeAll()');
        LoggerManager::getLogger()->debug("Removing from {$this->name}");

        if ($link->getSide() === REL_RHS) {
            LoggerManager::getLogger()->debug("{$link->name} is the RHS of the relationship");
            return parent::removeAll($link);
        }

        LoggerManager::getLogger()->debug("{$link->name} is the LHS of the relationship");
        $result = true;
        $lhs = $link->getFocus();
        $currentRows = $this->getCurrentRows($lhs);

        // There can only be one. But just in case something weird happens, we'll iterate through the rows.
        foreach ($currentRows as $currentRow) {
            LoggerManager::getLogger()->debug('Removing current_row=' . var_export($currentRow, true));

            if ($this->removeRowBeingReplaced($lhs, $currentRow)) {
                $result = $result && true;
            } else {
                LoggerManager::getLogger()->error('Failed to remove current_row=' . var_export($currentRow, true));
                $result = false;
            }
        }

        return $result;
    }

    /**
     * Patches $additionalFields['bean_type'] to guarantee that it is always the module of the right-hand side record.
     * This is required because bean_type isn't a role column for these relationships, whereas it is a role column for
     * the recipients relationships.
     *
     * {@inheritdoc}
     */
    protected function getRowToInsert($lhs, $rhs, $additionalFields = array())
    {
        $additionalFields['bean_type'] = $rhs->module_dir;
        return parent::getRowToInsert($lhs, $rhs, $additionalFields);
    }
}
