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
        if (!$GLOBALS['current_user']->isAdminForModule('Users')) {
            ACLController::displayNoAccess(true);
            sugar_cleanup(true);
        }

        parent::preDisplay();
    }

    /**
     * @see SugarView::display()
     */
    public function display()
    {
        $sugar_smarty = new Sugar_Smarty();
        $sugar_smarty->assign('APP', $GLOBALS['app_strings']);
        $sugar_smarty->assign('MOD', $GLOBALS['mod_strings']);
        $sugar_smarty->assign('APP_LIST', $GLOBALS['app_list_strings']);
        $sugar_smarty->assign('actionsList', $this->_getUserActionsList());
        $sugar_smarty->assign('moduleTitle', $this->getModuleTitle(false));

        $tbaConfigurator = new TeamBasedACLConfigurator();
        $sugar_smarty->assign('config', $tbaConfigurator->getConfig());

        echo $sugar_smarty->fetch(SugarAutoLoader::existingCustomOne('modules/Teams/tpls/TBAConfiguration.tpl'));
    }

    /**
     * Get user actions list filtered by default TbACLs disabled_modules parameter.
     */
    private function _getUserActionsList()
    {
        $defaultTBAConfig = TeamBasedACLConfigurator::getDefaultConfig();

        $actionsList = ACLAction::getUserActions($GLOBALS['current_user']->id);

        // Skipping modules that have 'hidden_to_role_assignment' property or not implement TBA
        foreach ($actionsList as $name => $category) {
            if (
                (!empty($GLOBALS['dictionary'][$name]['hidden_to_role_assignment']) &&
                    $GLOBALS['dictionary'][$name]['hidden_to_role_assignment']) ||
                !TeamBasedACLConfigurator::implementsTBA($name)
            ) {
                unset($actionsList[$name]);
            }
        }

        return array_diff(array_keys($actionsList), $defaultTBAConfig['disabled_modules']);
    }
}
