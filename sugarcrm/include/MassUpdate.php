<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/**
 * MassUpdate for ListViews
 *
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-professional-eula.html
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
 * by SugarCRM are Copyright (C) 2004-2005 SugarCRM, Inc.; All Rights Reserved.
 */

// $Id: MassUpdate.php 56786 2010-06-02 18:29:56Z jenny $

/**
 * MassUpdate class for updating multiple records at once
 */
 require_once('include/EditView/EditView2.php');
class MassUpdate
{
	/*
	 * internal sugarbean reference
	 */
	var $sugarbean = null;

	/**
	  * set the sugar bean to its internal member
	  * @param sugar bean reference
	  */
	function setSugarBean($sugar)
	{
		$this->sugarbean = $sugar;
	}

	/**
	 * get the massupdate form
	 * @param bool boolean need to execute the massupdate form or not
	 * @param multi_select_popup booleanif it is a multi-select value
	 */
	function getDisplayMassUpdateForm($bool, $multi_select_popup = false)
	{

		require_once('include/formbase.php');

		if(!$multi_select_popup)
		$form = '<form action="index.php" method="post" name="displayMassUpdate" id="displayMassUpdate">' . "\n";
		else
		$form = '<form action="index.php" method="post" name="MassUpdate" id="MassUpdate">' . "\n";

		if($bool)
		{
			$form .= '<input type="hidden" name="mu" value="false" />' . "\n";
		}
		else
		{
			$form .= '<input type="hidden" name="mu" value="true" />' . "\n";
		}

		$form .= getAnyToForm('mu', true);
		if(!$multi_select_popup) $form .= "</form>\n";

		return $form;
	}
	/**
	 * returns the mass update's html form header
	 * @param multi_select_popup boolean if it is a mult-select or not
	 */
	function getMassUpdateFormHeader($multi_select_popup = false)
	{
		global $sugar_version;
		global $sugar_config;
		global $current_user;

		unset($_REQUEST['current_query_by_page']);
		unset($_REQUEST[session_name()]);
		unset($_REQUEST['PHPSESSID']);
		$query = base64_encode(serialize($_REQUEST));

        $bean = loadBean($_REQUEST['module']);
       $order_by_name = $bean->module_dir.'2_'.strtoupper($bean->object_name).'_ORDER_BY' ;
       $lvso = isset($_REQUEST['lvso'])?$_REQUEST['lvso']:"";
       $request_order_by_name = isset($_REQUEST[$order_by_name])?$_REQUEST[$order_by_name]:"";
       $action = isset($_REQUEST['action'])?$_REQUEST['action']:"";
       $module = isset($_REQUEST['module'])?$_REQUEST['module']:"";
		if($multi_select_popup)
		$tempString = '';
		else
		$tempString = "<form action='index.php' method='post' name='MassUpdate'  id='MassUpdate' onsubmit=\"return check_form('MassUpdate');\">\n"
		. "<input type='hidden' name='return_action' value='{$action}' />\n"
        . "<input type='hidden' name='return_module' value='{$module}' />\n"
		. "<input type='hidden' name='massupdate' value='true' />\n"
		. "<input type='hidden' name='delete' value='false' />\n"
		. "<input type='hidden' name='merge' value='false' />\n"
        . "<input type='hidden' name='current_query_by_page' value='{$query}' />\n"
        . "<input type='hidden' name='module' value='{$module}' />\n"
        . "<input type='hidden' name='action' value='MassUpdate' />\n"
        . "<input type='hidden' name='lvso' value='{$lvso}' />\n"
        . "<input type='hidden' name='{$order_by_name}' value='{$request_order_by_name}' />\n";

		// cn: bug 9103 - MU navigation in emails is broken
		if($_REQUEST['module'] == 'Emails') {
			$type = "";
			// determine "type" - inbound, archive, etc.
			if (isset($_REQUEST['type'])) {
				$type = $_REQUEST['type'];
			}
			// determine owner
			$tempString .=<<<eoq
				<input type='hidden' name='type' value="{$type}" />
				<input type='hidden' name='ie_assigned_user_id' value="{$current_user->id}" />
eoq;
		}

		return $tempString;
	}

	/**
	  * Executes the massupdate form
	  * @param displayname Name to display in the popup window
      * @param varname name of the variable
	  */
	function handleMassUpdate(){

		require_once('include/formbase.php');
		global $current_user, $db, $disable_date_format, $timedate;

		foreach($_POST as $post=>$value){
			if(is_array($value)){
				if(empty($value)){
					unset($_POST[$post]);
				}
			}elseif(strlen($value) == 0){
				if( isset($this->sugarbean->field_defs[$post]) && $this->sugarbean->field_defs[$post]['type'] == 'radioenum' && isset($_POST[$post]) ){
				  $_POST[$post] = '';
				}else{
				  unset($_POST[$post]);
			        }
                        }elseif ( $value == '--null--'){  //Bug36693 MassUpdate for ENUM with Option '0'
				$_POST[$post] = '';	
			}
			if(is_string($value) && isset($this->sugarbean->field_defs[$post])) {
		        if(($this->sugarbean->field_defs[$post]['type'] == 'bool'
				 	|| (!empty($this->sugarbean->field_defs[$post]['custom_type']) && $this->sugarbean->field_defs[$post]['custom_type'] == 'bool'
				 	))){
				 		if(strcmp($value, '2') == 0)$_POST[$post] = 0;
				 		if(!empty($this->sugarbean->field_defs[$post]['dbType']) && strcmp($this->sugarbean->field_defs[$post]['dbType'], 'varchar') == 0 ){
				 			if(strcmp($value, '1') == 0 )$_POST[$post] = 'on';
				 			if(strcmp($value, '2') == 0)$_POST[$post] = 'off';
				 		}
    			}

			    if( ($this->sugarbean->field_defs[$post]['type'] == 'radioenum' && isset($_POST[$post]) && strlen($value) == 0)
			    || ($this->sugarbean->field_defs[$post]['type'] == 'enum' && $value == '__SugarMassUpdateClearField__') // Set to '' if it's an explicit clear
			    ){
				    $_POST[$post] = '';
			    }
                if ($this->sugarbean->field_defs[$post]['type'] == 'bool') {
                    $this->checkClearField($post, $value);
                }
			    if($this->sugarbean->field_defs[$post]['type'] == 'date' && !empty($_POST[$post])){
			        $_POST[$post] = $timedate->to_db_date($_POST[$post], false);
			    }
                if($this->sugarbean->field_defs[$post]['type'] == 'datetime' && !empty($_POST[$post])){
			        $_POST[$post] = $timedate->to_db($this->date_to_dateTime($post, $value));
			    }
			    if($this->sugarbean->field_defs[$post]['type'] == 'datetimecombo' && !empty($_POST[$post])){
			        $_POST[$post] = $timedate->to_db($_POST[$post]);
			    }
            }
         }

		//We need to disable_date_format so that date values for the beans remain in database format
		//notice we make this call after the above section since the calls to TimeDate class there could wind up
		//making it's way to the UserPreferences objects in which case we want to enable the global date formatting
		//to correctly retrieve the user's date format preferences
		$old_value = $disable_date_format;
		$disable_date_format = true;

		if(!empty($_REQUEST['uid'])) $_POST['mass'] = explode(',', $_REQUEST['uid']); // coming from listview
		elseif(isset($_REQUEST['entire']) && empty($_POST['mass'])) {
			if(empty($order_by))$order_by = '';
			$ret_array = create_export_query_relate_link_patch($_REQUEST['module'], $this->searchFields, $this->where_clauses);
			if(!isset($ret_array['join'])) {
				$ret_array['join'] = '';
			}
			$query = $this->sugarbean->create_export_query($order_by, $ret_array['where'], $ret_array['join']);
			$result = $db->query($query,true);
			$new_arr = array();
			while($val = $db->fetchByAssoc($result,-1,false))
			{
				array_push($new_arr, $val['id']);
			}
			$_POST['mass'] = $new_arr;
		}

		if(isset($_POST['mass']) && is_array($_POST['mass'])  && $_REQUEST['massupdate'] == 'true'){
			$count = 0;

			//BEGIN SUGARCRM flav=pro ONLY
			if(isset($_SESSION['REASSIGN_TEAMS'])) {
			   unset($_SESSION['REASSIGN_TEAMS']);
			}
			//END SUGARCRM flav=pro ONLY

			foreach($_POST['mass'] as $id){
                if(empty($id)) {
                    continue;
                }
				if(isset($_POST['Delete'])){
					$this->sugarbean->retrieve($id);
					if($this->sugarbean->ACLAccess('Delete')){
					//Martin Hu Bug #20872
						if($this->sugarbean->object_name == 'EmailMan'){
							$query = "DELETE FROM emailman WHERE id = '" . $this->sugarbean->id . "'";
							$db->query($query);
						} else {

							//BEGIN SUGARCRM flav=pro ONLY
						    if ($this->sugarbean->object_name == 'Team' && $this->sugarbean->has_records_in_modules()) {
                                if(!isset($_SESSION['REASSIGN_TEAMS'])) {
						    	   $_SESSION['REASSIGN_TEAMS'] = array();
                                }
                                $_SESSION['REASSIGN_TEAMS'][] = $this->sugarbean->id;
						    	continue;
						    }
						    //END SUGARCRM flav=pro ONLY
							$this->sugarbean->mark_deleted($id);
						}
					}
				}
				else {
					if($this->sugarbean->object_name == 'Contact' && isset($_POST['Sync'])){ // special for contacts module
						if($_POST['Sync'] == 'true') {
							$this->sugarbean->retrieve($id);
							if($this->sugarbean->ACLAccess('Save')){
								if($this->sugarbean->object_name == 'Contact'){

									$this->sugarbean->contacts_users_id = $current_user->id;
									$this->sugarbean->save(false);
								}
							}
						}
						elseif($_POST['Sync'] == 'false') {
							$this->sugarbean->retrieve($id);
							if($this->sugarbean->ACLAccess('Save')){
								if($this->sugarbean->object_name == 'Contact'){
									if (!isset($this->sugarbean->users))
									{
										$this->sugarbean->load_relationship('user_sync');
									}
									$this->sugarbean->contacts_users_id = null;
									$this->sugarbean->user_sync->delete($this->sugarbean->id, $current_user->id);
								}
							}
						}
					} //end if for special Contact handling

					if($count++ != 0) {
					   //Create a new instance to clear values and handle additional updates to bean's 2,3,4...
                       $className = get_class($this->sugarbean);
                       $this->sugarbean = new $className();
					}

					$this->sugarbean->retrieve($id);

					//BEGIN SUGARCRM flav=pro ONLY
					////////////////////////
					//IS USER OFFLINE CLIENT ENABLED
					if($this->sugarbean->object_name == 'User' && isset($_POST['oc_status'])){
						$this->sugarbean->setPreference('OfflineClientStatus',$_POST['oc_status']);
					}
					//
					////////////////////////
					//END SUGARCRM flav=pro ONLY

					if($this->sugarbean->ACLAccess('Save')){
						$_POST['record'] = $id;
						$_GET['record'] = $id;
						$_REQUEST['record'] = $id;
						$newbean=$this->sugarbean;

						$old_reports_to_id = null;
						if(!empty($_POST['reports_to_id']) && $newbean->reports_to_id != $_POST['reports_to_id']) {
						   $old_reports_to_id = empty($newbean->reports_to_id) ? 'null' : $newbean->reports_to_id;
						}

						$check_notify = FALSE;

						if (isset( $this->sugarbean->assigned_user_id)) {
							$old_assigned_user_id = $this->sugarbean->assigned_user_id;
							if (!empty($_POST['assigned_user_id'])
							&& ($old_assigned_user_id != $_POST['assigned_user_id'])
							&& ($_POST['assigned_user_id'] != $current_user->id)) {
								$check_notify = TRUE;
							}
						}

						//Call include/formbase.php, but do not call retrieve again
						populateFromPost('', $newbean, true);
						$newbean->save_from_post = false;

						if (!isset($_POST['parent_id'])) {
							$newbean->parent_type = null;
						}

						$email_address_id = '';
	                    if (!empty($_POST['optout_primary'])) {
	                    	$optout_flag_value = 0;
	                    	if ($_POST['optout_primary'] == 'true') {
	                    		$optout_flag_value = 1;
	                    	} // if
	                    	if (isset($this->sugarbean->emailAddress)) {
	                    		if (!empty($this->sugarbean->emailAddress->addresses)) {
	                    			foreach($this->sugarbean->emailAddress->addresses as $key =>$emailAddressRow) {
	                    				if ($emailAddressRow['primary_address'] == '1') {
	                    					$email_address_id = $emailAddressRow['email_address_id'];
	                    					break;
										} // if
									} // foreach
								} // if

							} // if
	                    } // if


						$newbean->save($check_notify);
						if (!empty($email_address_id)) {
	    					$query = "UPDATE email_addresses SET opt_out = {$optout_flag_value} where id = '{$emailAddressRow['email_address_id']}'";
	    					$GLOBALS['db']->query($query);

						} // if

						if(!empty($old_reports_to_id) && method_exists($newbean, 'update_team_memberships')) {
						   $old_id = $old_reports_to_id == 'null' ? '' : $old_reports_to_id;
						   //BEGIN SUGARCRM flav=pro ONLY
						   $newbean->update_team_memberships($old_id);
						   //END SUGARCRM flav=pro ONLY
						}
					}
				}
			}

			//BEGIN SUGARCRM flav=pro ONLY
			if(isset($_SESSION['REASSIGN_TEAMS'])) {
			   header("Location: index.php?module=Teams&action=ReassignTeams");
			   die();
			}
			//END SUGARCRM flav=pro ONLY
		}
		$disable_date_format = $old_value;
	}
	/**
  	  * Displays the massupdate form
  	  */
	function getMassUpdateForm(
	    $hideDeleteIfNoFieldsAvailable = false
	    )
	{
		global $app_strings;
		global $current_user;

		if($this->sugarbean->bean_implements('ACL') && ( !ACLController::checkAccess($this->sugarbean->module_dir, 'edit', true) || !ACLController::checkAccess($this->sugarbean->module_dir, 'massupdate', true) ) ){
			return '';
		}
		
		$lang_delete = translate('LBL_DELETE');
		$lang_update = translate('LBL_UPDATE');
		$lang_confirm= translate('NTC_DELETE_CONFIRMATION_MULTIPLE');
		$lang_sync = translate('LBL_SYNC_CONTACT');
		$lang_oc_status = translate('LBL_OC_STATUS');
		$lang_unsync = translate('LBL_UNSYNC');
		$lang_archive = translate('LBL_ARCHIVE');
		$lang_optout_primaryemail = $app_strings['LBL_OPT_OUT_FLAG_PRIMARY'];

		$field_count = 0;
		
		$html = "<div id='massupdate_form' style='display:none;'><table width='100%' cellpadding='0' cellspacing='0' border='0' class='formHeader h3Row'><tr><td nowrap><h3><span>" . $app_strings['LBL_MASS_UPDATE']."</h3></td></tr></table>";
		$html .= "<div id='mass_update_div'><table cellpadding='0' cellspacing='1' border='0' width='100%' class='edit view' id='mass_update_table'>";

		$even = true;
		
		if($this->sugarbean->object_name == 'Contact')
		{
			$html .= "<tr><td width='15%' scope='row'>$lang_sync</td><td width='35%' class='dataField'><select name='Sync'><option value=''>{$GLOBALS['app_strings']['LBL_NONE']}</option><option value='false'>{$GLOBALS['app_list_strings']['checkbox_dom']['2']}</option><option value='true'>{$GLOBALS['app_list_strings']['checkbox_dom']['1']}</option></select></td>";
			$even = false;
		} else if($this->sugarbean->object_name == 'Employee') {
			$this->sugarbean->field_defs['employee_status']['type'] = 'enum';
			$this->sugarbean->field_defs['employee_status']['massupdate'] = true;
			$this->sugarbean->field_defs['employee_status']['options'] = 'employee_status_dom';
		} else if($this->sugarbean->object_name == 'InboundEmail'){
			$this->sugarbean->field_defs['status']['type'] = 'enum';
			$this->sugarbean->field_defs['status']['options'] = 'user_status_dom';
		}

		//These fields should never appear on mass update form
		static $banned = array('date_modified'=>1, 'date_entered'=>1, 'created_by'=>1, 'modified_user_id'=>1, 'deleted'=>1,'modified_by_name'=>1,);
		
		foreach($this->sugarbean->field_defs as $field)
		{
			//BEGIN SUGARCRM flav=pro ONLY
			   if(ACLField::hasAccess($field['name'], $this->sugarbean->module_dir, $GLOBALS['current_user']->id, false)  < 2)
			   {
			   	  continue;
			   }
			//END SUGARCRM flav=pro ONLY
			 if(!isset($banned[$field['name']]) && (!isset($field['massupdate']) || !empty($field['massupdate'])))
			 {
				$newhtml = '';
				
				if($even)
				{
					$newhtml .= "<tr>";
				}
				
				if(isset($field['vname']))
				{
					$displayname = translate($field['vname']);
				}else{
					$displayname = '';
				}
				
				if(isset($field['type']) && $field['type'] == 'relate' && isset($field['id_name']) && $field['id_name'] == 'assigned_user_id')
				{
					$field['type'] = 'assigned_user_name';
				}
				
				if(isset($field['custom_type']))
				{
					$field['type'] = $field['custom_type'];
				}
				
				if(isset($field['type']))
				{
					switch($field["type"])
					{
						case "relate":
    						    // bug 14691: avoid laying out an empty cell in the <table>
    							$handleRelationship = $this->handleRelationship($displayname, $field);
    							if ($handleRelationship != '') 
    							{
    								$even = !$even;
    								$newhtml .= $handleRelationship;
    							}
							break;
						case "parent":$even = !$even; $newhtml .=$this->addParent($displayname, $field); break;
						case "int":
							if(!empty($field['massupdate']) && empty($field['auto_increment']))
							{
								$even = !$even; $newhtml .=$this->addInputType($displayname, $field);
							}
							 break;
						case "contact_id":$even = !$even; $newhtml .=$this->addContactID($displayname, $field["name"]); break;
						case "assigned_user_name":$even = !$even; $newhtml .= $this->addAssignedUserID($displayname,  $field["name"]); break;
						case "account_id":$even = !$even; $newhtml .= $this->addAccountID($displayname,  $field["name"]); break;
						case "account_name":$even = !$even; $newhtml .= $this->addAccountID($displayname,  $field["id_name"]); break;
						case "bool": $even = !$even; $newhtml .= $this->addBool($displayname,  $field["name"]); break;
						case "enum":
						case "multienum":
							if(!empty($field['isMultiSelect']))
							{
								$even = !$even; $newhtml .= $this->addStatusMulti($displayname,  $field["name"], translate($field["options"])); break;
							}else if(!empty($field['options'])) {
								$even = !$even; $newhtml .= $this->addStatus($displayname,  $field["name"], translate($field["options"])); break;
							}else if(!empty($field['function'])){
								$functionValue = $this->getFunctionValue($this->sugarbean, $field);
								$even = !$even; $newhtml .= $this->addStatus($displayname,  $field["name"], $functionValue); break;
							}
							break;
						case "radioenum":
						$even = !$even; $newhtml .= $this->addRadioenum($displayname,  $field["name"] , translate($field["options"])); break;
						case "datetimecombo":
						$even = !$even; $newhtml .= $this->addDatetime($displayname,  $field["name"]); break;
						case "datetime":
						case "date":$even = !$even; $newhtml .= $this->addDate($displayname,  $field["name"]); break;
						//BEGIN SUGARCRM flav=pro ONLY
						case "team_list": $teamhtml = $this->addTeamList(translate('LBL_TEAMS'),  $field); break;
						//END SUGARCRM flav=pro ONLY
					}
				}
				
				if($even)
				{
					$newhtml .="</tr>";
				}
					
				$field_count++;
				
				if(!in_array($newhtml, array('<tr>', '</tr>', '<tr></tr>', '<tr><td></td></tr>'))){
					$html.=$newhtml;
				}
			}
		}

		//BEGIN SUGARCRM flav=pro ONLY
		if(isset($teamhtml)) 
		{
			if(!$even)
			{
				$teamhtml .="</tr>";
			}
			
		    if(!in_array($teamhtml, array('<tr>', '</tr>', '<tr></tr>', '<tr><td></td></tr>')))
		    {
		       $html.=$teamhtml;
		    }
		    
		    $field_count++;
		}
		//END SUGARCRM flav=pro ONLY

		if ($this->sugarbean->object_name == 'Contact' ||
			$this->sugarbean->object_name == 'Account' ||
			$this->sugarbean->object_name == 'Lead' ||
			$this->sugarbean->object_name == 'Prospect') {

			$html .= "<tr><td width='15%'  scope='row' class='dataLabel'>$lang_optout_primaryemail</td><td width='35%' class='dataField'><select name='optout_primary'><option value=''>{$GLOBALS['app_strings']['LBL_NONE']}</option><option value='false'>{$GLOBALS['app_list_strings']['checkbox_dom']['2']}</option><option value='true'>{$GLOBALS['app_list_strings']['checkbox_dom']['1']}</option></select></td></tr>";

			}
		$html .="</table>";

		 $html .= "<table cellpadding='0' cellspacing='0' border='0' width='100%'><tr><td class='buttons'><input onclick='return sListView.send_mass_update(\"selected\", \"{$app_strings['LBL_LISTVIEW_NO_SELECTED']}\")' type='submit' id='update_button' name='Update' value='{$lang_update}' class='button'>&nbsp;<input onclick='javascript:toggleMassUpdateForm();' type='button' id='cancel_button' name='Cancel' value='{$GLOBALS['app_strings']['LBL_CANCEL_BUTTON_LABEL']}' class='button'>";
		// TODO: allow ACL access for Delete to be set false always for users
//		if($this->sugarbean->ACLAccess('Delete', true) && $this->sugarbean->object_name != 'User') {
//			global $app_list_strings;
//			$html .=" <input id='delete_button' type='submit' name='Delete' value='{$lang_delete}' onclick='return confirm(\"{$lang_confirm}\") && sListView.send_mass_update(\"selected\", \"{$app_strings['LBL_LISTVIEW_NO_SELECTED']}\", 1)' class='button'>";
//		}

		// only for My Inbox views - to allow CSRs to have an "Archive" emails feature to get the email "out" of their inbox.
		if($this->sugarbean->object_name == 'Email'
		&& (isset($_REQUEST['assigned_user_id']) && !empty($_REQUEST['assigned_user_id']))
		&& (isset($_REQUEST['type']) && !empty($_REQUEST['type']) && $_REQUEST['type'] == 'inbound')) {
			$html .=<<<eoq
			<input type='button' name='archive' value="{$lang_archive}" class='button' onClick='setArchived();'>
			<input type='hidden' name='ie_assigned_user_id' value="{$current_user->id}">
			<input type='hidden' name='ie_type' value="inbound">
eoq;
		}

		$html .= "</td></tr></table></div></div>";

		$html .= <<<EOJS
<script>
function toggleMassUpdateForm(){
    document.getElementById('massupdate_form').style.display = 'none';
}
</script>
EOJS;

		if($field_count > 0)
		{
			return $html;
		}else{
			//If no fields are found, render either a form that still permits Mass Update deletes or just display a message that no fields are available
			$html = "<div id='massupdate_form' style='display:none;'><table width='100%' cellpadding='0' cellspacing='0' border='0' class='formHeader h3Row'><tr><td nowrap><h3><span>" . $app_strings['LBL_MASS_UPDATE']."</h3></td></tr></table>";
			if($this->sugarbean->ACLAccess('Delete', true) && !$hideDeleteIfNoFieldsAvailable){
				$html .= "<table cellpadding='0' cellspacing='0' border='0' width='100%'><tr><td><input type='submit' name='Delete' value='$lang_delete' onclick=\"return confirm('{$lang_confirm}')\" class='button'></td></tr></table></div>";
			}else{
				$html .= $app_strings['LBL_NO_MASS_UPDATE_FIELDS_AVAILABLE'] . "</div>";
			}
			return $html;
		}
	}

	function getFunctionValue($focus, $vardef){
		$function = $vardef['function'];
	    if(is_array($function) && isset($function['name'])){
	    	$function = $vardef['function']['name'];
	    }else{
	       	$function = $vardef['function'];
	    }
		if(!empty($vardef['function']['returns']) && $vardef['function']['returns'] == 'html'){
			if(!empty($vardef['function']['include'])){
				require_once($vardef['function']['include']);
			}
			return $function($focus, $vardef['name'], '', 'MassUpdate');
		}else{
			return $function($focus, $vardef['name'], '', 'MassUpdate');
		}
	}

	/**
	  * Returns end of the massupdate form
	  */
	function endMassUpdateForm(){
		return '</form>';
	}

	/**
	  * Decides which popup HTML code is needed for mass updating
	  * @param displayname Name to display in the popup window
	  * @param field name of the field to update
	  */
	function handleRelationship($displayname, $field)
	{
		$ret_val = '';
		if(isset($field['module']))
		{
			if ($field['name'] == 'reports_to_name' && ($field['module'] == 'Users' || $field['module'] == 'Employee') )
			    return $this->addUserName($displayname, $field['name'], $field['id_name'], $field['module']);

		    switch($field['module'])
			{
				case 'Accounts':
					$ret_val = $this->addAccountID($displayname, $field['name'], $field['id_name']);
					break;
				case 'Contacts':
					$ret_val = $this->addGenericModuleID($displayname, $field['name'], $field['id_name'], "Contacts");
					break;
				case 'Users':
					$ret_val = $this->addGenericModuleID($displayname, $field['name'], $field['id_name'], "Users");
					break;
				case 'Employee':
					$ret_val = $this->addGenericModuleID($displayname, $field['name'], $field['id_name'], "Employee");
					break;
				case 'Releases':
					$ret_val = $this->addGenericModuleID($displayname, $field['name'], $field['id_name'], "Releases");
					break;
				default:
					if(!empty($field['massupdate'])){
						$ret_val = $this->addGenericModuleID($displayname, $field['name'], $field['id_name'], $field['module']);
					}
					break;
			}
		}

		return $ret_val;
	}
	/**
	  * Add a parent selection popup window
	  * @param displayname Name to display in the popup window
	  * @param field_name name of the field
	  */
	function addParent($displayname, $field){
		global $app_strings, $app_list_strings;

		///////////////////////////////////////
		///
		/// SETUP POPUP

		$popup_request_data = array(
		'call_back_function' => 'set_return',
		'form_name' => 'MassUpdate',
		'field_to_name_array' => array(
			'id' => "parent_id",
			'name' => "parent_name",
			),
			);

			$json = getJSONobj();
			$encoded_popup_request_data = $json->encode($popup_request_data);

            $qsName = array(
			            'form' => 'MassUpdate',
						'method' => 'query',
                        'modules' => array("Accounts"),
                        'group' => 'or',
						'field_list' => array('name', 'id'),
						'populate_list' => array("mass_parent_name", "mass_parent_id"),
						'conditions' => array(array('name'=>'name','op'=>'like_custom','end'=>'%','value'=>'')),
						'limit' => '30','no_match_text' => $app_strings['ERR_SQS_NO_MATCH']);
                $qsName = $json->encode($qsName);

			//
			///////////////////////////////////////

			$change_parent_button = "<span class='id-ff'><button title='".$app_strings['LBL_SELECT_BUTTON_TITLE']."' accessKey='".$app_strings['LBL_SELECT_BUTTON_KEY']."'  type='button' class='button' value='".$app_strings['LBL_SELECT_BUTTON_LABEL']
			."' name='button_parent_name' onclick='open_popup(document.MassUpdate.{$field['type_name']}.value, 600, 400, \"\", true, false, {$encoded_popup_request_data});'><img src='".SugarThemeRegistry::current()->getImageURL('id-ff-select.png')."'></button></span>";
			$parent_type = $field['parent_type'];
            $parent_types = $app_list_strings[$parent_type];
            $disabled_parent_types = ACLController::disabledModuleList($parent_types,false, 'list');
            foreach($disabled_parent_types as $disabled_parent_type) {
			    unset($parent_types[$disabled_parent_type]);
            }
			$types = get_select_options_with_id($parent_types, '');
			//BS Fix Bug 17110
			$pattern = "/\n<OPTION.*".$app_strings['LBL_NONE']."<\/OPTION>/";
			$types = preg_replace($pattern, "", $types);
			// End Fix

            $json = getJSONobj();
            $disabled_parent_types = $json->encode($disabled_parent_types);

			return <<<EOHTML
<td width="15%" scope="row">{$displayname} </td>
<td>
    <table width='100%' border='0' cellspacing='0' cellpadding='0'>
    <tr>
        <td valign='top'>
            <select name='{$field['type_name']}' id='mass_{$field['type_name']}'>
                $types
            </select>
        </td>
        <td valign='top'>
			<input name='{$field['id_name']}' id='mass_{$field['id_name']}' type='hidden' value=''>
			<input name='parent_name' id='mass_parent_name' class='sqsEnabled' autocomplete='off'
                type='text' value=''>
            $change_parent_button
        </td>
    </tr>
    </table>
</td>
<script type="text/javascript">
<!--
var disabledModules='{$disabled_parent_types}';
if(typeof sqs_objects == 'undefined'){
    var sqs_objects = new Array;
}
sqs_objects['MassUpdate_parent_name'] = $qsName;
registerSingleSmartInputListener(document.getElementById('mass_parent_name'));
addToValidateBinaryDependency('MassUpdate', 'parent_name', 'alpha', false, '{$app_strings['ERR_SQS_NO_MATCH_FIELD']} {$app_strings['LBL_ASSIGNED_TO']}','parent_id');

document.getElementById('mass_{$field['type_name']}').onchange = function()
{
    document.MassUpdate.parent_name.value="";
    document.MassUpdate.parent_id.value="";

	new_module = document.forms["MassUpdate"].elements["parent_type"].value;

	if(typeof(disabledModules[new_module]) != 'undefined') {
		sqs_objects["MassUpdate_parent_name"]["disable"] = true;
		document.forms["MassUpdate"].elements["parent_name"].readOnly = true;
	} else {
		sqs_objects["MassUpdate_parent_name"]["disable"] = false;
		document.forms["MassUpdate"].elements["parent_name"].readOnly = false;
	}
	sqs_objects["MassUpdate_parent_name"]["modules"] = new Array(new_module);
    enableQS(false);

    checkParentType(document.MassUpdate.parent_type.value, document.MassUpdate.button_parent_name);
}
-->
</script>
EOHTML;
	}

	/**
	  * Add a generic input type='text' field
	  * @param displayname Name to display in the popup window
	  * @param field_name name of the field
	  */
	function addInputType($displayname, $varname){
		$html = <<<EOQ
	<td scope="row" width="20%">$displayname</td>
	<td class='dataField' width="30%"><input type="text" name='$varname' size="12" id='{$varname}' maxlength='10' value=""></td>
	<script> addToValidate('MassUpdate','$varname','int',false,'$displayname');</script>
EOQ;
		return $html;

	}

    /**
	  * Add a generic widget to lookup Users.
	  * @param displayname Name to display in the popup window
	  * @param varname name of the variable
	  * @param id_name name of the id in vardef
	  * @param mod_type name of the module, either "Contact" or "Releases" currently
	  */
	function addUserName($displayname, $varname, $id_name='', $mod_type){
		global $app_strings;

		if(empty($id_name))
		$id_name = strtolower($mod_type)."_id";

		///////////////////////////////////////
		///
		/// SETUP POPUP
        $reportsDisplayName = showFullName() ? 'name' : 'user_name';
		$popup_request_data = array(
			'call_back_function' => 'set_return',
			'form_name' => 'MassUpdate',
			'field_to_name_array' => array(
				'id' => "{$id_name}",
				"$reportsDisplayName" => "{$varname}",
				),
				);

				$json = getJSONobj();
				$encoded_popup_request_data = $json->encode($popup_request_data);

                $qsName = array(
			            'form' => 'MassUpdate',
						'method' => 'get_user_array',
                        'modules' => array("{$mod_type}"),
                        'group' => 'or',
						'field_list' => array('user_name', 'id'),
						'populate_list' => array("mass_{$varname}", "mass_{$id_name}"),
						'conditions' => array(array('name'=>'name','op'=>'like_custom','end'=>'%','value'=>'')),
						'limit' => '30','no_match_text' => $app_strings['ERR_SQS_NO_MATCH']);
                $qsName = $json->encode($qsName);
				//
				///////////////////////////////////////

            return <<<EOHTML
<td width='15%'  scope='row' class='dataLabel'>$displayname</td>
<td width='35%' class='dataField'>
    <input name='{$varname}' id='mass_{$varname}' class='sqsEnabled' autocomplete='off' type='text' value=''>
    <input name='{$id_name}' id='mass_{$id_name}' type='hidden' value=''>&nbsp;
    <input title='{$app_strings['LBL_SELECT_BUTTON_TITLE']}'
        accessKey='{$app_strings['LBL_SELECT_BUTTON_KEY']}'
        type='button' class='button' value='{$app_strings['LBL_SELECT_BUTTON_LABEL']}' name='button'
        onclick='open_popup("$mod_type", 600, 400, "", true, false, {$encoded_popup_request_data});'
        />
</td>
<script type="text/javascript">
<!--
if(typeof sqs_objects == 'undefined'){
    var sqs_objects = new Array;
}
sqs_objects['MassUpdate_{$varname}'] = $qsName;
registerSingleSmartInputListener(document.getElementById('mass_{$varname}'));
addToValidateBinaryDependency('MassUpdate', '{$varname}', 'alpha', false, '{$app_strings['ERR_SQS_NO_MATCH_FIELD']} {$app_strings['LBL_ASSIGNED_TO']}','{$id_name}');
-->
</script>
EOHTML;
	}


	/**
	  * Add a generic module popup selection popup window HTML code.
	  * Currently supports Contact and Releases
	  * @param displayname Name to display in the popup window
	  * @param varname name of the variable
	  * @param id_name name of the id in vardef
	  * @param mod_type name of the module, either "Contact" or "Releases" currently
	  */
	function addGenericModuleID($displayname, $varname, $id_name='', $mod_type){
		global $app_strings;

		if(empty($id_name))
		$id_name = strtolower($mod_type)."_id";

		///////////////////////////////////////
		///
		/// SETUP POPUP

		$popup_request_data = array(
			'call_back_function' => 'set_return',
			'form_name' => 'MassUpdate',
			'field_to_name_array' => array(
				'id' => "{$id_name}",
				'name' => "{$varname}",
				),
				);

				$json = getJSONobj();
				$encoded_popup_request_data = $json->encode($popup_request_data);

                $qsName = array(
			            'form' => 'MassUpdate',
						'method' => 'query',
                        'modules' => array("{$mod_type}"),
                        'group' => 'or',
						'field_list' => array('name', 'id'),
						'populate_list' => array("mass_{$varname}", "mass_{$id_name}"),
						'conditions' => array(array('name'=>'name','op'=>'like_custom','end'=>'%','value'=>'')),
						'limit' => '30','no_match_text' => $app_strings['ERR_SQS_NO_MATCH']);
                $qsName = $json->encode($qsName);
				$img = SugarThemeRegistry::current()->getImageURL("id-ff-select.png");
				//
				///////////////////////////////////////

            return <<<EOHTML
<td width='15%'  scope='row' class='dataLabel'>$displayname</td>
<td width='35%' class='dataField'>
    <input name='{$varname}' id='mass_{$varname}' class='sqsEnabled' autocomplete='off' type='text' value=''>
    <input name='{$id_name}' id='mass_{$id_name}' type='hidden' value=''>
	<span class="id-ff multiple">
    <button title='{$app_strings['LBL_SELECT_BUTTON_TITLE']}'
        accessKey='{$app_strings['LBL_SELECT_BUTTON_KEY']}'
        type='button' class='button' value='{$app_strings['LBL_SELECT_BUTTON_LABEL']}' name='button'
        onclick='open_popup("$mod_type", 600, 400, "", true, false, {$encoded_popup_request_data});'
        /><img src="$img"></button></span>
</td>
<script type="text/javascript">
<!--
if(typeof sqs_objects == 'undefined'){
    var sqs_objects = new Array;
}
sqs_objects['MassUpdate_{$varname}'] = $qsName;
registerSingleSmartInputListener(document.getElementById('mass_{$varname}'));
addToValidateBinaryDependency('MassUpdate', '{$varname}', 'alpha', false, '{$app_strings['ERR_SQS_NO_MATCH_FIELD']} {$app_strings['LBL_ASSIGNED_TO']}','{$id_name}');
-->
</script>
EOHTML;
	}
	/**
	  * Add Account selection popup window HTML code
	  * @param displayname Name to display in the popup window
	  * @param varname name of the variable
	  * @param id_name name of the id in vardef
	  */
	function addAccountID($displayname, $varname, $id_name=''){
		global $app_strings;

		$json = getJSONobj();

		if(empty($id_name))
		$id_name = "account_id";

		///////////////////////////////////////
		///
		/// SETUP POPUP

		$popup_request_data = array(
			'call_back_function' => 'set_return',
			'form_name' => 'MassUpdate',
			'field_to_name_array' => array(
				'id' => "{$id_name}",
				'name' => "{$varname}",
				),
				);

				$encoded_popup_request_data = $json->encode($popup_request_data);

				//
				///////////////////////////////////////

				$qsParent = array(
							'form' => 'MassUpdate',
							'method' => 'query',
							'modules' => array('Accounts'),
							'group' => 'or',
							'field_list' => array('name', 'id'),
							'populate_list' => array('parent_name', 'parent_id'),
							'conditions' => array(array('name'=>'name','op'=>'like_custom','end'=>'%','value'=>'')),
							'order' => 'name',
							'limit' => '30',
							'no_match_text' => $app_strings['ERR_SQS_NO_MATCH']
							);
							$qsParent['populate_list'] = array('mass_'. $varname, 'mass_' . $id_name);
							$img = SugarThemeRegistry::current()->getImageURL("id-ff-select.png");
							$html = '<td scope="row">' . $displayname . " </td>\n"
							. '<td><input class="sqsEnabled" type="text" autocomplete="off" id="mass_' . $varname .'" name="' . $varname . '" value="" /><input id="mass_' . $id_name . '" type="hidden" name="'
							. $id_name . '" value="" />&nbsp;<span class="id-ff multiple"><button type="button" name="btn1" class="button" title="'
							. $app_strings['LBL_SELECT_BUTTON_LABEL'] . '" accesskey="'
							. $app_strings['LBL_SELECT_BUTTON_KEY'] . '" value="' . $app_strings['LBL_SELECT_BUTTON_LABEL'] . '" onclick='
							. "'open_popup(\"Accounts\",600,400,\"\",true,false,{$encoded_popup_request_data});' /><img src=\"$img\"></button></span></td>\n";
							$html .= '<script type="text/javascript" language="javascript">if(typeof sqs_objects == \'undefined\'){var sqs_objects = new Array;}sqs_objects[\'MassUpdate_' . $varname . '\'] = ' .
							$json->encode($qsParent) . '; registerSingleSmartInputListener(document.getElementById(\'mass_' . $varname . '\'));
					addToValidateBinaryDependency(\'MassUpdate\', \''.$varname.'\', \'alpha\', false, \'' . $app_strings['ERR_SQS_NO_MATCH_FIELD'] . $app_strings['LBL_ACCOUNT'] . '\',\''.$id_name.'\');
					</script>';

							return $html;
	}

	//BEGIN SUGARCRM flav=pro ONLY
	/**
	  * Add Team selection popup window HTML code
	  * @param displayname Name to display in the popup window
	  * @param varname name of the variable
	  */
	function addTeamList($displayname, $field){
		require_once('include/SugarFields/SugarFieldHandler.php');
		$sfh = new SugarFieldHandler();
		$field['custom_type'] = 'Teamset';
		$field['name'] = 'team_name';
		$html = "<td width='15%' scope='row'>$displayname</td>";
		$html .= "<td>".$sfh->displaySmarty('fields', $field, 'MassUpdateView')."</td>";
		return $html;
	}
	//END SUGARCRM flav=pro ONLY
	/**
	  * Add AssignedUser popup window HTML code
	  * @param displayname Name to display in the popup window
	  * @param varname name of the variable
	  */
	function addAssignedUserID($displayname, $varname){
		global $app_strings;

		$json = getJSONobj();

		$popup_request_data = array(
		'call_back_function' => 'set_return',
		'form_name' => 'MassUpdate',
		'field_to_name_array' => array(
			'id' => 'assigned_user_id',
			'user_name' => 'assigned_user_name',
			),
			);
			$encoded_popup_request_data = $json->encode($popup_request_data);
			$qsUser = array(
			            'form' => 'MassUpdate',
						'method' => 'get_user_array', // special method
						'field_list' => array('user_name', 'id'),
						'populate_list' => array('assigned_user_name', 'assigned_user_id'),
						'conditions' => array(array('name'=>'user_name','op'=>'like_custom','end'=>'%','value'=>'')),
						'limit' => '30','no_match_text' => $app_strings['ERR_SQS_NO_MATCH']);

						$qsUser['populate_list'] = array('mass_assigned_user_name', 'mass_assigned_user_id');
						$img = SugarThemeRegistry::current()->getImageURL("id-ff-select.png");
						$html = <<<EOQ
		<td width="15%" scope="row">$displayname</td>
		<td ><input class="sqsEnabled" autocomplete="off" id="mass_assigned_user_name" name='assigned_user_name' type="text" value=""><input id='mass_assigned_user_id' name='assigned_user_id' type="hidden" value="" />
		<span class="id-ff multiple"><button title="{$app_strings['LBL_SELECT_BUTTON_TITLE']}" accessKey="{$app_strings['LBL_SELECT_BUTTON_KEY']}" type="button" class="button" value='{$app_strings['LBL_SELECT_BUTTON_LABEL']}' name=btn1
				onclick='open_popup("Users", 600, 400, "", true, false, $encoded_popup_request_data);' /><img src="$img"></button></span>
		</td>
EOQ;
						$html .= '<script type="text/javascript" language="javascript">if(typeof sqs_objects == \'undefined\'){var sqs_objects = new Array;}sqs_objects[\'MassUpdate_assigned_user_name\'] = ' .
						$json->encode($qsUser) . '; registerSingleSmartInputListener(document.getElementById(\'mass_assigned_user_name\'));
				addToValidateBinaryDependency(\'MassUpdate\', \'assigned_user_name\', \'alpha\', false, \'' . $app_strings['ERR_SQS_NO_MATCH_FIELD'] . $app_strings['LBL_ASSIGNED_TO'] . '\',\'assigned_user_id\');
				</script>';

						return $html;
	}
	/**
	  * Add Status selection popup window HTML code
	  * @param displayname Name to display in the popup window
	  * @param varname name of the variable
	  * @param options array of options for status
	  */
	function addStatus($displayname, $varname, $options){
		global $app_strings, $app_list_strings;

		// cn: added "mass_" to the id tag to diffentieate from the status id in StoreQuery
		$html = '<td scope="row" width="15%">'.$displayname.'</td><td>';
		if(is_array($options)){
			if(!isset($options['']) && !isset($options['0'])){
			   $new_options = array();
			   $new_options[''] = '';
			   foreach($options as $key=>$value) {
			   	   $new_options[$key] = $value;
			   }
			   $options = $new_options;
			}else{  #Bug36693 MassUpdate for ENUM with Option '0'
				$new_options[''] = '';
				$new_options['--null--'] = isset($options['']) ? $options[''] : $options['0'];
				foreach($options as $key=>$value) {
			   	   $new_options[$key] = $value;
			    }
			    $options = $new_options;
			}
			$options = get_select_options_with_id_separate_key($options, $options, '', true);;
			$html .= '<select id="mass_'.$varname.'" name="'.$varname.'">'.$options.'</select>';
		}else{
			$html .= $options;
		}
		$html .= '</td>';
		return $html;
	}

/**
	  * Add Status selection popup window HTML code
	  * @param displayname Name to display in the popup window
	  * @param varname name of the variable
	  * @param options array of options for status
	  */
	function addBool($displayname, $varname){
		global $app_strings, $app_list_strings;
		return $this->addStatus($displayname, $varname, $app_list_strings['checkbox_dom']);
	}
	function addStatusMulti($displayname, $varname, $options){
		global $app_strings, $app_list_strings;

		if(!isset($options['']) && !isset($options['0'])){
		   $new_options = array();
		   $new_options[''] = '';
		   foreach($options as $key=>$value) {
		   	   $new_options[$key] = $value;
		   }
		   $options = $new_options;
		}
		$options = get_select_options_with_id_separate_key($options, $options, '', true);;

		// cn: added "mass_" to the id tag to diffentieate from the status id in StoreQuery
		$html = '<td scope="row" width="15%">'.$displayname.'</td>
			 <td><select id="mass_'.$varname.'" name="'.$varname.'[]" size="5" MULTIPLE>'.$options.'</select></td>';
		return $html;
	}
	/**
	  * Add Date selection popup window HTML code
	  * @param displayname Name to display in the popup window
	  * @param varname name of the variable
	  */
	function addDate($displayname, $varname){
		global $timedate;
		$userformat = '('. $timedate->get_user_date_format().')';
		$cal_dateformat = $timedate->get_cal_date_format();
		global $app_strings, $app_list_strings, $theme;

		$javascriptend = <<<EOQ
		 <script type="text/javascript">
		Calendar.setup ({
			inputField : "${varname}jscal_field", daFormat : "$cal_dateformat", ifFormat : "$cal_dateformat", showsTime : false, button : "${varname}jscal_trigger", singleClick : true, step : 1, weekNumbers:false
		});
		</script>
EOQ;
        $jscalendarImage = SugarThemeRegistry::current()->getImageURL('jscalendar.gif');
		$html = <<<EOQ
	<td scope="row" width="20%">$displayname</td>
	<td class='dataField' width="30%"><input onblur="parseDate(this, '$cal_dateformat')" type="text" name='$varname' size="12" id='{$varname}jscal_field' maxlength='10' value="">
    <img src="$jscalendarImage" id="{$varname}jscal_trigger" align="absmiddle" title="{$app_strings['LBL_MASSUPDATE_DATE']}" alt='{$app_strings['LBL_MASSUPDATE_DATE']}'>&nbsp;<span class="dateFormat">$userformat</span>
	$javascriptend</td>
	<script> addToValidate('MassUpdate','$varname','date',false,'$displayname');</script>
EOQ;
		return $html;

	}

	function addRadioenumItem($name, $value, $output) {
		$_output = '';
		$_output .= '<label>';
		$_output .= '<input type="radio" name="'
	        . $name . '" value="'
	        . $value. '"';

	    $_output .= ' />' . ($output == '' ? $GLOBALS['app_strings']['LBL_LINK_NONE'] : $output);
	    $_output .= '</label><br />';
	    return $_output;
	}

	function addRadioenum($displayname, $varname, $options){
		 foreach ($options as $_key=>$_val){
            $_html_result[] = $this->addRadioenumItem($varname, $_key, $_val);
        }

		$html = '<td scope="row" width="15%">'.$displayname.'</td>
			 <td>'.implode("\n",$_html_result).'</td>';
		return $html;
	}
	/**
	  * Add Datetime selection popup window HTML code
	  * @param displayname Name to display in the popup window
	  * @param varname name of the variable
	  */
	function addDatetime($displayname, $varname){
		global $timedate;
		$userformat = $timedate->get_user_time_format();
		$cal_dateformat = $timedate->get_cal_date_format();
		global $app_strings, $app_list_strings, $theme;
		$jscalendarImage = SugarThemeRegistry::current()->getImageURL('jscalendar.gif');

		$javascriptend = <<<EOQ
		 <script type="text/javascript">
		Calendar.setup ({
			inputField : "{$varname}_date",
			daFormat : "$cal_dateformat",
			ifFormat : "$cal_dateformat",
			showsTime : false,
			button : "{$varname}_trigger",
			singleClick : true,
			step : 1,
			weekNumbers:false
		});
		</script>
EOQ;

		$html = <<<EOQ
		<td scope="row" width="20%">$displayname</td>
		<td class='dataField' width="30%"><input onblur="parseDate(this, '$cal_dateformat')" type="text" name='$varname' size="12" id='{$varname}_date' maxlength='10' value="">
		<img border="0" src="$jscalendarImage" alt='{$app_strings['LBL_MASSUPDATE_DATE']}' id="{$varname}_trigger" title="{$app_strings['LBL_MASSUPDATE_DATE']}"  align="absmiddle">&nbsp;$javascriptend

		<span id="{$varname}_time_section"></span>
		</td>
		<input type="hidden" id="{$varname}" name="{$varname}">
		<script type="text/javascript" src="include/SugarFields/Fields/Datetimecombo/Datetimecombo.js"></script>
		<script type="text/javascript">
		var combo_{$varname} = new Datetimecombo(" ", "$varname", "$userformat", '','','',1);
		//Render the remaining widget fields
		text = combo_{$varname}.html('');
		document.getElementById('{$varname}_time_section').innerHTML = text;

		//Call eval on the update function to handle updates to calendar picker object
		eval(combo_{$varname}.jsscript(''));

		function update_{$varname}_available() {
		      YAHOO.util.Event.onAvailable("{$varname}_date", this.handleOnAvailable, this);
		}

		update_{$varname}_available.prototype.handleOnAvailable = function(me) {
			Calendar.setup ({
			onClose : update_{$varname},
			inputField : "{$varname}_date",
			daFormat : "$cal_dateformat",
			ifFormat : "$cal_dateformat",
			button : "{$varname}_trigger",
			singleClick : true,
			step : 1,
			weekNumbers:false
			});

			//Call update for first time to round hours and minute values
			combo_{$varname}.update(false);
		}

		var obj_{$varname} = new update_{$varname}_available();
		</script>

		<script> addToValidate('MassUpdate','{$varname}_date','date',false,'$displayname');
		addToValidateBinaryDependency('MassUpdate', '{$varname}_hours', 'alpha', false, "{$app_strings['ERR_MISSING_REQUIRED_FIELDS']}", '{$varname}_date');
		addToValidateBinaryDependency('MassUpdate', '{$varname}_minutes', 'alpha', false, "{$app_strings['ERR_MISSING_REQUIRED_FIELDS']}", '{$varname}_date');
		addToValidateBinaryDependency('MassUpdate', '{$varname}_meridiem', 'alpha', false, "{$app_strings['ERR_MISSING_REQUIRED_FIELDS']}", '{$varname}_date');
		</script>

EOQ;
		return $html;
	}

	function date_to_dateTime($field, $value) {
		global $timedate;
	    //Check if none was set
        if (isset($this->sugarbean->field_defs[$field]['group'])) {
            $group =  $this->sugarbean->field_defs[$field]['group'];
            if (isset($this->sugarbean->field_defs[$group."_flag"]) && isset($_POST[$group."_flag"])
                && $_POST[$group."_flag"] == 1) {
                return "";
            }
        }

        $oldDateTime = $this->sugarbean->$field;
        $oldTime = explode(" ", $oldDateTime);
        if (isset($oldTime[1])) {
        	$oldTime = $oldTime[1];
        } else {
        	$oldTime = $timedate->now();
        }
        $oldTime = explode(" ", $oldTime);
        if (isset($oldTime[1])) {
        	$oldTime = $oldTime[1];
        } else {
        	$oldTime = $oldTime[0];
        }
        $value = explode(" ", $value);
        $value = $value[0];
	    return $value." ".$oldTime;
	}

	function checkClearField($field, $value) {
	    if ($value == 1 && strpos($field, '_flag')) {
	        $fName = substr($field, -5);
            if (isset($this->sugarbean->field_defs[$field]['group'])) {
                $group =  $this->sugarbean->field_defs[$field]['group'];
                if (isset($this->sugarbean->field_defs[$group])) {
                    $_POST[$group] = "";
                }
            }
	    }
	}

    function generateSearchWhere($module, $query) {//this function is similar with function prepareSearchForm() in view.list.php
        $seed = loadBean($module);
        $this->use_old_search = true;
        if(file_exists('modules/'.$module.'/SearchForm.html')){
            if(file_exists('modules/' . $module . '/metadata/SearchFields.php')) {
                require_once('include/SearchForm/SearchForm.php');
                $searchForm = new SearchForm($module, $seed);
            }
            elseif(!empty($_SESSION['export_where'])) { //bug 26026, sometimes some module doesn't have a metadata/SearchFields.php, the searchfrom is generated in the ListView.php.
            //So currently massupdate will not gernerate the where sql. It will use the sql stored in the SESSION. But this will cause bug 24722, and it cannot be avoided now.
                $where = $_SESSION['export_where'];
                $whereArr = explode (" ", trim($where));
                if ($whereArr[0] == trim('where')) {
                    $whereClean = array_shift($whereArr);
                }
                $this->where_clauses = implode(" ", $whereArr);
                return;
            }
            else {
                $this->where_clauses = '';
                return;
            }
        }
        else{
            $this->use_old_search = false;
            require_once('include/SearchForm/SearchForm2.php');

            if(file_exists('custom/modules/'.$module.'/metadata/metafiles.php')){
                require('custom/modules/'.$module.'/metadata/metafiles.php');
            }elseif(file_exists('modules/'.$module.'/metadata/metafiles.php')){
                require('modules/'.$module.'/metadata/metafiles.php');
            }

            if (file_exists('custom/modules/'.$module.'/metadata/searchdefs.php'))
            {
                require_once('custom/modules/'.$module.'/metadata/searchdefs.php');
            }
            elseif (!empty($metafiles[$module]['searchdefs']))
            {
                require_once($metafiles[$module]['searchdefs']);
            }
            elseif (file_exists('modules/'.$module.'/metadata/searchdefs.php'))
            {
                require_once('modules/'.$module.'/metadata/searchdefs.php');
            }


            if(!empty($metafiles[$module]['searchfields']))
                require_once($metafiles[$module]['searchfields']);
            elseif(file_exists('modules/'.$module.'/metadata/SearchFields.php'))
                require_once('modules/'.$module.'/metadata/SearchFields.php');
            if(empty($searchdefs) || empty($searchFields)) {
               $this->where_clauses = ''; //for some modules, such as iframe, it has massupdate, but it doesn't have search function, the where sql should be empty.
               return;
            }
            $searchForm = new SearchForm($seed, $module);
            $searchForm->setup($searchdefs, $searchFields, 'include/SearchForm/tpls/SearchFormGeneric.tpl');
        }
	/* bug 31271: using false to not add all bean fields since some beans - like SavedReports
	   can have fields named 'module' etc. which may break the query */
        $searchForm->populateFromArray(unserialize(base64_decode($query)), null, true); // see bug 31271
        $this->searchFields = $searchForm->searchFields;
        $where_clauses = $searchForm->generateSearchWhere(true, $module);
        if (count($where_clauses) > 0 ) {
            $this->where_clauses = '('. implode(' ) AND ( ', $where_clauses) . ')';
            $GLOBALS['log']->info("MassUpdate Where Clause: {$this->where_clauses}");
        } else {
            $this->where_clauses = '';
        }
    }

    /**
     * This is kinda a hack how it is implimented, but will tell us whether or not a focus has
     * fields for Mass Update
     *
     * @return bool
     */
    public function doMassUpdateFieldsExistForFocus()
    {
        static $banned = array('date_modified'=>1, 'date_entered'=>1, 'created_by'=>1, 'modified_user_id'=>1, 'deleted'=>1,'modified_by_name'=>1,);
        foreach($this->sugarbean->field_defs as $field) {
            //BEGIN SUGARCRM flav=pro ONLY
            if(ACLField::hasAccess($field['name'], $this->sugarbean->module_dir, $GLOBALS['current_user']->id, false)  < 2)continue;
            //END SUGARCRM flav=pro ONLY
            if(!isset($banned[$field['name']]) && (!isset($field['massupdate']) || !empty($field['massupdate']))){
                if(isset($field['type']) && $field['type'] == 'relate' && isset($field['id_name']) && $field['id_name'] == 'assigned_user_id')
                    $field['type'] = 'assigned_user_name';
                if(isset($field['custom_type']))$field['type'] = $field['custom_type'];
                if(isset($field['type']))
                {
                    switch($field["type"]){
                    case "relate":
                    case "parent":
                    case "int":
                    case "contact_id":
                    case "assigned_user_name":
                    case "account_id":
                    case "account_name":
                    case "bool":
                    case "enum":
                    case "multienum":
                    case "radioenum":
                    case "datetimecombo":
                    case "datetime":
                    case "date":
                    //BEGIN SUGARCRM flav=pro ONLY
                    case "team_list":
                    //END SUGARCRM flav=pro ONLY
                        return true;
                        break;
                    }
                }
            }
        }

        return false;
    }
}

?>
