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
 * Class for common ModuleBuilder utilities
 */
class MBHelper
{
    /**
     * Builds id => name role dictionary
     *
     * @return array
     */
    public static function getACLRoleDictionary()
    {
        $dict = array(
            'default' => translate('LBL_DEFAULT')
        );
        foreach (ACLRole::getAllRoles() as $role) {
            $dict[$role->id] = $role->name;
        }
        return $dict;
    }
}