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
 /*********************************************************************************
 * $Id: index.php 45763 2009-04-01 19:16:18Z majed $
 ********************************************************************************/








global $theme, $current_user;




global $mod_strings;
global $app_list_strings;
global $app_strings;
global $current_user, $focus;

echo get_module_title($mod_strings['LBL_MODULE_ID'], $mod_strings['LBL_MODULE_TITLE'], true); 

if(!empty($_REQUEST['record']) && empty($_REQUEST['edit'])){
	$iFrame = new iFrame();
	$iFrame->retrieve($_REQUEST['record']);
	$xtpl = new XTemplate('modules/iFrames/DetailView.html');
	$xtpl_data = $iFrame->get_xtemplate_data();
	$xtpl_data['URL'] = add_http($xtpl_data['URL']);
	$xtpl->assign('IFRAME', $xtpl_data);
	$xtpl->parse('main');
	$xtpl->out('main');
}
else
{
	if(!empty($_REQUEST['edit']))
	{
		$iFrame = new iFrame();
		$xtpl = new XTemplate('modules/iFrames/EditView.html');	

		if(!empty($_REQUEST['record']))
		{
			$iFrame->retrieve($_REQUEST['record']);
		}

		$xtpl_data = $iFrame->get_xtemplate_data();
		
		$xtpl->assign("MOD", $mod_strings);
		$xtpl->assign("APP", $app_strings);
		
		if (isset($_REQUEST['return_module']))
		{
			 $xtpl->assign("RETURN_MODULE", $_REQUEST['return_module']);
		}
		else
		{
			$xtpl->assign("RETURN_MODULE", 'iFrames');
		}
		
		if (isset($_REQUEST['return_action']))
		{
			 $xtpl->assign("RETURN_ACTION", $_REQUEST['return_action']);
		}
		else
		{
			 $xtpl->assign("RETURN_ACTION",'index');
		}
		
		if (isset($_REQUEST['return_id'])) 
		{
			$xtpl->assign("RETURN_ID", $_REQUEST['return_id']);
		}
		else if(!empty($_REQUEST['record']))
		{
			$xtpl->assign("RETURN_ID", $_REQUEST['record']);
		}
		
		if(!empty($xtpl_data['STATUS']) && $xtpl_data['STATUS'] > 0)
		{
			$xtpl_data['STATUS_CHECKED'] = 'checked';	
		}

		$xtpl->assign('IFRAME', $xtpl_data);
		$xtpl->parse('main');
		$xtpl->out('main');

		
		$javascript = new javascript();
		$javascript->setFormName('EditView');
		$javascript->setSugarBean($iFrame);
		$javascript->addAllFields('');
		echo $javascript->getScript();

	}
	else if(!empty($_REQUEST['delete']) || !empty($_REQUEST['listview']) || (empty($_REQUEST['record']) && empty($_REQUEST['edit'])) )
	{
		$button_title = $app_strings['LBL_NEW_BUTTON_LABEL'];
			
		$sugar_config['disable_export'] = true;
		$iFrame = new iFrame();
		$ListView = new ListView();
		$where = '';
			
		if(!is_admin($current_user))
		{
			$where = "created_by='$current_user->id'";
		}

		$ListView->initNewXTemplate( 'modules/iFrames/ListView.html',$mod_strings);
		$ListView->setHeaderTitle($mod_strings['LBL_LIST_FORM_TITLE']. '&nbsp;' );
		$ListView->setQuery($where, "", "name", "IFRAME");
		$ListView->processListView($iFrame, "main", "IFRAME");
		
		//special case redirect for refreshing shorcut listed sites that might have been deleted
		if(!empty($_REQUEST['delete'])) header("Location: index.php?module=iFrames&action=index");
	}
	else
	{
		$iFrame = new iFrame();
		$xtpl = new XTemplate('modules/iFrames/DetailView.html');
		$xtpl_data = array();
		$xtpl_data['URL'] = translate('DEFAULT_URL', 'iFrames');
		$xtpl->assign('IFRAME', $xtpl_data);
		$xtpl->parse('main');
		$xtpl->out('main');
	}
}



?>
