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
     * When removing all rows using the right-hand side link, rows where the email_address_id is set are converted to
     * rows using EmailAddresses as the participant_module. This preserves historical data regarding email participants.
     * Even if the record ceases to exist, that email will continue to have to record of sending email from or to the
     * particular email address.
     *
     * {@inheritdoc}
     */
    public function removeAll($link)
    {
        if ($link->getSide() === REL_RHS) {
            // Most likely the right-hand side bean was deleted.
            $removeAllResult = true;
            $rhs = $link->getFocus();

            // Find all rows to remove.
            $beans = $link->getBeans();

            foreach ($beans as $lhs) {
                // Get the existing row so you know what email_address_id is.
                $args = array(
                    $this->def['join_key_lhs'] => $lhs->id,
                    $this->def['join_key_rhs'] => $rhs->id
                );
                $row = $this->checkExisting($args);

                if (empty($row)) {
                    $removeAllResult = false;
                    LoggerManager::getLogger()->error(
                        "Warning: row did not exist for relationship {$this->name} within " .
                        "EmailRecipientRelationship->removeAll()  dataToRemove: " . var_export($args, true)
                    );
                    continue;
                }

                // Remove the row.
                $removeResult = $this->remove($lhs, $rhs);
                $addResult = true;

                // Replace the row with a new row representing the email address used.
                if (!empty($row['email_address_id'])) {
                    $newLink = "email_addresses_{$row['role']}";

                    LoggerManager::getLogger()->debug(
                        "Replace {$rhs->module_dir}/{$rhs->id} with EmailAddresses/{$row['email_address_id']} for " .
                        "link {$newLink} on {$lhs->module_dir}/{$lhs->id}"
                    );

                    if ($lhs->load_relationship($newLink)) {
                        $address = BeanFactory::retrieveBean(
                            'EmailAddresses',
                            $row['email_address_id'],
                            array('disable_row_level_security' => true)
                        );

                        if ($lhs->$newLink->add($address) !== true) {
                            $addResult = false;
                            LoggerManager::getLogger()->error(
                                "Warning: failed to replace {$rhs->module_dir}/{$rhs->id} with EmailAddresses/" .
                                "{$address->id} for link {$newLink} on {$lhs->module_dir}/{$lhs->id} within " .
                                'EmailRecipientRelationship->removeAll()'
                            );
                        }
                    } else {
                        $addResult = false;
                        $lhsClass = get_class($lhs);
                        LoggerManager::getLogger()->fatal("could not load LHS {$newLink} in {$lhsClass}");
                    }
                }

                $removeAllResult = $removeAllResult && $removeResult && $addResult;
            }

            return $removeAllResult;
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
