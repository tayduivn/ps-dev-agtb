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
 * This class is used to enforce ACLs on modules that are restricted to DPO (Data Privacy Officer) only.
 */
class SugarACLDpoErasure extends SugarACLStrategy
{
    /**
     * Only allow access to the erasure action for DPR module.
     * @param string $module
     * @param string $view
     * @param array $context
     * @return bool
     */
    public function checkAccess($module, $view, $context)
    {
        $user = $this->getCurrentUser($context);
        if (!$user) {
            return false;
        }


        if (!empty($context['action']) && $context['action'] == 'erase') {
            if ($module != 'DataPrivacy' || !$user->isAdminForModule($module)) {
                    return false;
            }
        }

        return true;
    }
}
