<?php
 if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/**
 * Add modules to tabs for CE->PRO
 * TODO: irrelevant for 7?
 */
class SugarUpgradeAddModulesToCE extends UpgradeScript
{
    public $order = 4000;
    public $type = self::UPGRADE_DB;

    public function run()
    {
        if(!($this->from_flavor == 'ce' && $this->toFlavor('pro'))) return;

        //check to see if there are any new files that need to be added to systems tab
        //retrieve old modules list
        $this->log('check to see if new modules exist');
        if(empty($this->state['old_modules'])) {
            $this->log('No old modules info, skipping it');
            return;
        } else {
            $oldModuleList = $this->state['old_modules'];
        }

        $newModuleList = array();
        include('include/modules.php');
        $newModuleList = $moduleList;

        //include tab controller
        require_once('modules/MySettings/TabController.php');
        $newTB = new TabController();

        //make sure new modules list has a key we can reference directly
        $newModuleList = $newTB->get_key_array($newModuleList);
        $oldModuleList = $newTB->get_key_array($oldModuleList);

        //iterate through list and remove commonalities to get new modules
        foreach ($newModuleList as $remove_mod){
            if(in_array($remove_mod, $oldModuleList)){
                unset($newModuleList[$remove_mod]);
            }
        }

        $must_have_modules= array(
                'Activities'=>'Activities',
                'Calendar'=>'Calendar',
                'Reports' => 'Reports',
                'Quotes' => 'Quotes',
                'Products' => 'Products',
                'Forecasts' => 'Forecasts',
                'Contracts' => 'Contracts',
                'KBDocuments' => 'KBDocuments'
        );
        $newModuleList = array_merge($newModuleList,$must_have_modules);

        //new modules list now has left over modules which are new to this install, so lets add them to the system tabs
        $this->log('new modules to add are '.var_export($newModuleList,true));

        //grab the existing system tabs
        $tabs = $newTB->get_system_tabs();

        //add the new tabs to the array
        foreach($newModuleList as $nm ){
            $tabs[$nm] = $nm;
        }

        //now assign the modules to system tabs
        $newTB->set_system_tabs($tabs);
        $this->log('module tabs updated');

    }
}
