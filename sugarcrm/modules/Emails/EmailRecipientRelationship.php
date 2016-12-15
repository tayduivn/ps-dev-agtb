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
     * Disables self-referencing relationships.
     *
     * {@inheritdoc}
     */
    public function __construct($def)
    {
        parent::__construct($def);
        $this->self_referencing = false;
    }

    /**
     * When an email address is being linked and it collides with a row with the same email address:
     *
     * - The existing row is replaced by the new row if the existing row represents an email address.
     * - The existing row is not removed if it represents a record. The new row is added.
     * - The new row is not added if it represents an email address and the existing row represents a record.
     *
     * {@inheritdoc}
     */
    public function add($lhs, $rhs, $additionalFields = array())
    {
        LoggerManager::getLogger()->debug('EmailRecipientRelationship::add()');

        $dataToInsert = $this->getRowToInsert($lhs, $rhs, $additionalFields);
        LoggerManager::getLogger()->debug("Adding to {$this->name}");
        LoggerManager::getLogger()->debug('data=' . var_export($dataToInsert, true));

        $currentRows = $this->getCurrentRows($lhs);

        foreach ($currentRows as $currentRow) {
            LoggerManager::getLogger()->debug('current_row=' . var_export($currentRow, true));

            // Equality is checked loosely because null and empty strings need to be considered the same.
            if ($currentRow['email_address_id'] != $dataToInsert['email_address_id']) {
                LoggerManager::getLogger()->debug("The email_address_id's do not collide");
                continue;
            }

            LoggerManager::getLogger()->debug('The email_address_id columns collide');

            if ($dataToInsert['bean_type'] === 'EmailAddresses') {
                LoggerManager::getLogger()->debug(
                    'There is no benefit to updating the current row when the $dataToInsert[bean_type]=EmailAddresses'
                );

                if ($currentRow['bean_type'] === $dataToInsert['bean_type']) {
                    LoggerManager::getLogger()->debug('It is a no-op, so the framework can handle it');
                    continue;
                } else {
                    LoggerManager::getLogger()->debug('Preserve $currentRow[bean_type] and $currentRow[bean_id]');
                    return false;
                }
            }

            if ($currentRow['bean_type'] !== 'EmailAddresses') {
                LoggerManager::getLogger()->debug(
                    'Email addresses can be duplicated when bean_type and bean_id are not duplicated in order to ' .
                    'track all records that can be linked to the email'
                );
                continue;
            }

            LoggerManager::getLogger()->debug(
                'Replace the current row with the new row because we want the bean_type and bean_id data'
            );

            if (!$this->removeRowBeingReplaced($lhs, $currentRow)) {
                LoggerManager::getLogger()->error('Failed to remove current_row=' . var_export($currentRow, true));
                return false;
            }
        }

        if (parent::add($lhs, $rhs, $additionalFields)) {
            SugarRelationship::addToResaveList($lhs);
            return true;
        }

        return false;
    }

    /**
     * Adds $lhs to the resave list so that the emails_text data can be updated after unlinking $rhs.
     *
     * @param SugarBean $lhs
     * @param SugarBean $rhs
     * @return bool
     */
    public function remove($lhs, $rhs)
    {
        if (parent::remove($lhs, $rhs)) {
            SugarRelationship::addToResaveList($lhs);
            return true;
        }

        return false;
    }

    /**
     * When removing all rows using the right-hand side link, rows where the email_address_id is set are converted to
     * rows using EmailAddresses as the bean_type. This preserves historical data regarding email participants. Even if
     * the record ceases to exist, that email will continue to have to record of sending email from or to the particular
     * email address.
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
                    $this->def['join_key_rhs'] => $rhs->id,
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
                    $newLink = "email_addresses_{$row['address_type']}";

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
     * {@inheritdoc}
     */
    protected function getRowToInsert($lhs, $rhs, $additionalFields = array())
    {
        if ($rhs->module_dir === 'EmailAddresses') {
            $additionalFields['email_address_id'] = $rhs->id;
        }

        $row = parent::getRowToInsert($lhs, $rhs, $additionalFields);

        if (empty($row['email_address_id'])) {
            if (empty($row['email_address'])) {
                if ($lhs->state === Email::STATE_ARCHIVED) {
                    // This email is final, so choose the first valid email address.
                    $primary = $rhs->emailAddress->getPrimaryAddress($rhs);
                    $row['email_address_id'] = $rhs->emailAddress->getEmailGUID($primary);
                } else {
                    $row['email_address_id'] = null;
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
     * Only remove rows that match the role columns since the table is used for more than one relationship.
     *
     * {@inheritdoc}
     */
    protected function removeRow($where)
    {
        if (empty($where)) {
            return false;
        }

        $roleColumns = $this->getRelationshipRoleColumns();
        $where = array_merge($where, $roleColumns);

        return parent::removeRow($where);
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
     * The modified_user_id and created_by fields are not used in this relationship.
     *
     * {@inheritdoc}
     */
    protected function getStandardFields()
    {
        $fields = parent::getStandardFields();
        unset($fields['modified_user_id']);
        unset($fields['created_by']);

        return $fields;
    }

    /**
     * Returns the rows associated with an email and matching the role columns from this relationship.
     *
     * @param SugarBean $lhs
     * @return array
     */
    protected function getCurrentRows(SugarBean $lhs)
    {
        $rows = array();
        $roleColumns = $this->getRelationshipRoleColumns();
        $sql = "SELECT * FROM {$this->getRelationshipTable()} WHERE {$this->join_key_lhs}='{$lhs->id}' AND " .
            "address_type='{$roleColumns['address_type']}' AND deleted=0";
        $result = DBManagerFactory::getInstance()->query($sql);

        while ($row = DBManagerFactory::getInstance()->fetchByAssoc($result)) {
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * A row that is being replaced needs to be removed first. This is a convenience method that takes care of loading
     * the correct relationship and unlinking the RHS bean.
     *
     * @param SugarBean $lhs
     * @param array $row
     * @return bool
     */
    protected function removeRowBeingReplaced(SugarBean $lhs, array $row)
    {
        LoggerManager::getLogger()->debug('Removing a row that is being replaced');
        LoggerManager::getLogger()->debug('row=' . var_export($row, true));

        $rhs = BeanFactory::retrieveBean(
            $row['bean_type'],
            $row['bean_id'],
            array(
                'disable_row_level_security' => true,
            )
        );

        if ($this->getRHSModule() === $row['bean_type']) {
            LoggerManager::getLogger()->debug("Removing from this relationship: {$this->name}");
            return $this->remove($lhs, $rhs);
        } else {
            $module = $row['bean_type'] === 'EmailAddresses' ? 'email_addresses' : strtolower($row['bean_type']);
            $link = "{$module}_{$row['address_type']}";
            LoggerManager::getLogger()->debug("Removing from another relationship: link={$link}");

            if ($lhs->load_relationship($link)) {
                return $lhs->$link->delete($lhs->id, $rhs);
            } else {
                $lhsClass = get_class($lhs);
                LoggerManager::getLogger()->fatal("Could not load LHS {$link} in {$lhsClass}");
            }
        }

        return false;
    }

    /**
     * Adds an email address to the bean so that they are linked.
     *
     * @param SugarBean $bean
     * @param string $emailAddress
     * @return bool
     */
    private function addEmailAddressToRecord(SugarBean $bean, $emailAddress)
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
