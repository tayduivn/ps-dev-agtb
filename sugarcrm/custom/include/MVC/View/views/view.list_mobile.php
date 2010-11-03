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
require_once('include/MVC/View/SugarView.php');
require_once('custom/include/sugarmobile/ui.php');
require_once('include/utils.php');

class ViewList_Mobile extends SugarView{
	var $type ='list';
	var $read_only_modules = array('Employees');
	
 	function ViewList_Mobile(){
 		parent::SugarView();
		$this->options['show_all'] = false;
 	}

 	function display(){
	   	$module = $GLOBALS['module'];
		
		global $app_list_strings;

		require_once('custom/include/sugarmobile/metadata/fields.php');
		
		if(!array_key_exists($module, $module_list_mobile)){
			die("The metadata does not exist for this module.");
		}
		
		$seed = $this->bean;
		$mod_lbl = $this->module;
		if(isset($app_list_strings['moduleList'][$this->module])){
			$mod_lbl = $app_list_strings['moduleList'][$this->module];
		}
		$lv = new SUI_Page($mod_lbl .' List');
		
		if(!in_array($this->module, $this->read_only_modules)){
			$record_display = $app_list_strings['moduleListSingular'][$this->module];
			switch($record_display){
				case 'Bug Tracker': $record_display = 'Bug'; break;
				default: break;
			}
			
			$lv->add_link('(+) Add '.$record_display,'index.php?module='.$this->module.'&action=edit_mobile&do_new=1');
		}

		$form_filter = '';
		if (isset($_GET['filter'])) {
			$form_filter = $_GET['filter'];
		}

		$module_select = new SUI_form('index.php');
		$module_select->add_select($module_list_mobile,'module',$this->module);
		$module_select->add_hidden('action','list_mobile');

		$module_select->add_hidden('action','list_mobile');
		$module_select->add_input('filter', $form_filter, '');
		$module_select->add_button('Search');
		$module_select->render(1);

		$where = "";
		$filter = '';
		if(isset($_GET['filter'])) {
			$filter = $_GET['filter'] . '%';
		} else {
			$filter = '%';
		}

		if (isset($seed->field_name_map['case_number'])) {
			$where .= $seed->table_name . ".case_number =\"".$filter."\" OR ".$seed->table_name . ".name LIKE \"".$filter."\"";
		}
		else if (isset($seed->field_name_map['bug_number'])) {
			$where .= $seed->table_name . ".bug_number =\"".$filter."\" OR ".$seed->table_name . ".name LIKE \"".$filter."\"";
		}
		else if (isset($seed->field_name_map['name']['source']) && $seed->field_name_map['name']['source']  == 'non-db') {
			$where .= $seed->table_name . ".first_name LIKE \"".$filter."\" OR ".$seed->table_name . ".last_name LIKE \"".$filter."\"";
		} else {
			$where .= $seed->table_name . ".name LIKE \"".$filter."\"";
		}

		if(isset($_GET['mine']) && $_GET['mine'] == '1') {
			global $current_user;
			$where .= $seed->table_name. ".assigned_user_id = \"" . $current_user->id . "\"";;
		}

		if(isset($_GET['offset'])) {
			$offset = $_GET['offset'];
		} else {
			$offset = 0;
		}

		require_once('include/ListView/ListViewData.php');
		$lvd = new ListViewData();
		$foo = $lvd->getListViewData($seed,$where,$offset,10);


		$ct = 0;
		
		$filter_duplicates = array();
		foreach($foo['data'] as $index) {
			$index['NAME'] = html_entity_decode($index['NAME'],ENT_QUOTES);
			if (isset($seed->field_name_map['case_number'])) {
				$lv->add_link($index['CASE_NUMBER'].' '.$index['NAME'],'index.php?module='.$this->module.'&action=detail_mobile&record='.$index['ID']);
			} else if (isset($seed->field_name_map['bug_number'])) {
				$lv->add_link($index['BUG_NUMBER'].' '.$index['NAME'],'index.php?module='.$this->module.'&action=detail_mobile&record='.$index['ID']);
			} else if ($this->module == 'Employees'){
				if(!in_array($index['ID'], $filter_duplicates)){
					$lv->add_link($index['NAME'],'index.php?module='.$this->module. '&action=detail_mobile&record='.$index['ID']);
					$filter_duplicates[$index['ID']] = $index['ID'];
				}
			} else {			
				$lv->add_link($index['NAME'],'index.php?module='.$this->module. '&action=detail_mobile&record='.$index['ID']);
			}
		//$lv->add_text($title);
		}
		if(1) {
			//$new_offset = $offset+10;
			//$next_link = new HAW_link("Next>>", 'index.php?module=' . $this->module . '&action=list_mobile&filter='.$filter.'&offset='.$new_offset);
			//$lv->add_link($next_link);
		}


		echo $lv->render();
 	}

}
?>
