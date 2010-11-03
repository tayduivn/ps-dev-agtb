<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
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
 * by SugarCRM are Copyright (C) 2004-2008 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************

 * Description: This file is used to override the default Meta-data EditView behavior
 * to provide customization specific to the Contacts module.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/
require_once('include/json_config.php');
require_once('include/MVC/View/views/view.edit.php');
class ContactsViewEdit extends ViewEdit {
   
    function ContactsViewEdit(){
	parent::ViewEdit();
	$this->useForSubpanel = true;
	$this->useModuleQuickCreateTemplate = true;
    }
 	
    /**
     * display
     * 
     * We are overridding the display method to manipulate the sectionPanels.
     * If portal is not enabled then don't show the Portal Information panel.
     */
    function display() {
	
	// START SUGARINTERNAL CUSTOMIZATION
	global $mod_strings, $current_user;
	$this->ev->th->clearCache($this->module, 'EditView.tpl');

	// BEGIN: Determine whether or not we display the DCE fields


	// Load up all the references to the panels based on the labels
	$e = $this->ev->defs['panels'];
	$panelArray = array();
	foreach ($e as $panel_label => $panel_data) {
	    if (isset($mod_strings[strtoupper($panel_label)])) {
		$panelArray[$mod_strings[strtoupper($panel_label)]] = $panel_label;
	    }
	}
	if (!empty($_REQUEST['contact_name']) && !empty($_REQUEST['contact_id'])
	    && $this->ev->fieldDefs['report_to_name']['value'] == ''
	    && $this->ev->fieldDefs['reports_to_id']['value'] == '') { 
            $this->ev->fieldDefs['report_to_name']['value'] = $_REQUEST['contact_name'];
            $this->ev->fieldDefs['reports_to_id']['value'] = $_REQUEST['contact_id'];
        }
        $admin = new Administration();
	$admin->retrieveSettings();
	if (!is_admin($GLOBALS['current_user'])
	    && !$GLOBALS['current_user']->check_role_membership('DCE Field Access')
	    && $current_user->department != 'Customer Support') {
	    unset($this->ev->defs['panels'][$panelArray['DCE Information']]);
	    //foreach ($this->ev->defs['panels'] as $panel_index => $panel_rows) {
	    //if ($panel_index == $panelArray['DCE Information']) {
	    //	    unset($this->ev->defs['panels'][$panel_index]);
	    //	}
	    //}
	}


	// END SUGARINTERNAL CUSTOMIZATION

	if(empty($admin->settings['portal_on'])
	   || !$admin->settings['portal_on']) {
	    unset($this->ev->defs['panels'][$panelArray['Portal Information']]);
	} else {
	    echo '<script src="modules/Contacts/Contact.js"></script>';
	    echo '<script language="javascript">';
	    echo 'addToValidateComparison(\'EditView\', \'portal_password\', \'varchar\', false, SUGAR.language.get(\'app_strings\', \'ERR_SQS_NO_MATCH_FIELD\') + SUGAR.language.get(\'Contacts\', \'LBL_PORTAL_PASSWORD\'), \'portal_password1\');';	
	    echo 'addToValidateVerified(\'EditView\', \'portal_name_verified\', \'bool\', true, SUGAR.language.get(\'app_strings\', \'ERR_EXISTING_PORTAL_USERNAME\'));';
	    echo 'YAHOO.util.Event.on(\'portal_name\', \'blur\', validatePortalName);';
	    echo 'YAHOO.util.Event.on(\'portal_name\', \'keydown\', handleKeyDown);';
	    echo '</script>';
	}
	// BEGIN SUGARINTERNAL CUSTOMIZATION
	// You need this line to catch the panel changes
        $this->ev->process();
	// END SUGARINTERNAL CUSTOMIZATION
	echo $this->ev->display($this->showTitle);
    }
 	
}
