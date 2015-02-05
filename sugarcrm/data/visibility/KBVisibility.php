<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

/**
 * Class KBVisibility
 * Addidional visibility check for KB.
 */
class KBVisibility extends SugarVisibility
{
    /**
     * {@inheritDoc}
     * Need to check where it's used.
     */
    public function addVisibilityWhere(&$query)
    {
        return $query;
    }

    /**
     * {@inheritDoc}
     */
    public function addVisibilityWhereQuery(SugarQuery $query)
    {
        $currentUser = $GLOBALS['current_user'];
        $module = $this->bean->module_name;
        $db = DBManagerFactory::getInstance();
        if (!method_exists($this->bean, 'getPublishedStatuses') ||
            $currentUser->isAdminForModule($module) ||
            $currentUser->isDeveloperForModule($module)
        ) {
            return $query;
        } else {
            /**
             * It's better to use
             *             $query->orWhere()
             *   ->equals('created_by', $currentUser->id)
             *   ->in('status', $statuses);
             * but it doesn't work.
             */
            $statuses = $this->bean->getPublishedStatuses();
            foreach ($statuses as $_ => $status) {
                $statuses[$_] = $db->quoted($status);
            }
            $statuses = implode(',', $statuses);
            $ow = new OwnerVisibility($this->bean, $this->params);
            $addon = '';
            $ow->addVisibilityWhere($addon);

            $addon = "({$addon} OR {$this->bean->table_name}.status IN ($statuses))";
            $query->whereRaw($addon);
            return $query;
        }
    }
}
