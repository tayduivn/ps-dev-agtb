<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/**
 * SugarWidgetSubPanelIcon
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

// $Id: SugarWidgetSubPanelIcon.php 49142 2009-06-30 15:50:50Z jmertic $

require_once('include/generic/SugarWidgets/SugarWidgetField.php');

class SugarWidgetSubPanelIcon extends SugarWidgetField
{
	function displayHeaderCell(&$layout_def)
	{
		return '&nbsp;';
	}

	function displayList(&$layout_def)
	{
		global $app_strings;
		
		global $app_list_strings;

		if(isset($layout_def['varname']))
		{
			$key = strtoupper($layout_def['varname']);
		}
		else
		{
			$key = $this->_get_column_alias($layout_def);
			$key = strtoupper($key);
		}
//add module image
		//add module image
		if(!empty($layout_def['target_module_key'])) { 
			if (!empty($layout_def['fields'][strtoupper($layout_def['target_module_key'])])) {
				$module=$layout_def['fields'][strtoupper($layout_def['target_module_key'])];
			}	
		}		
        
        if (empty($module)) {
			if(empty($layout_def['target_module']))
			{
				$module = $layout_def['module'];
			}
		else
			{
				$module = $layout_def['target_module'];
			}
		}
		$action = 'DetailView';
		if(empty($layout_def['target_record_key']))
		{
			$record = $layout_def['fields']['ID'];
		}
		else
		{
			$record_key = strtoupper($layout_def['target_record_key']);
			$record = $layout_def['fields'][$record_key];
		}
		$icon_img_html = SugarThemeRegistry::current()->getImage( $module . '', 'border="0" alt="' . $app_list_strings['moduleList'][$module] . '"');
		if (!empty($layout_def['attachment_image_only']) && $layout_def['attachment_image_only'] == true) {
			$ret="";
		}else { 
			$ret= '<a href="index.php?module=' . $module
				. '&action=' . $action
				. '&record=' . $record
				. '" >' . "$icon_img_html</a>";
		}
//if requested, add attachement icon.
		if(!empty($layout_def['image2']) && !empty($layout_def['image2_url_field'])){
			if (is_array($layout_def['image2_url_field'])) {
				$filepath="";
				//Generate file url.
				if (!empty($layout_def['fields'][strtoupper($layout_def['image2_url_field']['id_field'])])
				and !empty($layout_def['fields'][strtoupper($layout_def['image2_url_field']['filename_field'])]) ){
					
					$key=$layout_def['fields'][strtoupper($layout_def['image2_url_field']['id_field'])];
					$file=$layout_def['fields'][strtoupper($layout_def['image2_url_field']['filename_field'])];
					//$filepath=UploadFile :: get_url(from_html($file), $key);	
					$filepath="index.php?entryPoint=download&id=".$key."&type=".$layout_def['module'];
				}
			}
			else {
				if (!empty($layout_def['fields'][strtoupper($layout_def['image2_url_field'])])) {
					$filepath="index.php?entryPoint=download&id=".$layout_def['fields']['ID']."&type=".$layout_def['module'];						
				 }
			}
			$icon_img_html = SugarThemeRegistry::current()->getImage( $layout_def['image2'] . '', 'border="0" alt="' . $layout_def['image2'] . '"');
			$ret.= (empty($filepath)) ? '' : '<a href="' . $filepath. '" >' . "$icon_img_html</a>";	
		}
		// now handle attachments for Emails
		else if(!empty($layout_def['module']) && $layout_def['module'] == 'Emails' && !empty($layout_def['fields']['ATTACHMENT_IMAGE'])) {			
			$ret.= $layout_def['fields']['ATTACHMENT_IMAGE'];	
		}
		return $ret;
	}
}
?>