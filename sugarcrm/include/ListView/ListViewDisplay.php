<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/**
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
 * by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights Reserved.
 */

// $Id: ListViewDisplay.php 56945 2010-06-14 19:51:27Z jmertic $

require_once('include/ListView/ListViewData.php');
require_once('include/MassUpdate.php');

class ListViewDisplay {

	var $show_mass_update_form = false;
	var $show_action_dropdown = true;
	var $rowCount;
	var $mass = null;
	var $seed;
	var $multi_select_popup;
	var $lvd;
	var $moduleString;
	var $export = true;
	var $multiSelect = true;
	var $mailMerge = true;
	var $should_process = true;
	/*
	 * Used in view.popup.php. Sometimes there are fields on the search form that are not referenced in the listviewdefs. If this
	 * is the case, then the filterFields will be set and the related fields will not be referenced when calling create_new_list_query.
	 */
	var $mergeDisplayColumns = false;
    public $actionsMenuExtraItems = array();

	/**
	 * Constructor
	 * @return null
	 */
	function ListViewDisplay() {
		$this->lvd = new ListViewData();
		$this->searchColumns = array () ;
	}
	function shouldProcess($moduleDir){
		if(!empty($GLOBALS['sugar_config']['save_query']) && $GLOBALS['sugar_config']['save_query'] == 'populate_only'){
		    if(empty($GLOBALS['displayListView']) && (!empty($_REQUEST['clear_query']) || $_REQUEST['module'] == $moduleDir && ((empty($_REQUEST['query']) || $_REQUEST['query'] == 'MSI' )&& (empty($_SESSION['last_search_mod']) || $_SESSION['last_search_mod'] != $moduleDir ) ))){
				$_SESSION['last_search_mod'] = $_REQUEST['module'] ;
				$this->should_process = false;
				return false;
			}
		}
		$this->should_process = true;
		return true;
	}

	/**
	 * Setup the class
	 * @param seed SugarBean Seed SugarBean to use
	 * @param file File Template file to use
	 * @param string $where
	 * @param offset:0 int offset to start at
	 * @param int:-1 $limit
	 * @param string[]:array() $filter_fields
	 * @param array:array() $params
	 * 	Potential $params are
		$params['distinct'] = use distinct key word
		$params['include_custom_fields'] = (on by default)
		$params['massupdate'] = true by default;
        $params['handleMassupdate'] = true by default, have massupdate.php handle massupdates?
	 * @param string:'id' $id_field
	 */
	function setup($seed, $file, $where, $params = array(), $offset = 0, $limit = -1,  $filter_fields = array(), $id_field = 'id') {
        $this->should_process = true;
        if(isset($seed->module_dir) && !$this->shouldProcess($seed->module_dir)){
        		return false;
        }
        if(isset($params['export'])) {
          $this->export = $params['export'];
        }
        if(!empty($params['multiSelectPopup'])) {
		  $this->multi_select_popup = $params['multiSelectPopup'];
        }
		if(!empty($params['massupdate']) && $params['massupdate'] != false) {
			$this->show_mass_update_form = true;
			$this->mass = new MassUpdate();
			$this->mass->setSugarBean($seed);
			if(!empty($params['handleMassupdate']) || !isset($params['handleMassupdate'])) {
                $this->mass->handleMassUpdate();
            }
		}
		$this->seed = $seed;

        $filter_fields = $this->setupFilterFields($filter_fields);

        $data = $this->lvd->getListViewData($seed, $where, $offset, $limit, $filter_fields, $params, $id_field);

		foreach($this->displayColumns as $columnName => $def)
		{
			$seedName =  strtolower($columnName);
            if(!empty($this->lvd->seed->field_defs[$seedName])){
                $seedDef = $this->lvd->seed->field_defs[$seedName];
            }

			if(empty($this->displayColumns[$columnName]['type'])){
				if(!empty($seedDef['type'])){
		            $this->displayColumns[$columnName]['type'] = (!empty($seedDef['custom_type']))?$seedDef['custom_type']:$seedDef['type'];
		        }else{
		        	$this->displayColumns[$columnName]['type'] = '';
		        }
			}//fi empty(...)

			if(!empty($seedDef['options'])){
					$this->displayColumns[$columnName]['options'] = $seedDef['options'];
			}

	        //C.L. Fix for 11177
	        if($this->displayColumns[$columnName]['type'] == 'html') {
	            $cField = $this->seed->custom_fields;
	               if(isset($cField) && isset($cField->bean->$seedName)) {
	                 	$seedName2 = strtoupper($columnName);
	                 	$htmlDisplay = html_entity_decode($cField->bean->$seedName);
	                 	$count = 0;
	                 	while($count < count($data['data'])) {
	                 		$data['data'][$count][$seedName2] = &$htmlDisplay;
	                 	    $count++;
	                 	}
	            	}
	        }//fi == 'html'

			if (!empty($seedDef['sort_on'])) {
		    	$this->displayColumns[$columnName]['orderBy'] = $seedDef['sort_on'];
		    }

            if(isset($seedDef)){
                // Merge the two arrays together, making sure the seedDef doesn't override anything explicitly set in the displayColumns array.
                $this->displayColumns[$columnName] = $this->displayColumns[$columnName] + $seedDef;
            }

		    //C.L. Bug 38388 - ensure that ['id'] is set for related fields
            if(!isset($this->displayColumns[$columnName]['id']) && isset($this->displayColumns[$columnName]['id_name'])) {
               $this->displayColumns[$columnName]['id'] = strtoupper($this->displayColumns[$columnName]['id_name']);
            }
		}

		$this->process($file, $data, $seed->object_name);
		return true;
	}

	function setupFilterFields($filter_fields = array())
	{
		// create filter fields based off of display columns
        if(empty($filter_fields) || $this->mergeDisplayColumns) {
            foreach($this->displayColumns as $columnName => $def) {

               $filter_fields[strtolower($columnName)] = true;

            if(isset($this->seed->field_defs[strtolower($columnName)]['type']) &&
               strtolower($this->seed->field_defs[strtolower($columnName)]['type']) == 'currency' &&
               isset($this->seed->field_defs['currency_id'])) {
                    $filter_fields['currency_id'] = true;
            }

               if(!empty($def['related_fields'])) {
                    foreach($def['related_fields'] as $field) {
                        //id column is added by query construction function. This addition creates duplicates
                        //and causes issues in oracle. #10165
                        if ($field != 'id') {
                            $filter_fields[$field] = true;
                        }
                    }
                }
                if (!empty($this->seed->field_defs[strtolower($columnName)]['db_concat_fields'])) {
                    foreach($this->seed->field_defs[strtolower($columnName)]['db_concat_fields'] as $index=>$field){
                        if(!isset($filter_fields[strtolower($field)]) || !$filter_fields[strtolower($field)])
                        {
                            $filter_fields[strtolower($field)] = true;
                        }
                    }
                }
            }
            foreach ($this->searchColumns as $columnName => $def )
            {
                $filter_fields[strtolower($columnName)] = true;
            }
        }

        //BEGIN SUGARCRM flav=pro ONLY

        //check for team_set_count
        if(!empty($filter_fields['team_name']) && empty($filter_fields['team_count'])){
            $filter_fields['team_count'] = true;

            //Add the team_id entry so that we can retrieve the team_id to display primary team
            $filter_fields['team_id'] = true;
        }
        //END SUGARCRM flav=pro ONLY

        return $filter_fields;
	}


	/**
	 * Any additional processing
	 * @param file File template file to use
	 * @param data array row data
	 * @param html_var string html string to be passed back and forth
	 */
	function process($file, $data, $htmlVar) {
		$this->rowCount = count($data['data']);
		$this->moduleString = $data['pageData']['bean']['moduleDir'] . '2_' . strtoupper($htmlVar) . '_offset';
	}

	/**
	 * Display the listview
	 * @return string ListView contents
	 */
	function display() {
		if(!$this->should_process) return '';
		$str = '';
		if($this->multiSelect == true && $this->show_mass_update_form)
			$str = $this->mass->getDisplayMassUpdateForm(true, $this->multi_select_popup).$this->mass->getMassUpdateFormHeader($this->multi_select_popup);

        return $str;
	}
	/**
	 * Display the select link
     * @return string select link html
	 * @param echo Bool set true if you want it echo'd, set false to have contents returned
	 */
	function buildSelectLink($id = 'select_link', $total=0, $pageTotal=0) {
		global $app_strings;
		if ($pageTotal < 0)
			$pageTotal = $total;
		$script = "<script>
			function select_overlib() {
				return overlib('<a style=\'width: 150px\' name=\"thispage\" class=\'menuItem\' onmouseover=\'hiliteItem(this,\"yes\");\' onmouseout=\'unhiliteItem(this);\' onclick=\'if (document.MassUpdate.select_entire_list.value==1){document.MassUpdate.select_entire_list.value=0;sListView.check_all(document.MassUpdate, \"mass[]\", true, $pageTotal)}else {sListView.check_all(document.MassUpdate, \"mass[]\", true)};\' href=\'#\'>{$app_strings['LBL_LISTVIEW_OPTION_CURRENT']}&nbsp;({$pageTotal})</a>"
			. "<a style=\'width: 150px\' name=\"selectall\" class=\'menuItem\' onmouseover=\'hiliteItem(this,\"yes\");\' onmouseout=\'unhiliteItem(this);\' onclick=\'sListView.check_entire_list(document.MassUpdate, \"mass[]\",true,{$total});\' href=\'#\'>{$app_strings['LBL_LISTVIEW_OPTION_ENTIRE']}&nbsp;({$total})</a>"
			. "<a style=\'width: 150px\' name=\"deselect\" class=\'menuItem\' onmouseover=\'hiliteItem(this,\"yes\");\' onmouseout=\'unhiliteItem(this);\' onclick=\'sListView.clear_all(document.MassUpdate, \"mass[]\", false);\' href=\'#\'>{$app_strings['LBL_LISTVIEW_NONE']}</a>"
			. "', CENTER, '"
			. "', STICKY, MOUSEOFF, 3000, CLOSETEXT, '<img border=0 src=" . SugarThemeRegistry::current()->getImageURL('close_inline.gif')
			. ">', WIDTH, 150, CLOSETITLE, '" . $app_strings['LBL_ADDITIONAL_DETAILS_CLOSE_TITLE'] . "', CLOSECLICK, FGCLASS, 'olOptionsFgClass', "
			. "CGCLASS, 'olOptionsCgClass', BGCLASS, 'olBgClass', TEXTFONTCLASS, 'olFontClass', CAPTIONFONTCLASS, 'olOptionsCapFontClass', CLOSEFONTCLASS, 'olOptionsCloseFontClass',TIMEOUT,1000);
			}
			</script>";
            $script .= "<a id='$id' onclick='return select_overlib();' href=\"#\"><img src='".SugarThemeRegistry::current()->getImageURL('MoreDetail.png')."' border='0''>"."</a>";

		return $script;
	}

	/**
	 * Display the actions link
	 *
	 * @param  string $id link id attribute, defaults to 'actions_link'
	 * @return string HTML source
	 */
	protected function buildActionsLink(
	    $id = 'actions_link'
	    )
	{
	    global $app_strings;
		$closeText = "<img border=0 src=" . SugarThemeRegistry::current()->getImageURL('close_inline.gif') . " />";
		$moreDetailImage = SugarThemeRegistry::current()->getImageURL('MoreDetail.png');
		$menuItems = '';

		// delete
		if ( ACLController::checkAccess($this->seed->module_dir,'delete',true) && $this->delete )
			$menuItems .= $this->buildDeleteLink();
		// compose email
        if ( isset($_REQUEST['module']) && $_REQUEST['module'] != 'Users' && $_REQUEST['module'] != 'Employees' &&
            ( SugarModule::get($_REQUEST['module'])->moduleImplements('Company')
                || SugarModule::get($_REQUEST['module'])->moduleImplements('Person') ) )
			$menuItems .= $this->buildComposeEmailLink($this->data['pageData']['offsets']['total']);
		// mass update
		$mass = new MassUpdate();
		$mass->setSugarBean($this->seed);
		if ( ACLController::checkAccess($this->seed->module_dir,'edit',true) && $this->showMassupdateFields && $mass->doMassUpdateFieldsExistForFocus() )
            $menuItems .= $this->buildMassUpdateLink();
		//BEGIN SUGARCRM flav=sales ONLY
		else if($this->seed->module_dir == 'Users' && $GLOBALS['current_user']->user_type == 'UserAdministrator')
			$menuItems .= $this->buildMassUpdateLink();
		//END SUGARCRM flav=sales ONLY
		//BEGIN SUGARCRM flav!=sales ONLY
		// merge
		if ( $this->mailMerge )
		    $menuItems .= $this->buildMergeLink();
		//END SUGARCRM flav!=sales ONLY
		if ( $this->mergeduplicates )
		    $menuItems .= $this->buildMergeDuplicatesLink();
		//BEGIN SUGARCRM flav!=sales ONLY
		// add to target list
		if ( isset($_REQUEST['module']) && in_array($_REQUEST['module'],array('Contacts','Prospects','Leads','Accounts'))
		&& ACLController::checkAccess('ProspectLists','edit',true)) {
		    $menuItems .= $this->buildTargetList();
		}
		//END SUGARCRM flav!=sales ONLY
		// export
		if ( ACLController::checkAccess($this->seed->module_dir,'export',true) && $this->export )
			$menuItems .= $this->buildExportLink();
		//BEGIN SUGARCRM flav=sales ONLY
		else if($this->seed->module_dir == 'Users' && $GLOBALS['current_user']->user_type == 'UserAdministrator')
			$menuItems .= $this->buildExportLink();
		//END SUGARCRM flav=sales ONLY

		foreach ( $this->actionsMenuExtraItems as $item )
		    $menuItems .= $item;

		$menuItems = str_replace('"','\"',$menuItems);

		if ( empty($menuItems) )
		    return '';

		return <<<EOHTML
<script type="text/javascript">
<!--
function actions_overlib()
{
    return overlib("{$menuItems}", CENTER, '', STICKY, MOUSEOFF, 3000, CLOSETEXT, "{$closeText}", WIDTH, 150,
        CLOSETITLE, "{$app_strings['LBL_ADDITIONAL_DETAILS_CLOSE_TITLE']}", CLOSECLICK,
        FGCLASS, 'olOptionsFgClass', CGCLASS, 'olOptionsCgClass', BGCLASS, 'olBgClass',
        TEXTFONTCLASS, 'olFontClass', CAPTIONFONTCLASS, 'olOptionsCapFontClass',
        CLOSEFONTCLASS, 'olOptionsCloseFontClass');
}
-->
</script>
<a id='$id' onclick='return actions_overlib();' href="#">
    {$app_strings['LBL_LINK_ACTIONS']}&nbsp;<img src='{$moreDetailImage}' border='0' />
</a>
EOHTML;
	}

	/**
	 * Builds the export link
	 *
	 * @return string HTML
	 */
	protected function buildExportLink()
	{
		global $app_strings;

		return "<a href='#' style='width: 150px' class='menuItem' onmouseover='hiliteItem(this,\"yes\");' onmouseout='unhiliteItem(this);' onclick=\"return sListView.send_form(true, '{$_REQUEST['module']}', 'index.php?entryPoint=export','{$app_strings['LBL_LISTVIEW_NO_SELECTED']}')\">{$app_strings['LBL_EXPORT']}</a>";
	}

	/**
	 * Builds the massupdate link
	 *
	 * @return string HTML
	 */
	protected function buildMassUpdateLink()
	{
		global $app_strings;

		return "<a href='#massupdate_form' style='width: 150px' class='menuItem' onmouseover='hiliteItem(this,\"yes\");' onmouseout='unhiliteItem(this);' onclick=\"document.getElementById('massupdate_form').style.display = '';\">{$app_strings['LBL_MASS_UPDATE']}</a>";
	}

	/**
	 * Builds the compose email link
	 *
	 * @return string HTML
	 */
	protected function buildComposeEmailLink(
	    $totalCount
	    )
	{
		global $app_strings,$dictionary;

        if (!is_array($this->seed->field_defs)) {
            return '';
        }
        $foundEmailField = false;
        // Search for fields that look like an email address
        foreach ($this->seed->field_defs as $field) {
            if(isset($field['type'])&&$field['type']=='link'
               &&isset($field['relationship'])&&isset($dictionary[$this->seed->object_name]['relationships'][$field['relationship']])
               &&$dictionary[$this->seed->object_name]['relationships'][$field['relationship']]['rhs_module']=='EmailAddresses') {
                $foundEmailField = true;
                break;
            }
        }
        if (!$foundEmailField) {
            return '';
        }


		$userPref = $GLOBALS['current_user']->getPreference('email_link_type');
		$defaultPref = $GLOBALS['sugar_config']['email_default_client'];
		if($userPref != '')
			$client = $userPref;
		else
			$client = $defaultPref;

		if($client == 'sugar')
			$script = "<a href='#' style='width: 150px' class='menuItem' onmouseover='hiliteItem(this,\"yes\");' onmouseout='unhiliteItem(this);' " .
					'onclick="return sListView.send_form_for_emails(true, \''."Emails".'\', \'index.php?module=Emails&action=Compose&ListView=true\',\''.$app_strings['LBL_LISTVIEW_NO_SELECTED'].'\', \''.$_REQUEST['module'].'\', \''.$totalCount.'\', \''.$app_strings['LBL_LISTVIEW_LESS_THAN_TEN_SELECT'].'\')">' .
					$app_strings['LBL_EMAIL_COMPOSE'] . '</a>';
		else
			$script = "<a href='#' style='width: 150px' class='menuItem' onmouseover='hiliteItem(this,\"yes\");' onmouseout='unhiliteItem(this);' " .
					'onclick="return sListView.use_external_mail_client(\''.$app_strings['LBL_LISTVIEW_NO_SELECTED'].'\');">' .
					$app_strings['LBL_EMAIL_COMPOSE'] . '</a>';

		return $script;
	} // fn
	/**
	 * Builds the delete link
	 *
	 * @return string HTML
	 */
	protected function buildDeleteLink()
	{
		global $app_strings;

		return "<a href='#' style='width: 150px' class='menuItem' onmouseover='hiliteItem(this,\"yes\");' onmouseout='unhiliteItem(this);' onclick=\"return sListView.send_mass_update('selected', '{$app_strings['LBL_LISTVIEW_NO_SELECTED']}', 1)\">{$app_strings['LBL_DELETE_BUTTON_LABEL']}</a>";
	}
	/**
	 * Display the selected object span object
	 *
     * @return string select object span
	 */
	function buildSelectedObjectsSpan($echo = true, $total=0) {
		global $app_strings;

		$selectedObjectSpan = "{$app_strings['LBL_LISTVIEW_SELECTED_OBJECTS']}<input  style='border: 0px; background: transparent; font-size: inherit; color: inherit' type='text' id='selectCountTop' readonly name='selectCount[]' value='{$total}' />";

        return $selectedObjectSpan;
	}
    /**
	 * Builds the mail merge link
	 * The link can be disabled by setting module level duplicate_merge property to false
	 * in the moudle's vardef file.
	 *
	 * @return string HTML
	 */
	protected function buildMergeDuplicatesLink()
	{
        global $app_strings, $dictionary;

        $return_string='';
        $return_string.= isset($_REQUEST['module']) ? "&return_module={$_REQUEST['module']}" : "";
        $return_string.= isset($_REQUEST['action']) ? "&return_action={$_REQUEST['action']}" : "";
        $return_string.= isset($_REQUEST['record']) ? "&return_id={$_REQUEST['record']}" : "";
        //need delete and edit access.
		if (!(ACLController::checkAccess( $_REQUEST['module'], 'edit', true)) or !(ACLController::checkAccess( $_REQUEST['module'], 'delete', true))) {
			return '';
		}

        if (isset($dictionary[$this->seed->object_name]['duplicate_merge']) && $dictionary[$this->seed->object_name]['duplicate_merge']==true ) {
            return "<a href='#' style='width: 150px' class='menuItem' onmouseover='hiliteItem(this,\"yes\");' onmouseout='unhiliteItem(this);' ".
                "onclick='if (sugarListView.get_checks_count()> 1) {sListView.send_form(true, \"MergeRecords\", \"index.php\", \"{$app_strings['LBL_LISTVIEW_NO_SELECTED']}\", \"{$this->seed->module_dir}\",\"$return_string\");} else {alert(\"{$app_strings['LBL_LISTVIEW_TWO_REQUIRED']}\");return false;}'>".
                $app_strings['LBL_MERGE_DUPLICATES'].'</a>';
        }

        return '';
     }
	//BEGIN SUGARCRM flav!=sales ONLY
    /**
	 * Builds the mail merge link
	 *
	 * @return string HTML
	 */
	protected function buildMergeLink()
	{
        require_once('modules/MailMerge/modules_array.php');
        global $current_user, $app_strings;

        $admin = new Administration();
        $admin->retrieveSettings('system');
        $user_merge = $current_user->getPreference('mailmerge_on');
       	$module_dir = (!empty($this->seed->module_dir) ? $this->seed->module_dir : '');
        $str = '';

        if ($user_merge == 'on' && isset($admin->settings['system_mailmerge_on']) && $admin->settings['system_mailmerge_on'] && !empty($modules_array[$module_dir])) {
            $str = "<a href='#' style='width: 150px' class='menuItem' onmouseover='hiliteItem(this,\"yes\");' onmouseout='unhiliteItem(this);' " .
					'onclick="if (document.MassUpdate.select_entire_list.value==1){document.location.href=\'index.php?action=index&module=MailMerge&entire=true\'} else {return sListView.send_form(true, \'MailMerge\',\'index.php\',\''.$app_strings['LBL_LISTVIEW_NO_SELECTED'].'\');}">' .
					$app_strings['LBL_MAILMERGE'].'</a>';
        }
        return $str;
	}

	/**
	 * Builds the add to target list link
	 *
     * @return string HTML
	 */
	protected function buildTargetList()
	{
        global $app_strings;
        $current_query_by_page = base64_encode(serialize($_REQUEST));

		$js = <<<EOF
            if(sugarListView.get_checks_count() < 1) {
                alert('{$app_strings['LBL_LISTVIEW_NO_SELECTED']}');
                return false;
            }
			if ( document.forms['targetlist_form'] ) {
				var form = document.forms['targetlist_form'];
				form.reset;
			} else
				var form = document.createElement ( 'form' ) ;
			form.setAttribute ( 'name' , 'targetlist_form' );
			form.setAttribute ( 'method' , 'post' ) ;
			form.setAttribute ( 'action' , 'index.php' );
			document.body.appendChild ( form ) ;
			if ( !form.module ) {
			    var input = document.createElement('input');
			    input.setAttribute ( 'name' , 'module' );
			    input.setAttribute ( 'value' , '{$_REQUEST['module']}' );
			    input.setAttribute ( 'type' , 'hidden' );
			    form.appendChild ( input ) ;
			    var input = document.createElement('input');
			    input.setAttribute ( 'name' , 'action' );
			    input.setAttribute ( 'value' , 'TargetListUpdate' );
			    input.setAttribute ( 'type' , 'hidden' );
			    form.appendChild ( input ) ;
			}
			if ( !form.uids ) {
			    var input = document.createElement('input');
			    input.setAttribute ( 'name' , 'uids' );
			    input.setAttribute ( 'type' , 'hidden' );
			    form.appendChild ( input ) ;
			}
			if ( !form.prospect_list ) {
			    var input = document.createElement('input');
			    input.setAttribute ( 'name' , 'prospect_list' );
			    input.setAttribute ( 'type' , 'hidden' );
			    form.appendChild ( input ) ;
			}
			if ( !form.return_module ) {
			    var input = document.createElement('input');
			    input.setAttribute ( 'name' , 'return_module' );
			    input.setAttribute ( 'type' , 'hidden' );
			    form.appendChild ( input ) ;
			}
			if ( !form.return_action ) {
			    var input = document.createElement('input');
			    input.setAttribute ( 'name' , 'return_action' );
			    input.setAttribute ( 'type' , 'hidden' );
			    form.appendChild ( input ) ;
			}
			if ( !form.select_entire_list ) {
			    var input = document.createElement('input');
			    input.setAttribute ( 'name' , 'select_entire_list' );
			    input.setAttribute ( 'value', document.MassUpdate.select_entire_list.value);
			    input.setAttribute ( 'type' , 'hidden' );
			    form.appendChild ( input ) ;
			}
			if ( !form.current_query_by_page ) {
			    var input = document.createElement('input');
			    input.setAttribute ( 'name' , 'current_query_by_page' );
			    input.setAttribute ( 'value', '{$current_query_by_page}' );
			    input.setAttribute ( 'type' , 'hidden' );
			    form.appendChild ( input ) ;
			}
			open_popup('ProspectLists','600','400','',true,false,{ 'call_back_function':'set_return_and_save_targetlist','form_name':'targetlist_form','field_to_name_array':{'id':'prospect_list'} } );
EOF;
        $js = str_replace(array("\r","\n"),'',$js);
        return "<a href='#' style='width: 150px' class='menuItem' onmouseover='hiliteItem(this,\"yes\");' onmouseout='unhiliteItem(this);' onclick=\"$js\">{$app_strings['LBL_ADD_TO_PROSPECT_LIST_BUTTON_LABEL']}</a>";
	}
	//END SUGARCRM flav!=sales ONLY
	/**
	 * Display the bottom of the ListView (ie MassUpdate
	 * @return string contents
	 */
	function displayEnd() {
		$str = '';
		if($this->show_mass_update_form) {
			$str .= $this->mass->getMassUpdateForm(true);
			$str .= $this->mass->endMassUpdateForm();
		}

		return $str;
	}

    /**
     * Display the multi select data box etc.
     * @return string contents
     */
	function getMultiSelectData() {
		$str = "<script>YAHOO.util.Event.addListener(window, \"load\", sListView.check_boxes);</script>\n";

		$massUpdateRun = isset($_REQUEST['massupdate']) && $_REQUEST['massupdate'] == 'true';
		$uids = empty($_REQUEST['uid']) || $massUpdateRun ? '' : $_REQUEST['uid'];
		$select_entire_list = isset($_REQUEST['select_entire_list']) && !$massUpdateRun ? $_REQUEST['select_entire_list'] : 0;

		$str .= "<textarea style='display: none' name='uid'>{$uids}</textarea>\n" .
				"<input type='hidden' name='select_entire_list' value='{$select_entire_list}'>\n".
				"<input type='hidden' name='{$this->moduleString}' value='0'>\n";
		return $str;
	}

}
?>
