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
require_once('data/SugarACLStrategy.php');

/**
 * This class is used to restrict actions for Addressees module.
 * Addresses is a module for Event (Call, Meeting) potential invitees
 * that do not have email address bound to any known Contact, User, etc.
 * Module is used only by external sync and is not considered to be a regular Sugar Module.
 */
class SugarACLAddressees extends SugarACLStrategy
{
    /**
     * Only write operations.
     *
     * @param string $module
     * @param string $view
     * @param array $context
     * @return bool|void
     */
    public function checkAccess($module, $view, $context)
    {
        if ($view == 'team_security') {
            // Let the other modules decide
            return true;
        }

        if (!$this->isWriteOperation($view, $context)) {
            return true;
        } else {
            return false;
        }
    }
}
