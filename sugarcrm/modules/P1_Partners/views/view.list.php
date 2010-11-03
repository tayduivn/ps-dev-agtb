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
 * by SugarCRM are Copyright (C) 2004-2010 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************

 * Description:  TODO To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

require_once('include/MVC/View/views/view.list.php');

require_once("include/SugarTinyMCE.php");
class partnerTinyMCE extends SugarTinyMCE {
	function getInstance($targets = "") {
		global $json;
		
		if(empty($json)) {
			$json = getJSONobj();
		}
		
		$config = $this->defaultConfig;
		$config['elements'] = $targets;
		$config['theme_advanced_buttons1'] = $this->buttonConfigs['default']['buttonConfig']; 
		$config['theme_advanced_buttons2'] = $this->buttonConfigs['default']['buttonConfig2']; 
		$config['theme_advanced_buttons3'] = $this->buttonConfigs['default']['buttonConfig3']; 
		$jsConfig = $json->encode($config);
		
		$instantiateCall = '';
		if (!empty($targets)) {
			$exTargets = explode(",", $targets);
			foreach($exTargets as $instance) {
				//$instantiateCall .= "tinyMCE.execCommand('mceAddControl', false, document.getElementById('{$instance}'));\n";
			} 
		}

		$ret =<<<eoq


	tinyMCE.init({$jsConfig});
	{$instantiateCall}	


eoq;
		return $ret;
	}
	
	function getTinyJsPath() {
		$path = getJSPath('include/javascript/tiny_mce/tiny_mce.js');
		return '<script type="text/javascript" language="Javascript" src="'.$path.'"></script>';
	}
}

class P1_PartnersViewList extends ViewList {
 
 	function P1_PartnersViewList(){
 		parent::ViewList();
 		//IT Request #10243
 		//By default the order by will be the first entry in the Ordery By Column under the Advanced Search -> Layout Options
 		/*if( empty($_REQUEST['P1_Partners2_P1_PARTNERS_ORDER_BY'] ) )
 		{
 			$_REQUEST['P1_Partners2_P1_PARTNERS_ORDER_BY'] = 'account_name';
 			$_REQUEST['lvso'] = "asc"; 
 		}*/
 	}
 	
	
	function preDisplay(){			
	 	parent::preDisplay();
	 	//IT REQUEST #10169 - asandberg
	 	$this->lv->showMassupdateFields = false;
		$this->lv->delete = false;
	}
	
 	function prepareSearchForm(){
 	global $mod_strings;
 	$this->searchForm = null;
    
        //search
        $view = 'basic_search';
        if(!empty($_REQUEST['search_form_view']) && $_REQUEST['search_form_view'] == 'advanced_search')
            $view = $_REQUEST['search_form_view'];
        $this->headers = true;
        if(!empty($_REQUEST['search_form_only']) && $_REQUEST['search_form_only'])
            $this->headers = false;
        elseif(!isset($_REQUEST['search_form']) || $_REQUEST['search_form'] != 'false') {
            if(isset($_REQUEST['searchFormTab']) && $_REQUEST['searchFormTab'] == 'advanced_search') {
                $view = 'advanced_search';
            }else {
                $view = 'basic_search';
            }
        }
        
        $this->use_old_search = true;
        if(file_exists('modules/'.$this->module.'/SearchForm.html')){
            require_once('include/SearchForm/SearchForm.php');
            $this->searchForm = new SearchForm($this->module, $this->seed);
        }else{
            $this->use_old_search = false;
            require_once('include/SearchForm/SearchForm2.php');
            
            
/*          if(!empty($metafiles[$this->module]['searchdefs']))
                require_once($metafiles[$this->module]['searchdefs']);
            elseif(file_exists('modules/'.$this->module.'/metadata/searchdefs.php'))
                require_once('modules/'.$this->module.'/metadata/searchdefs.php');
*/
            
            if (file_exists('custom/modules/'.$this->module.'/metadata/searchdefs.php'))
            {
                require_once('custom/modules/'.$this->module.'/metadata/searchdefs.php');
            }
            elseif (!empty($metafiles[$this->module]['searchdefs']))
            {
                require_once($metafiles[$this->module]['searchdefs']);
                echo "2";
            }
            elseif (file_exists('modules/'.$this->module.'/metadata/searchdefs.php'))
            {
                require_once('modules/'.$this->module.'/metadata/searchdefs.php');
                echo 'modules/'.$this->module.'/metadata/searchdefs.php';
            }
                
                
            if(!empty($metafiles[$this->module]['searchfields']))
                require_once($metafiles[$this->module]['searchfields']);
            elseif(file_exists('modules/'.$this->module.'/metadata/SearchFields.php'))
                require_once('modules/'.$this->module.'/metadata/SearchFields.php');
        
            $this->searchForm = new SearchForm($this->seed, $this->module, $this->action);
            $this->searchForm->setup($searchdefs, $searchFields, 'include/SearchForm/tpls/SearchFormGeneric.tpl', $view, $this->listViewDefs);
            
                   $this->searchForm->tabs = array(array('title'  => $mod_strings['LNK_BASIC_SEARCH'],
                            'link'   => $this->module . '|basic_search',
                            'key'    => $this->module . '|basic_search',
                            'name'   => 'basic',
                            'displayDiv'   => ''),
                      array('title'  => $mod_strings['LNK_ADVANCED_SEARCH'],
                            'link'   => $this->module . '|advanced_search',
                            'key'    => $this->module . '|advanced_search',
                            'name'   => 'advanced',
                            'displayDiv'   => 'display:none'),
                       );
                       
            $this->searchForm->lv = $this->lv;
        }
 	}
	
	/**
	 * In order to add the 'Assign' button we need to pass in a custom ListViewGeneric tpl which is only passed in during this call.
	 * It's unfortunate that the ListViewSmarty doesn't accept the tpl file as an instance variable so this is the best solution to keep
	 * things upgrade safe.  The other option is to extend the current ListViewSmarty file and overide the process function.
	 * IT REQUEST #10205
	 */
	function listViewProcess(){
		global $mod_strings,$timedate;
		$this->processSearchForm();
		$this->lv->searchColumns = $this->searchForm->searchColumns;
		
		$this->lv->ss->assign('prmAssignButton', $this->_buildAssignButton() );
		$this->lv->ss->assign('setToJunkButton', $this->_buildJunkButton() );
	
		//Assign variables needed for localization when validating fields for inline edit
		$seps = get_number_seperators();
		$this->lv->ss->assign('NUM_GRP_SEP', $seps[0]);
		$this->lv->ss->assign('DEC_SEP', $seps[1]);
		$this->ss->assign('SIXTYMIN_OPP', $GLOBALS['mod_strings']['LBL_SIXTYMIN_OPP']);
		$this->ss->assign('REJECTED_OPP', $GLOBALS['mod_strings']['LBL_REJECTED_OPP']);
		$this->ss->assign('MATURE_OPP', $GLOBALS['mod_strings']['LBL_MATURE_OPP']);
		$this->ss->assign('QF_REJECTED_OPP', $GLOBALS['mod_strings']['LBL_QUICK_FILTER_REJECTED_OPP']);
		$this->ss->assign('QF_SIXTYMIN_OPP', $GLOBALS['mod_strings']['LBL_QUICK_FILTER_SIXTYMIN_OPP']);
		$this->ss->assign('QF_CONFLICT', $GLOBALS['mod_strings']['LBL_QUICK_FILTER_CONFLICT']);
		$this->ss->assign('QF_MATURE_OPP', $GLOBALS['mod_strings']['LBL_QUICK_FILTER_MATURE_OPP']);
		$this->ss->assign('LEGEND', $GLOBALS['mod_strings']['LBL_LEGEND']);
		$this->ss->assign('FILTER_BY', $GLOBALS['mod_strings']['LBL_FILTER_BY']);
		
		if(isset($_POST['partner_contact_notified_c_advanced']) || isset($_POST['partner_contact_notified_c_basic'])) {
			$this->ss->assign('POST_MATURE', '1');
		} else {
			$this->ss->assign('POST_MATURE', '0');
		}
		if((isset($_POST['sixtymin_opp_c_advanced']) && $_POST['sixtymin_opp_c_advanced'] == 1) || (isset($_POST['sixtymin_opp_c_basic']) && $_POST['sixtymin_opp_c_basic'] == 1)) {
			$this->ss->assign('POST_SIXTY', '1');
		} else {
			$this->ss->assign('POST_SIXTY', '0');
		}
		if((isset($_POST['accepted_by_partner_c_advanced']) && isset($_POST['open_tasks'])) || (isset($_POST['accepted_by_partner_c_basic']) && isset($_POST['open_tasks']))) {
			$this->ss->assign('POST_REJECTED', '1');
		} else {
			$this->ss->assign('POST_REJECTED', '0');
		}
        if((isset($_POST['conflict_c_advanced']) && $_POST['conflict_c_advanced'] == 1) || (isset($_POST['conflict_c_basic']) && $_POST['conflict_c_basic'] == 1)) {
			$this->ss->assign('POST_CONFLICT', '1');
		} else {
			$this->ss->assign('POST_CONFLICT', '0');
		}
		/**** DATE TIME ***/
		$this->lv->ss->assign('CAL_DATEFORMAT',$timedate->get_cal_date_format());
        	$this->lv->ss->assign('USER_DATEFORMAT', $timedate->get_user_date_format());
        	$this->lv->ss->assign('USER_TIMEFORMAT', $timedate->get_user_time_format());
        	$date = gmdate($GLOBALS['timedate']->get_db_date_time_format());
       		$this->lv->ss->assign('USER_DATEDEFAULT', $timedate->to_display_date($date));
        	$this->lv->ss->assign('USER_TIMEDEFAULT', $timedate->to_display_time($date,true));
        	
		/**** TINYMCE ****/	
        	$tiny = new partnerTinyMCE();
        	$tiny->defaultConfig['width'] = '100px';
		$tiny->defaultConfig['height'] = '350px';
		$tinyHtml = $tiny->getInstance('P1_Partnersbody_html');
		$tiny_contactemail = $tiny->getInstance('P1_Partners_contactmail_body_html');
		$tinyPath = $tiny->getTinyJsPath();
        	$this->lv->ss->assign('tiny', $tinyHtml);
		$this->lv->ss->assign('tiny_contactemail', $tiny_contactemail);
        	$this->lv->ss->assign('tinyPath', $tinyPath);
			
		$old_list_max_entries_per_page = $GLOBALS['sugar_config']['list_max_entries_per_page'];
		$GLOBALS['sugar_config']['list_max_entries_per_page'] = 50;	

		if(!$this->headers)
			return;
			
		if(empty($_REQUEST['search_form_only']) || $_REQUEST['search_form_only'] == false){
			$this->ss->display('modules/P1_Partners/tpls/FastFilters.tpl');	
			$listview_template = 'modules/P1_Partners/tpls/ListViewGeneric.tpl';
			$this->lv->setup($this->seed, $listview_template, $this->where, $this->params);
			$savedSearchName = empty($_REQUEST['saved_search_select_name']) ? '' : (' - ' . $_REQUEST['saved_search_select_name']);
			echo get_form_header($GLOBALS['mod_strings']['LBL_LIST_FORM_TITLE'] . $savedSearchName, '', false);
			echo $this->lv->display();
			
		}
		
		$GLOBALS['sugar_config']['list_max_entries_per_page'] = $old_list_max_entries_per_page;
 	}
 	
 	/**
 	 * Custom function to build the assign button which is displayed on the P1_Partners ListView that displays the Assign Wizard.
 	 * IT REQUEST #10205
 	 */
	function _buildAssignButton()
	{
		$p1_part_language = return_module_language($GLOBALS['current_language'], 'P1_Partners');
		$assign_button = "<input type='button' id='assign_button' value='{$p1_part_language['LBL_ASSIGN_WIZARD_BUTTON_TITLE']}' name='assign_button' class='button' onclick='if(sugarListView.get_checks_count() < 1) {alert(\"Please select at least 1 record to proceed.\"); return false;} else { this.form.return_module.value=\"P1_Partners\"; this.form.return_action.value=\"index\"; this.form.action.value = \"assignwizard\"; this.form.module.value = \"P1_Partners\"; postAssignWizard();}' title='{$p1_part_language['LBL_ASSIGN_WIZARD_BUTTON_TITLE']}' disabled/>";
		return $assign_button;
	}

	function _buildJunkButton()
        {
                $p1_part_language = return_module_language($GLOBALS['current_language'], 'P1_Partners');
                $assign_button = "<input type='submit' value='{$p1_part_language['LBL_SET_TO_JUNK']}' name='junk_button' class='button' 
			onclick='                                
				if(sugarListView.get_checks_count() < 1) {                                        
				alert(\"Please select at least 1 record to proceed.\");                                         
				return false;                                
				} else {                                         
				var junk_confirm = confirm(\"Are you sure you want to set the selected Opp(s) to Junk?\");                                        
				if(junk_confirm == true) {                                        
					this.form.return_module.value=\"P1_Partners\"; 
                                        this.form.return_action.value=\"index\"; 
                                        this.form.action.value = \"settojunk\"; 
                                        this.form.module.value = \"P1_Partners\"; 
                                        }
                                        else {
                                                return false;
                                        }
                                }' 
                        title='{$p1_part_language['LBL_SET_TO_JUNK']}' />";
                return $assign_button;
        }
}
