<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

class Basic extends SugarBean{

	function Basic(){
		parent::SugarBean();
	}
	function get_summary_text()
	{
		return "$this->name";
	}
	
	function create_export_query($order_by, $where){
		return $this->create_new_list_query($order_by, $where, array(), array(), 0, '', false, $this, true);
	}
	
	/*
	 * FIXME for bug 20718,
	 * Because subpanels are not rendered using smarty and do not repsect the "currency_format" list def flag,
	 * we must convert currency values to the display format before dislplay only on subpanels.
	 * This code should be removed once all subpanels render properly using smarty rather than XTemplate.
	 */
	function get_list_view_data(){
		global $action;
		if (isset($this->currency_id) && ($action == 'DetailView' || $action == "SubPanelViewer"))
		{
			global $locale, $current_language, $current_user, $mod_strings, $app_list_strings, $sugar_config;
			$app_strings = return_application_language($current_language);
       		$params = array();
			
			$temp_array = $this->get_list_view_array();
			$params = array('currency_id' => $this->currency_id, 'convert' => true);
			foreach($temp_array as $field => $value)
			{
				$fieldLow = strToLower($field);
				if (!empty($this->field_defs[$fieldLow]) &&  $this->field_defs[$fieldLow]['type'] == 'currency')
				{
					$temp_array[$field] = currency_format_number($this->$fieldLow, $params);
				}
			}
			return $temp_array;
		}
		else 
		{
			return parent::get_list_view_data();
		}
		
	}
	
}