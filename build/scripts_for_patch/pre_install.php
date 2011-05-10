<?php
if(!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
/**
 * This script executes after the files are copied during the install.
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
 *
 * $Id: pre_install.php 49973 2009-08-06 06:03:52Z xye $
 */
require_once(clean_path($unzip_dir.'/scripts/upgrade_utils.php'));

function upgrade_config_pwd(){
    require_once('modules/Administration/Administration.php');
    $focus = new Administration();
    $focus->retrieveSettings(false, true);
    if(isset($focus->settings['ldap_admin_password']))
    {
        $pwd = $focus->encrpyt_before_save($focus->settings['ldap_admin_password']);
        $focus->saveSetting('ldap', 'admin_password', $pwd);
    }
    if(isset($focus->settings['proxy_password']))
    {
        $pwd = $focus->encrpyt_before_save($focus->settings['proxy_password']);
        $focus->saveSetting('proxy', 'password', $pwd);
    }
}

function pre_install() {
	global $sugar_version;
    if($sugar_version < '5.5.0') {
        _logThis("Begin Upgrade passwords in table config", $path);
        upgrade_config_pwd();
        _logThis("End Upgrade passwords in table config", $path);
        
// BEGIN SUGARCRM flav=com ONLY 
        _logThis("Begin remove ACL actions for Trackers", $path);
        include('include/modules.php');        
        if(isset($beanFiles['Tracker']) && file_exists($beanFiles['Tracker']))
        {
            require_once('modules/ACLActions/ACLAction.php');
            ACLAction::removeActions('Trackers', 'Tracker');
        }   
        _logThis("End remove ACL actions for Trackers", $path);
// END SUGARCRM flav=com ONLY 
    
    }
	return true;
}
?>