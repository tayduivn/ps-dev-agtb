<?php
/*********************************************************************************
 * SugarCRM is a customer relationship management program developed by
 * SugarCRM, Inc. Copyright (C) 2004 - 2007 SugarCRM Inc.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 *
 * You can contact SugarCRM, Inc. headquarters at 10050 North Wolfe Road,
 * SW2-130, Cupertino, CA 95014, USA. or at email address contact@sugarcrm.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * SugarCRM" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by SugarCRM".
 * *******************************************************************************/
/*
 * Created on Apr 13, 2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
require_once('custom/include/sugarmobile/ui.php');


class ViewDetail_Mobile extends SugarView{
	var $type ='detail';
	var $read_only_modules = array('Employees');
	
 	function ViewDetail_Mobile(){
 		$this->options['show_all'] = false;
 		parent::SugarView();
 	}

 	function display(){

		if(empty($this->bean->id)){
			global $app_strings;
			sugar_die($app_strings['ERROR_NO_RECORD']);
		}

		global $app_strings;
		global $mod_strings;

		require_once('custom/include/sugarmobile/metadata/fields.php');

		if(!array_key_exists($this->module, $detail_view_mobile)){
			die("The metadata does not exist for this module.");
		}

		$dv = new SUI_page($this->module.' Detail');
		$dv->charset = 'UTF-8';

		global $app_list_strings;
		
		$mod_lbl = $this->module;
		if(isset($app_list_strings['moduleList'][$this->module])){
			$mod_lbl = $app_list_strings['moduleList'][$this->module];
		}
		$dv->add_link('Back to '.$mod_lbl,'index.php?module='.$this->module.'&action=List_Mobile');
		if(!in_array($this->module, $this->read_only_modules)){
			$dv->add_newline();
			$dv->add_link("Edit",'index.php?module='.$this->module.'&record='.$this->bean->id.'&action=edit_mobile');
		}
		
		$street = '';
		foreach ($detail_view_mobile[$this->bean->module_dir] as $value2) {
			$lbl = $value2;
			if (isset($mod_strings[$this->bean->field_name_map[$value2]['vname']])) {
				$lbl = $mod_strings[$this->bean->field_name_map[$value2]['vname']];
			} else if(isset($app_strings[$this->bean->field_name_map[$value2]['vname']])){
				$lbl = $app_strings[$this->bean->field_name_map[$value2]['vname']];
			}
			
			$dv->add_text($lbl . " ",0);
			if ($value2 == 'parent_type')  {
				$this_parent_type = $this->bean->$value2;
			}
			if ($value2 == 'parent_id')  {
				$this_parent_id = $this->bean->$value2;
			}
			
			if (preg_match("/.*phone.*/",$value2) ) {
				$dv->add_phone($this->bean->$value2);
		 	} else if ($value2 != 'parent_type' && $value2 != 'parent_id'){
				$dv->add_text($this->bean->$value2);
			}
			if (preg_match("/.*address_street.*/",$value2)) {
				$street = $this->bean->$value2;
			}
			if (preg_match("/.*address_city.*/",$value2)) {
				$city = $this->bean->$value2;
			}
			if (preg_match("/.address_state.*/",$value2)) {
				$state = $this->bean->$value2;
			}
			if (preg_match("/.address_postalcode.*/",$value2)) {
				$postal_code = $this->bean->$value2;
			}
			if (isset($label) && isset($value)) {
				$label->set_br(0);
				$dv->add_text($label);
				$dv->add_text($value);
			}
		}
		if(isset($this_parent_type) && isset($this_parent_id)) {
			$dv->add_link(' this record','index.php?module='.$this_parent_type.'&record='.$this_parent_id.'&action=detail_mobile');
		}
		
		$dv->add_newline();
		
		if (isset($street) && isset($city) && isset($state) && isset($postal_code)) {
			$dv->add_link("Map it!",'http://maps.google.com/m/search?source=mobileproducts&q='.$street.'+'.$city.'+'.$state.'+'.$postal_code.'&site=maps');
		}
		
		if ($this->module == "Contacts") {
			$dv->add_link("+vCard","vCard.php?contact_id=".$this->bean->id."&module=Contact");
		}
		
		if ($this->module == "Leads") {
			$dv->add_link("+vCard","vCard.php?contact_id=".$this->bean->id."&module=Lead");
		}
		
		//Now for Subpanels?
		
		require_once('custom/include/sugarmobile/metadata/subpanels.php');
		
		global $moduleList;
		global $beanFiles;
		global $beanList;
		global $app_list_strings;
		
		if (isset($subpanels_mobile[$this->module])) {
			foreach ($subpanels_mobile[$this->module] as $sub_module) {
				$dv->add_text('<hr>');
				$parent_link_string = '';
				if ($sub_module == 'Notes' || $sub_module == 'Calls' || $sub_module == 'Meetings' || $sub_module == 'Tasks') {
					$parent_link_string = 'pop_parent_id='.$this->bean->id.'&pop_parent_type='.$this->module;
					$dv->add_link('+','index.php?module='.$sub_module.'&action=Edit_Mobile&do_new=1&'.$parent_link_string,0);
				}
				$dv->add_text($app_list_strings['moduleList'][$sub_module]);
				require_once($beanFiles[$beanList[$sub_module]]);
				$sub_obj = new $beanList[$sub_module]();
				$the_table = $sub_obj->table_name;
				if (isset($this->bean->$the_table)) {
					$sub_data = $this->bean->get_related_list($sub_obj,$sub_obj->table_name,'','',0,-1,-1,0,'');
					foreach($sub_data['list'] as $sub_datum) {
						if(isset($seed->field_name_map['name']['source']) && $seed->field_name_map['name']['source'] == 'non-db') {
							$dv->add_link($sub_datum->first_name . " " . $sub_datum->last_name, 'index.php?module=' . $sub_obj->module_dir . '&action=detail_mobile&record=' . $sub_datum->id);
						} else {
							$dv->add_link($sub_datum->name,'index.php?module=' . $sub_obj->module_dir . '&action=detail_mobile&record=' . $sub_datum->id);
						}
					}
				}
			}
		}

		echo $dv->render();
 	}
}
