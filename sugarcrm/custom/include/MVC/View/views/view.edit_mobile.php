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


class ViewEdit_Mobile extends SugarView{
	var $type ='detail';
 	function ViewEdit_Mobile(){
 		$this->options['show_all'] = false;
 		parent::SugarView();
 	}
 	
 	function display(){
		$pre_pop = 0;
		global $beanList;
		if (isset($_GET['do_save']) && $_GET['do_save'] == 1) {
			$do_save = $_GET['do_save'];
		} else {
			$do_save = '0';
		}

		if (isset($_GET['do_new']) && $_GET['do_new'] == 1) {
			$do_new = $_GET['do_new'];
		} else {
			$do_new = '0';
		}
		
		if (!$do_save) {
			if (!$do_new) {
				if(empty($this->bean->id) ){
					global $app_strings;
					sugar_die($app_strings['ERROR_NO_RECORD']);
			}
		}				
		
		global $app_strings;
		global $app_list_strings;
		global $mod_strings;

		$dv = new SUI_page($this->module.' Edit');
		if(!empty($this->bean->id)){
			$dv->add_link('Back to Detail','index.php?module='.$this->module.'&action=detail_mobile&record='.$this->bean->id);
			$dv->add_newline();
		}
		else{
			$dv->add_link('Back to List','index.php?module='.$this->module.'&action=list_mobile');
			$dv->add_newline();
		}
		echo $dv->render();
		require_once('custom/include/sugarmobile/metadata/fields.php');

		$main_form = new SUI_form('index.php');

		foreach ($detail_view_mobile[$this->bean->module_dir] as $value2) {
			$required = '';
			if(isset($this->bean->required_fields[$value2]) && $this->bean->required_fields[$value2] == 1) {
				$required = '<font color="red">*</font>';
			}
			if (isset($mod_strings[$this->bean->field_name_map[$value2]['vname']])) {
				$lbl = $mod_strings[$this->bean->field_name_map[$value2]['vname']] . $required . " ";
			} else {
				$lbl = $app_strings[$this->bean->field_name_map[$value2]['vname']] . $required . " ";
			}
			if (isset($this->bean->field_name_map[$value2]['auto_increment'])) {
				$main_form->add_text($this->bean->$value2, $lbl,1);
			} else {		
				$value = $this->bean->$value2;
				$type = $this->bean->field_name_map[$value2]['type'];
				if ($type == 'enum') {
					$dom_name = $this->bean->field_name_map[$value2]['options'];
					$dom_array = ($app_list_strings[$dom_name]);
					$dom_selected_value = '';
					foreach ($dom_array as $key => $value3) {
						if ($key == '') { $key = 'BLANK'; }
						if ($value3 == '') { $value3 = '-none-'; }
						if ($value == $key) {
							$dom_selected_value = $value3;	
						}
					}
					$main_form->add_select($dom_array,$value2,$dom_selected_value,$lbl);
				} else if ($type == 'relate' || $value2 == 'assigned_user_name') {
					if ($value2 == 'assigned_user_name') {
						require_once('include/utils.php');
						$user_list = get_user_array();
						$selected_user = '';
						foreach($user_list as $user_id => $user_name) {
							global $current_user;
							if($value == $user_name && $do_new == 0) {
								$selected_user = $user_name;	
							} else if ($user_name == $current_user->user_name && $do_new == 1) {
								$selected_user = $user_name;
							}
							$user_array[$user_id] = $user_name;
						}
						$main_form->add_select($user_array,'assigned_user_id', $selected_user,'User: ');
					} else if ($value2 == 'team_name') {
						require_once('include/utils.php');
						$team_list = get_team_array();
						$selected_team = '';
						foreach($team_list as $team_id => $team_name) {
							global $current_user;
							if($value == $team_name && $do_new == 0) {
								$selected_team = $team_name;
							} else if ($team_id == $current_user->default_team && $do_new == 1) {
								$selected_team = $team_name;
							}
							$team_array[$team_id] = $team_name;
						}
						$main_form->add_select($team_array,'team_id',$selected_team,'Team: ');
					}
				} else if ($type == 'datetime') { 
					$timedate = new TimeDate();
					$generic_date = $timedate->swap_formats($value,$timedate->get_date_time_format(),"Y-m-d H:i:s");
					$main_form->add_text('',$lbl);
					$split_date = array();
					preg_match('/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/',$generic_date,$split_date);
					
					if (isset($split_date[1])) {
						$main_form->add_input($value2."_y",$split_date[1],'Y');
					} else {
						$main_form->add_input($value2."_y","2007",'Y');
					}
						$main_form->add_text('','M');
					$month_array = array();
					$selected_month = '';
					for ($month_ct = 0;$month_ct<=11;$month_ct++) {
						if ($month_ct+1 < 10) {
							$month_pad = sprintf("0%d",$month_ct+1);
						} else {
							$month_pad = $month_ct+1;
						}
						if (isset($split_date[2]) && $month_pad == $split_date[2]) {
							$month_array[$month_pad] = $timedate->getMonthName($month_ct);
							$selected_month = $timedate->getMonthName($month_ct);
							// $month->add_option($timedate->getMonthName($month_ct),$month_pad,HAW_SELECTED);
						} else {
							$month_array[$month_pad] = $timedate->getMonthName($month_ct);
						}
					}
					$main_form->add_select($month_array,$value2.'_m',$selected_month,$lbl,1);
					if (isset($split_date[3])) {
						$main_form->add_input($value2."_d",$split_date[3],'D');
					} else {
						$main_form->add_input($value2."_d",'','D');
					}
					if (isset($split_date[4])) {
						$main_form->add_input($value2."_h",$split_date[4],'H');
					} else {
						$main_form->add_input($value2."_h",'','H');
					}
					if (isset($split_date[5])) {
						$main_form->add_input($value2."_mi",$split_date[5],'Mi');
					} else {
						$main_form->add_input($value2."_mi",'','Mi');
					}
				} else if ($type == 'bool') {
					$main_form->add_bool($value2,$value,$lbl,1);	
				} else if ($value2 == 'parent_id' || $value2 == 'parent_type') {
					
				} else {
					$main_form->add_input($value2,$value,$lbl,1);
				}
			}
		}
		
		foreach ($_GET as $key => $value) {
			$pop_value = array();
			if (preg_match('/pop_(.*)/',$key, $pop_value)) {
				$main_form->add_hidden($pop_value[1], $_GET[$key]);
				$pre_pop = 1;
			}
		}

		if ($pre_pop) {
			$main_form->add_hidden('pre_pop','1');
		}

		$main_form->add_hidden('do_save','1');
		$main_form->add_hidden('action','edit_mobile');
		$main_form->add_hidden('module',$this->module);
		$main_form->add_hidden('record',$this->bean->id);

		$main_form->add_newline();
		$main_form->add_button('Save');
	
		$main_form->render();

		} else {
			$focus = new $beanList[$this->module]();
			if (isset($_GET['record'])) {
				$focus->id = $_GET['record'];
			}

			if (isset($_GET['parent_type'])) {
				$focus->parent_type = $_GET['parent_type'];
			}
			if (isset($_GET['parent_id'])) {
				if ($this->module == 'Notes') {
					$focus->contact_id = $_GET['parent_id'];
				}
				$focus->parent_id = $_GET['parent_id'];
			}
			
			if (isset($_GET['pre_pop'])) {
				$pre_pop = 1;
			}
			
			
			require_once('custom/include/sugarmobile/metadata/fields.php');
			
			foreach ($detail_view_mobile[$this->module] as $value2) {
				if ($focus->required_fields[$value2] == 1 && $_GET[$value2] == '') {
					die("Please go back and fill out the required fields");
				}
				if ($value2 == 'assigned_user_name') { $value2 = 'assigned_user_id'; }
				if ($value2 == 'team_name') { $value2 = 'team_id'; }
			 	if($focus->field_name_map[$value2]['type'] == 'enum' && $_GET[$value2] == 'BLANK') {
					$focus->$value2 = '';
				} else if ($focus->field_name_map[$value2]['type'] == 'datetime') {
					$build_date = $_GET[$value2.'_y'] .'-'. $_GET[$value2.'_m'] .'-'. $_GET[$value2.'_d'] .' '. $_GET[$value2.'_h'] .':'. $_GET[$value2.'_mi'] . ':00';
					$timedate = new TimeDate();
					$focus->$value2 = $timedate->swap_formats($build_date,'Y-m-d H:i:s',$timedate->get_date_time_format());
				} else if ($focus->field_name_map[$value2]['type'] == 'bool') {
					if ($_GET[$value2] == 'on') {
						$focus->$value2 = '1';
					} else {
						$focus->$value2 = '0';
					}
				} else {
					$focus->$value2 = $_GET[$value2];
				}
			}
			
			$focus->save();
			
			if ($pre_pop) {  //check for relationships
				global $beanFiles;
				require_once($beanFiles[$beanList[$focus->parent_type]]);
				$parent_obj = new $beanList[$focus->parent_type];
				$parent_id_name = strtolower($parent_obj->object_name);
				$child_id_name = strtolower($focus->object_name);
				//echo $parent_id_name."<br>";
				$rel_parent_table = 'rel_'.$parent_id_name.'_table';
				$rel_parent_table_alt = 'rel_'.$parent_id_name.'s_table';
				$relate_values = array($parent_id_name.'_id'=>$focus->parent_id,$child_id_name.'_id'=>$focus->id);
				if (isset($focus->$rel_parent_table)) {
					$focus->set_relationship($focus->$rel_parent_table, $relate_values, true, false, array());
				} else if (isset($focus->$rel_parent_table_alt)) {  //someday, vardefs will be better...
					$focus->set_relationship($focus->$rel_parent_table_alt, $relate_values, true, false, array());
				}
			}
			if (!$pre_pop) {
				header('Location: index.php?module='.$_GET['module'].'&record='.$focus->id.'&action=detail_mobile');
			} else {
				header('Location: index.php?module='.$focus->parent_type.'&record='.$focus->parent_id.'&action=detail_mobile');
			}
		}
 	}
}
