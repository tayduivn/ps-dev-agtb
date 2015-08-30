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

class TeamsViewTBA extends SugarView
{
    /**
     * @see SugarView::_getModuleTitleParams()
     */
    protected function _getModuleTitleParams($browserTitle = false)
    {
        global $mod_strings;

        return array(
            "<a href='index.php?module=Administration&action=index'>".$mod_strings['LBL_MODULE_NAME']."</a>",
            $mod_strings['LBL_TBA_CONFIGURATION']
        );
    }

    /**
     * @see SugarView::preDisplay()
     */
    public function preDisplay()
    {
        global $current_user;

        if (!$GLOBALS['current_user']->isAdminForModule('Users') &&
            !$GLOBALS['current_user']->isDeveloperForModule('Users')
        ) {
            sugar_die("Unauthorized access to administration.");
        }

        parent::preDisplay();
    }

    /**
     * @see SugarView::display()
     */
    public function display()
    {
        global $mod_strings;
        global $app_strings;
        global $current_user;

        $sugar_smarty = new Sugar_Smarty();
        $sugar_smarty->assign('APP', $app_strings);
        $sugar_smarty->assign('MOD', $mod_strings);
        $sugar_smarty->assign('actionsList', ACLAction::getUserActions($current_user->id));
        $sugar_smarty->assign('moduleTitle', $this->getModuleTitle(false));

        $tbaConfigurator = new TeamBasedACLConfigurator();
        $sugar_smarty->assign('config', $tbaConfigurator->getConfig());

        echo $sugar_smarty->fetch(SugarAutoLoader::existingCustomOne('modules/Teams/tpls/TBAConfiguration.tpl'));
    }
}
