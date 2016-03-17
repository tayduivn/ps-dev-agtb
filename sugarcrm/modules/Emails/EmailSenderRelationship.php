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
     * {@inheritdoc}
     */
    public function add($lhs, $rhs, $additionalFields = array())
    {
        $dataToInsert = $this->getRowToInsert($lhs, $rhs, $additionalFields);
        $currentRow = $this->checkExisting($dataToInsert);

        if ($currentRow && !$this->compareRow($currentRow, $dataToInsert)) {
            $lhsLinkName = $this->lhsLink;

            if (empty($lhs->$lhsLinkName) && !$lhs->load_relationship($lhsLinkName)) {
                $lhsClass = get_class($lhs);
                LoggerManager::getLogger()->fatal("could not load LHS {$lhsLinkName} in {$lhsClass}");
                return false;
            }

            if ($this->removeAll($lhs->$lhsLinkName) === false) {
                LoggerManager::getLogger()->error(
                    "Warning: failure calling removeAll() on lhsLinkName: {$lhsLinkName} for relationship " .
                    "{$this->name} within EmailSenderRelationship->add()."
                );
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
        if ($link->getSide() === REL_RHS) {
            return parent::removeAll($link);
        }

        $lhs = $link->getFocus();
        $data = array(
            $this->def['join_key_lhs'] => $lhs->id,
        );
        $currentRow = $this->checkExisting($data);

        if ($currentRow) {
            $rhs = BeanFactory::retrieveBean(
                $currentRow['participant_module'],
                $currentRow['participant_id'],
                array(
                    'disable_row_level_security' => true,
                )
            );
            return $this->remove($lhs, $rhs);
        }

        return true;
    }

    /**
     * Returns the row for a particular email where the role is "from."
     *
     * {@inheritdoc}
     */
    protected function checkExisting($row)
    {
        $lhsKey = $this->def['join_key_lhs'];

        if (empty($row[$lhsKey])) {
            return false;
        }

        $query = 'SELECT * FROM '
            . $this->getRelationshipTable() .
            " WHERE {$lhsKey}='{$row[$lhsKey]}' " .
            $this->getRoleWhere() .
            ' AND deleted=0';
        $row = DBManagerFactory::getInstance()->fetchOne($query);

        return empty($row) ? false : $row;
    }

    /**
     * Patches $additionalFields['participant_module'] to guarantee that it is always the module of the right-hand side
     * record. This is required because participant_module isn't a role column for these relationships, whereas it is a
     * role column for the recipients relationships.
     *
     * {@inheritdoc}
     */
    protected function getRowToInsert($lhs, $rhs, $additionalFields = array())
    {
        $additionalFields['participant_module'] = $rhs->module_dir;
        return parent::getRowToInsert($lhs, $rhs, $additionalFields);
    }
}
