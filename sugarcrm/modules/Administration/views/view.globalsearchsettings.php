<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
//BEGIN SUGARCRM flav=pro ONLY
require_once('include/SugarSearchEngine/SugarSearchEngineFullIndexer.php');
require_once('include/SugarSearchEngine/SugarSearchEngineMetadataHelper.php');
//END SUGARCRM flav=pro ONLY

class AdministrationViewGlobalsearchsettings extends SugarView
{
 	/**
	 * @see SugarView::_getModuleTitleParams()
	 */
	protected function _getModuleTitleParams($browserTitle = false)
	{
	    global $mod_strings;

    	return array(
    	   "<a href='index.php?module=Administration&action=index'>".translate('LBL_MODULE_NAME','Administration')."</a>",
    	   $mod_strings['LBL_GLOBAL_SEARCH_SETTINGS']
    	   );
    }

    /**
	 * @see SugarView::_getModuleTab()
	 */
	protected function _getModuleTab()
    {
        return 'Administration';
    }

    /**
	 * @see SugarView::display()
	 */
	public function display()
    {
    	require_once('modules/Home/UnifiedSearchAdvanced.php');
		$usa = new UnifiedSearchAdvanced();
        global $mod_strings, $app_strings, $app_list_strings;

        $sugar_smarty = new Sugar_Smarty();
        $sugar_smarty->assign('APP', $app_strings);
        $sugar_smarty->assign('MOD', $mod_strings);
        $sugar_smarty->assign('moduleTitle', $this->getModuleTitle(false));

        $modules = $usa->retrieveEnabledAndDisabledModules();

        $sugar_smarty->assign('enabled_modules', json_encode($modules['enabled']));
        $sugar_smarty->assign('disabled_modules', json_encode($modules['disabled']));
        //BEGIN SUGARCRM flav=pro ONLY
        //FTS Options
        $schedulerID = SugarSearchEngineFullIndexer::isFTSIndexScheduled();

        if(isset($GLOBALS['sugar_config']['full_text_engine']) &&
           is_array($GLOBALS['sugar_config']['full_text_engine']))
        {
            $defaultEngine = SugarSearchEngineFactory::getFTSEngineNameFromConfig();
            $config = $GLOBALS['sugar_config']['full_text_engine'][$defaultEngine];
        }
        else
        {
            $defaultEngine = '';
            $config = array('host' => '','port' => '');
        }

        $justRequestedAScheduledIndex = !empty($_REQUEST['sched']) ? TRUE : FALSE;

        $scheduleDisableButton = empty($defaultEngine) ? 'disabled' : '';
        $schedulerID = SugarSearchEngineFullIndexer::isFTSIndexScheduled();
        $schedulerCompleted = SugarSearchEngineFullIndexer::isFTSIndexScheduleCompleted($schedulerID);
        $hide_fts_config = isset( $GLOBALS['sugar_config']['hide_full_text_engine_config'] ) ? $GLOBALS['sugar_config']['hide_full_text_engine_config'] : FALSE;

        $showSchedButton = ($defaultEngine != '' && $this->isFTSConnectionValid()) ? TRUE : FALSE;

        $sugar_smarty->assign("showSchedButton", $showSchedButton);
        $sugar_smarty->assign("hide_fts_config", $hide_fts_config);
        $sugar_smarty->assign("fts_type", get_select_options_with_id($app_list_strings['fts_type'], $defaultEngine));
        $sugar_smarty->assign("fts_host", empty($config['host']) ? 'localhost' : $config['host']);
        $sugar_smarty->assign("fts_port", empty($config['port']) ? '9200' : $config['port']);
        $sugar_smarty->assign("scheduleDisableButton", $scheduleDisableButton);
        $sugar_smarty->assign("fts_scheduled", !empty($schedulerID) && !$schedulerCompleted);
        $sugar_smarty->assign('justRequestedAScheduledIndex', $justRequestedAScheduledIndex);
        //End FTS
        //END SUGARCRM flav=pro ONLY
        echo $sugar_smarty->fetch(SugarAutoLoader::existingCustomOne('modules/Administration/templates/GlobalSearchSettings.tpl'));

    }

    protected function isFTSConnectionValid()
    {
        require_once('include/SugarSearchEngine/SugarSearchEngineFactory.php');
        $searchEngine = SugarSearchEngineFactory::getInstance();
        $result = $searchEngine->getServerStatus();
        if($result['valid']) {
            $this->setFTSUp();
            return TRUE;
        }
        else {
            return FALSE;
        }
    }

    /**
     * This method sets the full text search to available when a scheduled FTS Index occurs.  
     * An indexing can only occur with a valid connection
     * 
     * TODO: XXX Fix this to use admin settings not config options
     * @return bool
     */
    protected function setFTSUp() {
        require_once('include/SugarSearchEngine/SugarSearchEngineAbstractBase.php');
        require_once('modules/Administration/Administration.php');
        $cfg = new Configurator();
        $cfg->config['fts_disable_notification'] = false;
        $cfg->handleOverride();
        // set it up
        SugarSearchEngineAbstractBase::markSearchEngineStatus(false);
        $admin = BeanFactory::newBean('Administration');
        $admin->retrieveSettings(FALSE, TRUE);
        return TRUE;
    }
}
