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
 * Class EmailsVisibility
 *
 * Additional visibility check for the Emails module.
 */
class EmailsVisibility extends SugarVisibility
{
    /**
     * Draft emails are only accessible by their assigned user.
     *
     * {@inheritdoc}
     */
    public function addVisibilityWhere(&$query)
    {
        $alias = $this->getOption('table_alias');
        $ownerWhere = $this->bean->getOwnerWhere($GLOBALS['current_user']->id, $alias);

        if (empty($alias)) {
            $alias = $this->bean->getTableName();
        }

        $where = "({$alias}.state<>'" . Email::EMAIL_STATE_DRAFT . "' OR ({$alias}.state='" . Email::EMAIL_STATE_DRAFT .
            "' AND{$ownerWhere}))";
        $query = empty($query) ? $where : "{$query} AND {$where}";

        return $query;
    }

    /**
     * Draft emails are only accessible by their assigned user.
     *
     * {@inheritdoc}
     */
    public function addVisibilityWhereQuery(SugarQuery $query)
    {
        $where = null;
        $this->addVisibilityWhere($where);

        if (!empty($where)) {
            $query->where()->queryAnd()->addRaw($where);
        }

        return $query;
    }
}
