<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

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


//FILE SUGARCRM flav=ent ONLY

class ViewPortalPreview extends SugarView 
{
	function ViewPortalPreview()
	{
	    $this->options['show_title'] = false;
		$this->options['show_header'] = false;
        $this->options['show_footer'] = false;
        $this->options['show_javascript'] = false;
        $GLOBALS['log']->debug('in ViewPortalStyleView');
	}
	
	/**
	 * @see SugarView::_getModuleTitleParams()
	 */
	protected function _getModuleTitleParams($browserTitle = false)
	{
	    global $mod_strings;
	    
    	return array(
    	   translate('LBL_MODULE_NAME','Administration'),
    	   ModuleBuilderController::getModuleTitle(),
    	   );
    }
	
	// DO NOT REMOVE - overrides parent ViewEdit preDisplay() which attempts to load a bean for a non-existent module
	function preDisplay() 
	{
	}

	function display() 
	{
        $smarty = new Sugar_Smarty();
        $smarty->assign('mod', $GLOBALS['mod_strings']);
        $smarty->assign('app', $GLOBALS['app_strings']);
        $smarty->assign('welcome', $GLOBALS['mod_strings']['LBL_SP_UPLOADSTYLE']);
        $smarty->assign('useCustomFile', file_exists('custom/portal/custom/style.css'));
        $smarty->assign('langHeader', get_language_header());
        echo $smarty->fetch('modules/ModuleBuilder/tpls/portalpreview.tpl');
	}
}