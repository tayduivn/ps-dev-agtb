<?php
//FILE SUGARCRM flav!=sales ONLY
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
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

require_once('include/SugarSearchEngine/SugarSearchEngineFullIndexer.php');

class ViewConfigureFts extends SugarView
{
    /**
     * @see SugarView::_getModuleTitleParams()
     */
    protected function _getModuleTitleParams($browserTitle = false)
    {
        return array(
            "<a href='index.php?module=Administration&action=index'>" . translate('LBL_MODULE_NAME') . "</a>",
            translate('LBL_CONFIG_AJAX')
        );
    }

    /**
     * @see SugarView::preDisplay()
     */
    public function preDisplay()
    {
        global $current_user;

        if (!is_admin($current_user))
        {
            sugar_die("Unauthorized access to administration.");
        }
    }

    /**
     * @see SugarView::display()
     */
    public function display()
    {
        global $app_list_strings;

        $mod_strings = return_module_language($GLOBALS['current_language'], $this->module) ;
        $ftsScheduleEnabledText = $mod_strings['LBL_FTS_SCHED_ENABLED'];



        if(isset($GLOBALS['sugar_config']['full_text_engine']))
        {
            $engines = array_keys($GLOBALS['sugar_config']['full_text_engine']);
            $defaultEngine = $engines[0];
            $config = $GLOBALS['sugar_config']['full_text_engine'][$defaultEngine];
        }
        else
        {
            $defaultEngine = '';
            $config = array('host' => '','port' => '');
        }

        $scheduleDisableButton = empty($defaultEngine) ? 'disabled' : '';
        $schedulerID = SugarSearchEngineFullIndexer::isFTSIndexScheduled();
        $schedulerCompleted = SugarSearchEngineFullIndexer::isFTSIndexScheduleCompleted($schedulerID);

        $ftsScheduleEnabledText = string_format($ftsScheduleEnabledText, array($schedulerID));
        $this->ss->assign("fts_type", get_select_options_with_id($app_list_strings['fts_type'], $defaultEngine));
        $this->ss->assign("fts_host", $config['host']);
        $this->ss->assign("fts_port", $config['port']);
        $this->ss->assign("scheduleDisableButton", $scheduleDisableButton);
        $this->ss->assign("fts_scheduled", !empty($schedulerID) && !$schedulerCompleted);
        $this->ss->assign('title',$this->getModuleTitle(false));
        $this->ss->assign('ftsScheduleEnabledText',$ftsScheduleEnabledText);

        echo $this->ss->fetch('modules/Administration/templates/ConfigureFTS.tpl');
    }


}
