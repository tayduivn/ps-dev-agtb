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
        if (!$this->relationship_exists($lhs, $rhs)) {
            $dataToInsert = $this->getRowToInsert($lhs, $rhs, $additionalFields);
            $currentRow = $this->getCurrentRow($lhs);

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
        $currentRow = $this->getCurrentRow($lhs);

        if ($currentRow) {
            $rhs = BeanFactory::retrieveBean(
                $currentRow['bean_type'],
                $currentRow['bean_id'],
                array(
                    'disable_row_level_security' => true,
                )
            );
            return $this->remove($lhs, $rhs);
        }

        return true;
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

    /**
     * Returns the row for a particular email where the role is "from."
     *
     * @param SugarBean $lhs An email bean.
     * @return bool|array
     */
    private function getCurrentRow(SugarBean $lhs)
    {
        $query = 'SELECT * FROM '
            . $this->getRelationshipTable() .
            " WHERE {$this->def['join_key_lhs']}='{$lhs->id}' " .
            $this->getRoleWhere() .
            ' AND deleted=0';
        $row = DBManagerFactory::getInstance()->fetchOne($query);

        return empty($row) ? false : $row;
    }
}
