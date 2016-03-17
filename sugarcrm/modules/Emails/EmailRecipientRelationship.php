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
 * Class EmailRecipientRelationship
 *
 * Represents a table-based many-to-many relationship between Emails and modules that can be recipients of an email. In
 * particular, each email can have many recipients, all coming from different modules. Emails should be on the left side
 * of the relationship.
 */
class EmailRecipientRelationship extends M2MRelationship
{
    /**
     * Disables the primary flag column, as this relationship does not support primary records. This has the effect of
     * allowing the relationship table to exclude the date_modified field, since the date_modified column is only
     * required when the primary flag column is enabled.
     *
     * Disables self-referencing relationships.
     *
     * {@inheritdoc}
     */
    public function __construct($def)
    {
        parent::__construct($def);
        $this->def['primary_flag_column'] = false;
        $this->self_referencing = false;
    }

    /**
     * Can only remove all records using the left-hand side link.
     *
     * {@inheritdoc}
     */
    public function removeAll($link)
    {
        if ($link->getSide() === REL_RHS) {
            //TODO: Add a new EmailAddresses row for each row removed.
            // Most likely this is because the rhs bean was deleted.
            // Find all of the rows where $focus appears for this link.
            // For each row...
            // Remove the row.
            // (If the row is a draft, do we do anything other than remove it?)
            // If the row has an email_address_id then capture it.
            // Else call $focus->emailAddress->getPrimaryAddress($focus) and find the ID for that email address.
            // Load the lhs bean for email_id and load the rhs bean for the captured ID and add(lhs, rhs).
            // Return true if everything was successful or false.
            return true;
        }

        return parent::removeAll($link);
    }

    /**
     * Patches $additionalFields['email_address_id'] when adding an EmailAddresses record.
     *
     * Patches $additionalFields['email_address_id'] if $additionalFields['email_address'] is provided instead. This
     * requires discovering the ID of the email address and then guaranteeing that the email address is linked to the
     * right-hand side record.
     *
     * The field date_modified is not used in this relationship.
     *
     * {@inheritdoc}
     */
    protected function getRowToInsert($lhs, $rhs, $additionalFields = array())
    {
        if ($rhs->module_dir === 'EmailAddresses') {
            $additionalFields['email_address_id'] = $rhs->id;
        }

        $row = parent::getRowToInsert($lhs, $rhs, $additionalFields);
        unset($row['date_modified']);

        if (empty($row['email_address_id'])) {
            if (empty($row['email_address'])) {
                if (in_array($lhs->state, array(Email::EMAIL_STATE_ARCHIVED, Email::EMAIL_STATE_READY))) {
                    // This email is final, so choose the first valid email address.
                    $primary = $rhs->emailAddress->getPrimaryAddress($rhs);
                    $row['email_address_id'] = $rhs->emailAddress->getEmailGUID($primary);
                }
            } else {
                // An email address was given. Use it to get an ID.
                $row['email_address_id'] = $rhs->emailAddress->getEmailGUID($row['email_address']);

                if (!$this->addEmailAddressToRecord($rhs, $row['email_address'])) {
                    LoggerManager::getLogger()->error(
                        "Failed to add {$row['email_address']} to {$rhs->module_dir}/{$rhs->id} for {$this->name} " .
                        "within EmailRecipientRelationship::getRowToInsert()"
                    );
                }
            }
        }

        /**
         * TODO: If participant_module is EmailAddresses and the email address is linked to only one record, then it
         * should be safe to update participant_module and participant_id to match that record. Unless this is a brand
         * new person who just happens to have the same email address.
         */

        unset($row['email_address']);

        return $row;
    }

    /**
     * Physically deletes the row.
     *
     * {@inheritdoc}
     */
    protected function removeRow($where)
    {
        if (empty($where)) {
            return false;
        }

        $stringSets = array();

        foreach ($where as $field => $val) {
            $stringSets[] = "{$field}='{$val}'";
        }

        $query = 'DELETE FROM ' .
            $this->getRelationshipTable() .
            ' WHERE ' .
            implode(' AND ', $stringSets) .
            $this->getRoleWhere();

        return DBManagerFactory::getInstance()->query($query);
    }

    /**
     * Self-referencing relationships are not supported. This is a no-op.
     *
     * {@inheritdoc}
     */
    protected function addSelfReferencing($lhs, $rhs, $additionalFields = array())
    {
        return true;
    }

    /**
     * Self-referencing relationships are not supported. This is a no-op.
     *
     * {@inheritdoc}
     */
    protected function removeSelfReferencing($lhs, $rhs, $additionalFields = array())
    {
        return true;
    }

    /**
     * The fields date_modified, modified_user_id, and created_by are not used in this relationship.
     *
     * {@inheritdoc}
     */
    protected function getStandardFields()
    {
        $fields = parent::getStandardFields();
        unset($fields['date_modified']);
        unset($fields['modified_user_id']);
        unset($fields['created_by']);

        return $fields;
    }

    /**
     * Adds an email address to the bean so that they are linked.
     *
     * @param SugarBean $bean
     * @param string $emailAddress
     * @return bool
     */
    protected function addEmailAddressToRecord(SugarBean $bean, $emailAddress)
    {
        $emailAddresses = $bean->emailAddress->getAddressesForBean($bean);
        $matches = array_filter($emailAddresses, function ($address) use ($emailAddress) {
            return $address['email_address'] === $emailAddress;
        });

        if (count($matches) === 0) {
            if ($bean->emailAddress->addAddress($emailAddress) === false) {
                return false;
            } else {
                $bean->emailAddress->save($bean->id, $bean->module_dir);
                $bean->emailAddress->dontLegacySave = true;
                $bean->emailAddress->populateLegacyFields($bean);
            }
        }

        return true;
    }
}
