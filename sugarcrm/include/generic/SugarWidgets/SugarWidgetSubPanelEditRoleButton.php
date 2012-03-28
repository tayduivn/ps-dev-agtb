<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/**
 * SugarWidgetSubPanelEditRoleButton
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

// $Id: SugarWidgetSubPanelEditRoleButton.php 40541 2008-10-14 17:42:09Z jmertic $



class SugarWidgetSubPanelEditRoleButton extends SugarWidgetField
{
	function displayHeaderCell(&$layout_def)
	{
		return '&nbsp;';
	}

	function displayList(&$layout_def)
	{
		global $app_strings;
        global $subpanel_item_count;
        $unique_id = $layout_def['subpanel_id']."_edit_".$subpanel_item_count; //bug 51512
	
		$href = 'index.php?module=' . $layout_def['module']
			. '&action=' . 'ContactOpportunityRelationshipEdit'
			. '&record=' . $layout_def['fields']['OPPORTUNITY_ROLE_ID']
			. '&return_module=' . $_REQUEST['module']
			. '&return_action=' . 'DetailView'
			. '&return_id=' . $_REQUEST['record'];
			
	//based on listview since that lets you select records
	if($layout_def['ListView']){
		return '<a href="' . $href . '"'
            . "id=\"$unique_id\""
			. 'class="listViewTdToolsS1">' . $app_strings['LNK_EDIT'] .'</a>&nbsp;';
	}else{
		return '';
	}
	}
}

?>