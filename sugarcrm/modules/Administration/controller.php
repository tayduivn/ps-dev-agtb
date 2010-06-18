<?php
//FILE SUGARCRM flav!=sales ONLY
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
/*********************************************************************************
 * $Id: SaveTabs.php 54176 2010-02-01 23:07:34Z dwheeler $
 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

class AdministrationController extends SugarController
{
    public function action_savetabs()
    {
        require_once('include/SubPanel/SubPanelDefinitions.php');
        require_once('modules/MySettings/TabController.php');
        
        global $current_user;
        
        if (!is_admin($current_user)) sugar_die("Unauthorized access to administration.");
        
        // handle the tabs listing
        $toDecode = html_entity_decode  ($_REQUEST['enabled_tabs'], ENT_QUOTES);
        $enabled_tabs = json_decode($toDecode);
        $tabs = new TabController();
        $tabs->set_system_tabs($enabled_tabs);
        $tabs->set_users_can_edit(isset($_REQUEST['user_edit_tabs']) && $_REQUEST['user_edit_tabs'] == 1);
        
        // handle the subpanels
        if(isset($_REQUEST['disabled_tabs'])) {
            $disabledTabs = json_decode(html_entity_decode($_REQUEST['disabled_tabs'], ENT_QUOTES));
            $disabledTabsKeyArray = TabController::get_key_array($disabledTabs);
            SubPanelDefinitions::set_hidden_subpanels($disabledTabsKeyArray);
        }
        
        header("Location: index.php?module=Administration&action=ConfigureTabs");
    }
}
