<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
// $Id: action_utils.php 56841 2010-06-05 00:44:19Z smalyshev $
//FILE SUGARCRM flav=pro ONLY

include_once('include/workflow/workflow_utils.php');
include_once('include/workflow/field_utils.php');
include_once('include/utils/expression_utils.php');

function process_workflow_actions($focus, $action_array){


	if($action_array['action_type']=="update"){
		process_action_update($focus, $action_array);
	}
	if($action_array['action_type']=="update_rel"){
		process_action_update_rel($focus, $action_array);
	}
	if($action_array['action_type']=="new"){
		process_action_new($focus, $action_array);
	}
	if($action_array['action_type']=="new_rel"){
		process_action_new_rel($focus, $action_array);
	}


//end function process_workflow_actions
}

function process_action_update($focus, $action_array){

	foreach($action_array['basic'] as $field => $new_value){
		if(empty($action_array['basic_ext'][$field])){
			//if we have a relate field, make sure the related record still exists.
			if ($focus->field_defs[$field]['type'] == "relate")
			{
				$relBean = BeanFactory::getBean($focus->field_defs[$field]['module']);
				$relBean->retrieve($new_value);
                if (empty($relBean->id) && (!empty($focus->required_fields[$field]) && $focus->required_fields[$field] == true))
                {
					$GLOBALS['log']->info("workflow attempting to set relate field $field to invalid id: $new_value");
					continue;
				}
			}
            if (!empty($focus->field_defs[$field]['calculated']))
            {
                $GLOBALS['log']->info("workflow attempting to update calculated field $field.");
                continue;
            }
            if (in_array($focus->field_defs[$field]['type'], array('double', 'decimal','currency', 'float')))
            {
                $new_value = (float)unformat_number($new_value);
            } elseif ($focus->field_defs[$field]['type'] === 'multienum') {
                $new_value = workflow_convert_multienum_value($new_value);
            }
			$focus->$field = convert_bool($new_value, $focus->field_defs[$field]['type']);
			execute_special_logic($field, $focus);
		}
		//otherwise rely on the basic_ext to handle the action for this field
        if($field == "assigned_user_id" && (empty($_REQUEST['massupdate']) || $_REQUEST['massupdate']==='false')) {
            $focus->notify_inworkflow = true;
        }

        if($field == "email1") $focus->email1_set_in_workflow = $focus->email1;
        if($field == "email2") $focus->email2_set_in_workflow = $focus->email2;
	}

	foreach($action_array['basic_ext'] as $field => $new_value){
        if (!empty($focus->field_defs[$field]['calculated']))
        {
            $GLOBALS['log']->info("workflow attempting to update calculated field $field.");
            continue;
        }
		$fieldType = get_field_type($focus->field_defs[$field]);
		//Only here if there is a datetime.
		if($new_value=='Triggered Date'){
			$focus->$field = get_expiry_date($fieldType, $action_array['basic'][$field], $fieldType === 'date');
			if($focus->field_defs[$field]['type']=='date' && !empty($focus->field_defs[$field]['rel_field']) ){
				$rel_field = $focus->field_defs[$field]['rel_field'];
				$focus->$rel_field = get_expiry_date('time', $action_array['basic'][$field]);
			}
			execute_special_logic($field, $focus);
		}
		if($new_value=='Existing Value'){
			$focus->$field = get_expiry_date($fieldType, $action_array['basic'][$field], false, true, $focus->$field);
			execute_special_logic($field, $focus);
		}
	}

	foreach($action_array['advanced'] as $field => $meta_array){
        if (!empty($focus->field_defs[$field]['calculated']))
            {
                $GLOBALS['log']->info("workflow attempting to update calculated field $field.");
                continue;
            }
		$new_value = process_advanced_actions($focus, $field, $meta_array, $focus);
		$focus->$field = $new_value;
		execute_special_logic($field, $focus);
	}
    $focus->in_workflow = true;
//end function process_action_update
}

function process_action_update_rel($focus, $action_array){

		$rel_handler = $focus->call_relationship_handler("module_dir", true);
		$rel_handler->set_rel_vardef_fields($action_array['rel_module']);
		$rel_handler->build_info(false);

		$rel_list = $rel_handler->build_related_list();

	//All, First, Filter (default ALL)

	if(!empty($rel_list[0])){

		$rel_list = process_rel_type("rel_module_type", "rel_filter", $rel_list, $action_array);

		foreach($rel_list as $rel_object){
            if (empty($rel_object->id)) {
                continue;
            }
            $check_notify = false;
            $old_owner = $rel_object->assigned_user_id;
			foreach($action_array['basic'] as $field => $new_value){
                if (isset($rel_object->field_defs[$field]['type']) && $rel_object->field_defs[$field]['type'] === 'multienum') {
                    $new_value = workflow_convert_multienum_value($new_value);
                }
				if(empty($action_array['basic_ext'][$field])){
					$rel_object->$field = convert_bool($new_value, $rel_object->field_defs[$field]['type']);
				}
                execute_special_logic($field, $rel_object);
				//otherwise rely on the basic_ext to handle the action for this field
				if($field == "email1") $rel_object->email1_set_in_workflow = $rel_object->email1;
                if($field == "email2") $rel_object->email2_set_in_workflow = $rel_object->email2;
			//loop through fields to change
			}
			foreach($action_array['basic_ext'] as $field => $new_value){
				//Only here if there is a datetime.
				if($new_value=='Triggered Date'){
					$rel_object->$field = get_expiry_date(get_field_type($rel_object->field_defs[$field]), $action_array['basic'][$field], true);
				}
				if($new_value=='Existing Value'){
					$rel_object->$field = get_expiry_date(get_field_type($rel_object->field_defs[$field]), $action_array['basic'][$field], true, true, $rel_object->$field);
				}
			}

			foreach($action_array['advanced'] as $field => $meta_array){
				$new_value = process_advanced_actions($focus, $field, $meta_array, $rel_object);
				$rel_object->$field = $new_value;
			}
            $rel_object->in_workflow = true;
            if($old_owner != $rel_object->assigned_user_id) $check_notify = true;
            if(!empty($_REQUEST['massupdate']) && $_REQUEST['massupdate']=='true') $check_notify = false;//if in a massupdate, the notification will not be sent, because it will take a long time.
			$rel_object->save($check_notify);

		//end foreach rel_object
		}

	//end if there are any relationship records
	}

//end function process_action_update_rel
}

function process_action_new($focus, $action_array){

	//find out if the action_module is related to this module or not.  If so make sure to connect
	$seed_object = BeanFactory::getBean('WorkFlow');
    $seed_object->base_module = $focus->module_dir;
    $rel_module = $seed_object->get_rel_module($action_array['action_module'], true);
	$target_module = BeanFactory::getBean($rel_module);
	$rel_handler = $focus->call_relationship_handler("module_dir", true);
	//$rel_handler->base_bean = & $focus;
	$rel_handler->get_relationship_information($target_module);

	//get_relationship_information($target_module, $focus);

    if(!empty($_REQUEST['massupdate']) && $_REQUEST['massupdate']=='true') {
        $check_notify = false;
    }
    else {
        $check_notify = true;
    }

	foreach($action_array['basic'] as $field => $new_value){
        if (isset($target_module->field_defs[$field]['type']) && $target_module->field_defs[$field]['type'] === 'multienum') {
            $new_value = workflow_convert_multienum_value($new_value);
        }
			//rrs - bug 10466
			$target_module->$field = convert_bool($new_value, $target_module->field_defs[$field]['type'], (empty($target_module->field_defs[$field]['dbType']) ? '' : $target_module->field_defs[$field]['dbType']));
            if($field == "email1") $target_module->email1_set_in_workflow = $target_module->email1;
            if($field == "email2") $target_module->email2_set_in_workflow = $target_module->email2;
	//end foreach value
	}
	foreach($action_array['basic_ext'] as $field => $new_value){
		//Only here if there is a datetime.
		if($new_value=='Triggered Date'){

			$target_module->$field = get_expiry_date(get_field_type($target_module->field_defs[$field]), $action_array['basic'][$field], true);

			if($target_module->field_defs[$field]['type']=='date' && !empty($target_module->field_defs[$field]['rel_field']) ){
				$rel_field = $target_module->field_defs[$field]['rel_field'];
				$target_module->$rel_field = get_expiry_date('time', $action_array['basic'][$field], true);
			}
		}
	}

	foreach($action_array['advanced'] as $field => $meta_array){


		$new_value = process_advanced_actions($focus, $field, $meta_array, $target_module);

		$target_module->$field = $new_value;
	}
	clean_save_data($target_module, $action_array);


	//BEGIN BRIDGING FOR MEETINGS/CALLS
	if(!empty($action_array['bridge_id']) && $action_array['bridge_id']!=""){
		$target_module->bridge_id = $action_array['bridge_id'];
		$target_module->bridge_object = $focus;

	}
	//END BRIDGING FOR MEETINGS/CALLS

    $target_module->in_workflow = true;
    $target_module->not_use_rel_in_req = true;
    $target_module->new_rel_relname = $seed_object->rel_name;
    $target_module->new_rel_id = $focus->id;
    if (!empty($focus->assigned_user_id) && empty($target_module->assigned_user_id)) {
        $target_module->assigned_user_id = $focus->assigned_user_id;
    }
	$target_module->save($check_notify);

//end function_action_new
}



function process_action_new_rel($focus, $action_array){

	///Build the relationship information using the Relationship handler
	$rel_handler = $focus->call_relationship_handler("module_dir", true);
	$rel_handler->set_rel_vardef_fields($action_array['rel_module'], $action_array['action_module']);
	//$rel_handler->base_bean = & $focus;
	$rel_handler->build_info(true);
	//get related bean
	$rel_list = $rel_handler->build_related_list("base");

    if(!empty($_REQUEST['massupdate']) && $_REQUEST['massupdate']=='true') {
        $check_notify = false;
    }
    else {
        $check_notify = true;
    }


	//All, first, filter (FIRST)

	if(!empty($rel_list[0])){

		$rel_list = process_rel_type("rel_module_type", "rel_filter", $rel_list, $action_array);

		foreach($rel_list as $rel_object){
			//Connect new module to the first related bean.
			$rel_handler2 = $rel_object->call_relationship_handler("module_dir", true);
			//$rel_handler->base_bean = & $rel_object;
			$rel_handler2->get_relationship_information($rel_handler->rel2_bean, true);

			//get_relationship_information($rel_handler->rel2_bean, $rel_object);

			$target_module = & $rel_handler->rel2_bean;

			foreach($action_array['basic'] as $field => $new_value){
                if (isset($target_module->field_defs[$field]['type']) && $target_module->field_defs[$field]['type'] === 'multienum') {
                    $new_value = workflow_convert_multienum_value($new_value);
                }
				$target_module->$field = convert_bool($new_value, $target_module->field_defs[$field]['type']);
                if($field == "email1") $target_module->email1_set_in_workflow = $target_module->email1;
                if($field == "email2") $target_module->email2_set_in_workflow = $target_module->email2;
			//end foreach value
			}
			foreach($action_array['basic_ext'] as $field => $new_value){
				//Only here if there is a datetime.
				if($new_value=='Triggered Date'){

					$target_module->$field = get_expiry_date(get_field_type($target_module->field_defs[$field]), $action_array['basic'][$field], true);
					if($target_module->field_defs[$field]['type']=='date' && !empty($target_module->field_defs[$field]['rel_field']) ){
						$rel_field = $target_module->field_defs[$field]['rel_field'];
						$target_module->$rel_field = get_expiry_date('time', $action_array['basic'][$field], true);
					}
				}
			}

			foreach($action_array['advanced'] as $field => $meta_array){
				$new_value = process_advanced_actions($focus, $field, $meta_array, $target_module);
				$target_module->$field = $new_value;
			}
			clean_save_data($target_module, $action_array);


			//BEGIN BRIDGING FOR MEETINGS/CALLS
				if(!empty($action_array['bridge_id']) && $action_array['bridge_id']!=""){
					$target_module->bridge_id = $action_array['bridge_id'];
					$target_module->bridge_object = $focus;
				}
			//END BRIDGING FOR MEETINGS/CALLS
            if($focus->object_name == $target_module->object_name){
			     $target_module->processed = true;
            }

			$target_module->in_workflow = true;
			$target_module->not_use_rel_in_req = true;

			$target_module->save($check_notify);

		//end for loop of all,first, filter
		}
	//end if a related record exists to connect this item too.
	}
//end function_action_new_rel
}


function clean_save_data($target_module, $action_array){
	global $app_list_strings;
		if (empty($app_list_strings)) {
			global $sugar_config;
			$app_list_strings = return_app_list_strings_language($sugar_config['default_language']);
		}
		foreach($target_module->column_fields as $field){
			if(empty($target_module->$field)){

				$data_cleaned = false;

				if($target_module->field_defs[$field]['type']=='bool'){
					$target_module->$field = 0;
					$data_cleaned = true;
				}

				if( isset($target_module->field_defs[$field]['auto_increment'] )
				    && $target_module->field_defs[$field]['auto_increment']  ){
					$target_module->$field = null;
					$data_cleaned = true;
				}

				// make sure there are options, some enums based on functions don't have options set
				if($target_module->field_defs[$field]['type']=='enum' && isset($target_module->field_defs[$field]['options'])) {
					$options_array_name = $target_module->field_defs[$field]['options'];
					$target_module->$field = key($app_list_strings[$options_array_name]);
					$data_cleaned = true;
				//end if type is enum
				}

				if($target_module->field_defs[$field]['name']=='duration_hours' ||
					$target_module->field_defs[$field]['name']=='duration_minutes'
				){
					$target_module->$field = '0';
					$data_cleaned = true;
				//end if duration hours or minutes from calls module
				}

				if(  ( $target_module->field_defs[$field]['name']=='date_start' ||
					$target_module->field_defs[$field]['name']=='time_start' )
					&&
					 (  $target_module->object_name == "Call" ||
						$target_module->object_name == "Meeting"  )

				){
					$target_module->$field = get_expiry_date(get_field_type($target_module->field_defs[$field]), 0);
					if($target_module->field_defs[$field]['type']=='date' && !empty($target_module->field_defs[$field]['rel_field']) ){
						$rel_field = $target_module->field_defs[$field]['rel_field'];
						$target_module->$rel_field = get_expiry_date('time', $action_array['basic'][$field]);
					}
					$data_cleaned=true;
				}

				if($target_module->field_defs[$field]['name']=='date_entered'){

					$data_cleaned=true;
				}

				if($target_module->field_defs[$field]['name'] == "name"){
				//make sure you set the 'name' to blank, otherwise you won't be able
				//to go into the record

					$target_module->$field = " - blank - ";
					$data_cleaned=true;
				}

				if($data_cleaned==false){

						//try to fill with default if available
						if(!empty($target_module->field_defs[$field]['default'])){
							$target_module->$field = $target_module->field_defs[$field]['default'];
						} else {

							//fill in with blank value
							$target_module->$field = "";


						}
				}

			//end if not empty
			}

		//end foreach
		}

//end function clean_save_data
}

/**
 * Parse date from certain type and add interval to it
 *
 * @param string $stamp_type  Type (date, time, datetime)
 * @param int $time_interval Interval, seconds
 * @param bool $user_format Date is in user format?
 * @param bool $is_update Is it update for existing field?
 * @param string $value Date value (if update)
 */
function get_expiry_date($stamp_type, $time_interval, $user_format = false, $is_update = false, $value=null)
{
	/* This function needs to be combined with the one in WorkFlowSchedule.php
	*/
	global $timedate;

	if($is_update){
	    if(!empty($value)) {
    	    if($user_format) {
    	        $date = $timedate->fromUserType($value, $stamp_type);
    	    } else {
    		    $date = $timedate->fromDbType($value, $stamp_type);
    	    }
	    }
	} else {
	    // When the type is "date", asDbType() does not change the TZ by default, so the date will still be in user TZ
	    // even though formatted as DB date. That's because we do not convert dates (as opposed to datetimes) by default.
	    $date = $timedate->getNow($user_format);
	}

    if (empty($date)) {
        $GLOBALS['log']->warn("Invalid date [$value] for type $stamp_type");
        return '';
    }

	$date->modify("+$time_interval seconds");
    return $timedate->asDbType($date, $stamp_type);
}

/**
 * Creates proper representation of multienum value from actions_array.php
 *
 * @param string $value
 * @return string
 */
function workflow_convert_multienum_value($value)
{
    // this is weird, but new value is stored in workflow definition as a partially
    // encoded string — without leading and trailing ^s, but with delimiting ^s and commas.
    // thus we pretend it's a single value and wrap it into array in order to get the leading and trailing ^s
    // @see parse_multi_array()
    return encodeMultienumValue(array($value));
}
