<?php
// FILE SUGARCRM flav=ent ONLY

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
require_once 'modules/ModuleBuilder/views/view.dropdown.php';

/**
 * Role based dropdown filter editor
 */
class ViewRoleDropdownFilter extends ViewDropdown
{
    protected $template = 'modules/ModuleBuilder/tpls/MBModule/roledropdownfilter.tpl';

    protected $defaultParams = array(
        'refreshTree' => false,
        'package_name' => 'studio',
        'view_package' => 'studio',
        'view_module' => '',
        'dropdown_lang' => '',
        'dropdown_name' => '',
        'dropdown_role' => 'default',
        'field' => '',
        'new' => false
    );

    /**
     * @param $params
     * @return Sugar_Smarty
     */
    public function generateSmarty($params)
    {
        $smarty = parent::generateSmarty($params);
        $smarty->assign('dropdown_role', $params['dropdown_role']);
        $smarty->assign('role_options', $this->getRoleOptions($params));
        return $smarty;
    }

    /**
     * @param $params
     * @return mixed
     * @throws Exception
     */
    protected function getRoleOptions($params)
    {
        $parser = new ParserRoleDropDownFilter();
        $options = $parser->getOne($params['dropdown_role'], $params['dropdown_name']);
        if (!$options) {
            $options = $this->getDefaultRoleOptions($params);
        }
        return $options;
    }

    /**
     * @param $params
     * @return array
     */
    protected function getDefaultRoleOptions($params)
    {
        $app_list_strings = return_app_list_strings_language($params['dropdown_lang']);
        return array_fill_keys(array_keys($app_list_strings[$params['dropdown_name']]), true);
    }
}
