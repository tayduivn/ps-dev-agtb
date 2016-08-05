<?php
//FILE SUGARCRM flav=ent ONLY

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
require_once 'data/SugarACLStrategy.php';

use Sugarcrm\Sugarcrm\ProcessManager\Registry;

/**
 * Class SugarACLLockedFields
 *
 * To check the edit access for a field based on whether it's a locked field
 */
class SugarACLLockedFields extends SugarACLStrategy
{
    /**
     * Check access to the locked field
     * @param string $module
     * @param string $view
     * @param array $context
     * @return bool true if not a locked field, false otherwise
     */
    public function checkAccess($module, $view, $context)
    {
        // only need to check field acl
        if ($view != 'field') {
            return true;
        }

        // nothing to check
        if (empty($context) || empty($context['field']) || empty($context['action'])) {
            return true;
        }

        // only need to check write type action, but not delete
        $action = self::fixUpActionName($context['action']);
        $writes = array('edit', 'import', 'massupdate');
        if (!in_array($action, $writes)) {
            return true;
        }

        // If the skip flag is set, it will be true, so check if it is not true
        // to determine if we need to enforce locked field checking
        if (Registry\Registry::getInstance()->get('skip_locked_field_checks') === true) {
            return true;
        }

        // to get bean object
        $bean = static::loadBean($module, $context);
        if (empty($bean)) {
            return true;
        }

        // check if this field is a locked field
        $lockedFields = $bean->getLockedFields();
        if (!empty($lockedFields) && in_array($context['field'], $lockedFields)) {
            // it is a locked field
            return false;
        }

        return true;
    }

    /**
     * Load bean from context
     * @static
     * @param string $module
     * @param array $context
     * @return SugarBean
     */
    public static function loadBean($module, $context = array())
    {
        $bean = null;
        if (isset($context['bean']) && $context['bean'] instanceof SugarBean
            && $context['bean']->module_dir == $module) {
            $bean = $context['bean'];
        }
        return $bean;
    }
}
