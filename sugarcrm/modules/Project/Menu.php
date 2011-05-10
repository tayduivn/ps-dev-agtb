<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/**
 * Side-bar menu for Project
 *
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2005 SugarCRM, Inc.; All Rights Reserved.
 */

// $Id: Menu.php 51719 2009-10-22 17:18:00Z mitani $

global $current_user;
global $mod_strings, $app_strings;
$module_menu = array();

// Each index of module_menu must be an array of:
// the link url, display text for the link, and the icon name.

// Create Project
if(ACLController::checkAccess('Project', 'edit', true)) {
    $module_menu[] = array(
        'index.php?module=Project&action=EditView&return_module=Project&return_action=DetailView',
        isset($mod_strings['LNK_NEW_PROJECT']) ? $mod_strings['LNK_NEW_PROJECT'] : '',
        'CreateProject'
    );
}

//BEGIN SUGARCRM flav=pro ONLY
// Create Project Template
if(ACLController::checkAccess('Project', 'edit', true)) {
    $module_menu[] = array(
        'index.php?module=Project&action=ProjectTemplatesEditView&return_module=Project&return_action=ProjectTemplatesDetailView',
        isset($mod_strings['LNK_NEW_PROJECT_TEMPLATES']) ? $mod_strings['LNK_NEW_PROJECT_TEMPLATES'] : '',
        'CreateProjectTemplate'
    );
}
//END SUGARCRM flav=pro ONLY
	
// Project List
if(ACLController::checkAccess('Project', 'list', true)) {
    $module_menu[] = array(
        'index.php?module=Project&action=index',
        isset($mod_strings['LNK_PROJECT_LIST']) ? $mod_strings['LNK_PROJECT_LIST'] : '',
        'Project'
    );
}
	
//BEGIN SUGARCRM flav=pro ONLY
// Project Templates
if(ACLController::checkAccess('Project', 'list', true)) {
    $module_menu[] = array(
        'index.php?module=Project&action=ProjectTemplatesListView',
        isset($mod_strings['LNK_PROJECT_TEMPLATES_LIST']) ? $mod_strings['LNK_PROJECT_TEMPLATES_LIST'] : '',
        'ProjectTemplate'
    );
}
//END SUGARCRM flav=pro ONLY
	
// Project Tasks
if(ACLController::checkAccess('ProjectTask', 'list', true)) {
    $module_menu[] = array(
        'index.php?module=ProjectTask&action=index',
        isset($mod_strings['LNK_PROJECT_TASK_LIST']) ? $mod_strings['LNK_PROJECT_TASK_LIST'] : '',
        'ProjectTask'
    );
}
	
//BEGIN SUGARCRM flav=pro ONLY
if(ACLController::checkAccess('Project', 'list', true)) {
    $module_menu[] = array(
        "index.php?module=Project&action=Dashboard&return_module=Project&return_action=DetailView",
        isset($mod_strings['LNK_PROJECT_DASHBOARD']) ? $mod_strings['LNK_PROJECT_DASHBOARD'] : '',
        'Project'
    );
}
//END SUGARCRM flav=pro ONLY

?>
