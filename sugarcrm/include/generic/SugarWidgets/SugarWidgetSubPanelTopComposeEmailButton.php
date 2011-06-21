<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/**
 * SugarWidgetSubPanelTopCreateNoteButton
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

// $Id: SugarWidgetSubPanelTopComposeEmailButton.php 56851 2010-06-07 22:17:02Z jenny $

require_once('include/generic/SugarWidgets/SugarWidgetSubPanelTopButton.php');

class SugarWidgetSubPanelTopComposeEmailButton extends SugarWidgetSubPanelTopButton
{
	function display($defines)
	{
		global $app_strings,$current_user,$sugar_config,$beanList,$beanFiles;
		$title = $app_strings['LBL_COMPOSE_EMAIL_BUTTON_TITLE'];
		$accesskey = $app_strings['LBL_COMPOSE_EMAIL_BUTTON_KEY'];
		$value = $app_strings['LBL_COMPOSE_EMAIL_BUTTON_LABEL'];
		$parent_type = $defines['focus']->module_dir;
		$parent_id = $defines['focus']->id;
		
		//martin Bug 19660
		$userPref = $current_user->getPreference('email_link_type');
		$defaultPref = $sugar_config['email_default_client'];
		if($userPref != '') {
			$client = $userPref;
		} else {
			$client = $defaultPref;
		}		
		if($client != 'sugar') {
			$class = $beanList[$parent_type];
			require_once($beanFiles[$class]);
			$bean = new $class();
			$bean->retrieve($parent_id);
			// awu: Not all beans have emailAddress property, we must account for this
			if (isset($bean->emailAddress)){
				$to_addrs = $bean->emailAddress->getPrimaryAddress($bean);
				$button = "<input class='button' type='button'  value='$value'  id='".preg_replace('[ ]', '', strtolower($value))."_button'  name='".preg_replace('[ ]', '', $value)."_button'  accesskey='$accesskey' title='$title' onclick=\"location.href='mailto:$to_addrs';return false;\" />";
			}
			else{
				$button = "<input class='button' type='button'  value='$value'  id='".preg_replace('[ ]', '', strtolower($value))."_button'  name='".preg_replace('[ ]', '', $value)."_button'  accesskey='$accesskey' title='$title' onclick=\"location.href='mailto:';return false;\" />";
			}
		}else 
		{
			//Generate the compose package for the quick create options.
    		$composeData = "parent_id=$parent_id&parent_type=$parent_type";
            require_once('modules/Emails/EmailUI.php');
            $eUi = new EmailUI();
            $j_quickComposeOptions = $eUi->generateComposePackageForQuickCreateFromComposeUrl($composeData);
                    
            $button = "<input title='$title'  id='".preg_replace('[ ]', '', strtolower($value))."_button' accesskey='$accesskey' onclick='SUGAR.quickCompose.init($j_quickComposeOptions);' class='button' type='submit' name='".preg_replace('[ ]', '', $value)."_button' value='$value' />";
		}
		return $button;	 
	}
}
?>
