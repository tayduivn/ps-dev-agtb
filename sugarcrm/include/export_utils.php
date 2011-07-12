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
 *Portions created by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights
 *Reserved.
 ********************************************************************************/
/*********************************************************************************
 * $Id: export_utils.php 56252 2010-05-04 20:59:44Z jmertic $
 * Description:
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc. All Rights
 * Reserved. Contributor(s): ______________________________________..
 * *******************************************************************************/

/**
 * gets the system default delimiter or an user-preference based override
 * @return string the delimiter
 */
function getDelimiter() {
	global $sugar_config;
	global $current_user;

	$delimiter = ','; // default to "comma"
	$userDelimiter = $current_user->getPreference('export_delimiter');
	$delimiter = empty($sugar_config['export_delimiter']) ? $delimiter : $sugar_config['export_delimiter'];
	$delimiter = empty($userDelimiter) ? $delimiter : $userDelimiter;

	return $delimiter;
}


/**
 * builds up a delimited string for export
 * @param string type the bean-type to export
 * @param array records an array of records if coming directly from a query
 * @return string delimited string for export
 */
function export($type, $records = null, $members = false) {
	global $beanList;
	global $beanFiles;
	global $current_user;
	global $app_strings;
	global $app_list_strings;
	global $timedate;
    global $mod_strings;
    global $current_language;
	$contact_fields = array(
		"id"=>"Contact ID"
		,"lead_source"=>"Lead Source"
		,"date_entered"=>"Date Entered"
		,"date_modified"=>"Date Modified"
		,"first_name"=>"First Name"
		,"last_name"=>"Last Name"
		,"salutation"=>"Salutation"
		,"birthdate"=>"Lead Source"
		,"do_not_call"=>"Do Not Call"
		,"email_opt_out"=>"Email Opt Out"
		,"title"=>"Title"
		,"department"=>"Department"
		,"birthdate"=>"Birthdate"
		,"do_not_call"=>"Do Not Call"
		,"phone_home"=>"Phone (Home)"
		,"phone_mobile"=>"Phone (Mobile)"
		,"phone_work"=>"Phone (Work)"
		,"phone_other"=>"Phone (Other)"
		,"phone_fax"=>"Fax"
		,"email1"=>"Email"
		,"email2"=>"Email (Other)"
		,"assistant"=>"Assistant"
		,"assistant_phone"=>"Assistant Phone"
		,"primary_address_street"=>"Primary Address Street"
		,"primary_address_city"=>"Primary Address City"
		,"primary_address_state"=>"Primary Address State"
		,"primary_address_postalcode"=>"Primary Address Postalcode"
		,"primary_address_country"=>"Primary Address Country"
		,"alt_address_street"=>"Other Address Street"
		,"alt_address_city"=>"Other Address City"
		,"alt_address_state"=>"Other Address State"
		,"alt_address_postalcode"=>"Other Address Postalcode"
		,"alt_address_country"=>"Other Address Country"
		,"description"=>"Description"
	);

	$account_fields = array(
		"id"=>"Account ID",
		"name"=>"Account Name",
		"website"=>"Website",
		"industry"=>"Industry",
		"account_type"=>"Type",
		"ticker_symbol"=>"Ticker Symbol",
		"employees"=>"Employees",
		"ownership"=>"Ownership",
		"phone_office"=>"Phone",
		"phone_fax"=>"Fax",
		"phone_alternate"=>"Other Phone",
		"email1"=>"Email",
		"email2"=>"Other Email",
		"rating"=>"Rating",
		"sic_code"=>"SIC Code",
		"annual_revenue"=>"Annual Revenue",
		"billing_address_street"=>"Billing Address Street",
		"billing_address_city"=>"Billing Address City",
		"billing_address_state"=>"Billing Address State",
		"billing_address_postalcode"=>"Billing Address Postalcode",
		"billing_address_country"=>"Billing Address Country",
		"shipping_address_street"=>"Shipping Address Street",
		"shipping_address_city"=>"Shipping Address City",
		"shipping_address_state"=>"Shipping Address State",
		"shipping_address_postalcode"=>"Shipping Address Postalcode",
		"shipping_address_country"=>"Shipping Address Country",
		"description"=>"Description"
	);
	$focus = 0;
	$content = '';

	$bean = $beanList[$type];
	require_once($beanFiles[$bean]);
	$focus = new $bean;
    $searchFields = array();
	$db = DBManagerFactory::getInstance();

	if($records) {
		$records = explode(',', $records);
		$records = "'" . implode("','", $records) . "'";
		$where = "{$focus->table_name}.id in ($records)";
	} elseif (isset($_REQUEST['all']) ) {
		$where = '';
	} else {
		if(!empty($_REQUEST['current_post'])) {
			$ret_array = generateSearchWhere($type, $_REQUEST['current_post']);
			
			$where = $ret_array['where'];
			$searchFields = $ret_array['searchFields'];
		} else {
			$where = '';
		}
	}
	$order_by = "";
	if($focus->bean_implements('ACL')){
		if(!ACLController::checkAccess($focus->module_dir, 'export', true)){
			ACLController::displayNoAccess();
			sugar_die('');
		}
		if(ACLController::requireOwner($focus->module_dir, 'export')){
			if(!empty($where)){
				$where .= ' AND ';
			}
			$where .= $focus->getOwnerWhere($current_user->id);
		}

	}
    // Export entire list was broken because the where clause already has "where" in it
    // and when the query is built, it has a "where" as well, so the query was ill-formed.
    // Eliminating the "where" here so that the query can be constructed correctly.
    if($members == true){
   		$query = $focus->create_export_members_query($records);
    }else{
		$beginWhere = substr(trim($where), 0, 5);
	    if ($beginWhere == "where")
	        $where = substr(trim($where), 5, strlen($where));
        $ret_array = create_export_query_relate_link_patch($type, $searchFields, $where);
        if(!empty($ret_array['join'])) {
        	$query = $focus->create_export_query($order_by,$ret_array['where'],$ret_array['join']);
        } else {
	    	$query = $focus->create_export_query($order_by,$ret_array['where']);
        }
    }


	$result = $db->query($query, true, $app_strings['ERR_EXPORT_TYPE'].$type.": <BR>.".$query);

	$fields_array = $db->getFieldsArray($result,true);

    //grab the focus module strings
    $temp_mod_strings = $mod_strings;
	$mod_strings = return_module_language($current_language, $focus->module_dir);

    //set the order in header here prior to any translation
      $fields_array = get_field_order_mapping($focus->module_dir,$fields_array);
    //get the array_values so it matches the record values when processing record in foreach loop below
      $fields_array = array_values($fields_array);


    //iterate through db fields and attempt to retrieve label from field mapping
    foreach($fields_array as $dbname){
        $fieldLabel = '';

        //first check to see if we are overriding the label for export.
        if (!empty($mod_strings['LBL_EXPORT_'.strtoupper($dbname)])){
            //entry exists which means we are overriding this value for exporting, use this label
            $fieldLabel = $mod_strings['LBL_EXPORT_'.strtoupper($dbname)];
        }elseif (!empty($focus->field_name_map[$dbname]['vname']) && !empty($app_strings[$focus->field_name_map[$dbname]['vname']])){
            //check to see if label exists in mapping and in app strings
            $fieldLabel = $app_strings[$focus->field_name_map[$dbname]['vname']];
        }elseif (!empty($focus->field_name_map[$dbname]['vname']) && !empty($app_strings[$focus->field_name_map[$dbname]['vname']])){
            //check to see if label exists in mapping and in app strings
            $fieldLabel = $app_strings[$focus->field_name_map[$dbname]['vname']];
        }elseif (!empty($mod_strings['LBL_'.strtoupper($dbname)])){
            //field is not in mapping, so check to see if db can be uppercased and found in mod strings
            $fieldLabel = $mod_strings['LBL_'.strtoupper($dbname)];
        }elseif (!empty($app_strings['LBL_'.strtoupper($dbname)])){
            //check to see if db can be uppercased and found in app strings
            $fieldLabel = $app_strings['LBL_'.strtoupper($dbname)];
        }else{
            //we could not find the label in mod_strings or app_strings based on either a mapping entry
            //or on the db_name itself, so default to the db name as a last resort
            $fieldLabel = $dbname;
        }
        //strip the label of any columns, and set in field label array
        $field_labels[$dbname] = preg_replace("/([:]|\xEF\xBC\x9A)[\\s]*$/", '', trim($fieldLabel));;
    }
    //reset the bean mod_strings back to original import strings
    $mod_strings = $temp_mod_strings;

	// setup the "header" line with proper delimiters
	$header = implode("\"".getDelimiter()."\"", array_values($field_labels));
	if($members){
		$header = str_replace('"ea_deleted"'.getDelimiter().'"ear_deleted"'.getDelimiter().'"primary_address"'.getDelimiter().'','',$header);
	}
	$header = "\"" .$header;
	$header .= "\"\r\n";
	$content .= $header;
	$pre_id = '';

	while($val = $db->fetchByAssoc($result, -1, false)) {
        //order the values in the record array
        $val = get_field_order_mapping($focus->module_dir,$val);

		$new_arr = array();
		//BEGIN SUGARCRM flav=pro ONLY
		if(!is_admin($current_user)){
			$focus->id = (!empty($val['id']))?$val['id']:'';
			$focus->assigned_user_id = (!empty($val['assigned_user_id']))?$val['assigned_user_id']:'' ;
			$focus->created_by = (!empty($val['created_by']))?$val['created_by']:'';
			ACLField::listFilter($val, $focus->module_dir,$current_user->id, $focus->isOwner($current_user->id), true, 1, true );
		}

		//END SUGARCRM flav=pro ONLY
		if($members){
			if($pre_id == $val['id'])
				continue;
			if($val['ea_deleted']==1 || $val['ear_deleted']==1){
				$val['primary_email_address'] = '';
			}
			unset($val['ea_deleted']);
			unset($val['ear_deleted']);
			unset($val['primary_address']);
		}
		$pre_id = $val['id'];
		$vals = array_values($val);
		foreach ($vals as $key => $value) {
			//if our value is a datetime field, then apply the users locale
			if(isset($focus->field_name_map[$fields_array[$key]]['type']) && ($focus->field_name_map[$fields_array[$key]]['type'] == 'datetime' || $focus->field_name_map[$fields_array[$key]]['type'] == 'datetimecombo')){
				$value = $timedate->to_display_date_time($value);
				$value = preg_replace('/([pm|PM|am|AM]+)/', ' \1', $value);
			}
			//kbrill Bug #16296
			if(isset($focus->field_name_map[$fields_array[$key]]['type']) && $focus->field_name_map[$fields_array[$key]]['type'] == 'date'){
				$value = $timedate->to_display_date($value, false);
			}
			// Bug 32463 - Properly have multienum field translated into something useful for the client
			if(isset($focus->field_name_map[$fields_array[$key]]['type']) && $focus->field_name_map[$fields_array[$key]]['type'] == 'multienum'){
			    $value = str_replace("^","",$value);
			    if ( isset($focus->field_name_map[$fields_array[$key]]['options'])
			            && isset($app_list_strings[$focus->field_name_map[$fields_array[$key]]['options']]) ) {
                    $valueArray = explode(",",$value);
                    foreach ( $valueArray as $multikey => $multivalue ) {
                        if ( isset($app_list_strings[$focus->field_name_map[$fields_array[$key]]['options']][$multivalue]) ) {
                            $valueArray[$multikey] = $app_list_strings[$focus->field_name_map[$fields_array[$key]]['options']][$multivalue];
                        }
                    }
                    $value = implode(",",$valueArray);
			    }
			}
			//BEGIN SUGARCRM flav=pro ONLY
			if(isset($focus->field_name_map[$fields_array[$key]]['custom_type']) && $focus->field_name_map[$fields_array[$key]]['custom_type'] == 'teamset'){
				require_once('modules/Teams/TeamSetManager.php');
				$value = TeamSetManager::getCommaDelimitedTeams($val['team_set_id'], !empty($val['team_id']) ? $val['team_id'] : '');
			}
			//END SUGARCRM flav=pro ONLY

			//replace user_name with full name if use_real_name preference setting is enabled
            //and this is a user name field
            $useRealNames = $current_user->getPreference('use_real_names');
            if(!empty($useRealNames) && ($useRealNames &&  $useRealNames !='off' )
               && !empty($focus->field_name_map[$fields_array[$key]]['type']) && $focus->field_name_map[$fields_array[$key]]['type'] == 'relate'
               && !empty($focus->field_name_map[$fields_array[$key]]['module'])&& $focus->field_name_map[$fields_array[$key]]['module'] == 'Users'
               && !empty($focus->field_name_map[$fields_array[$key]]['rname']) && $focus->field_name_map[$fields_array[$key]]['rname'] == 'user_name'){

                global $locale;
                $userFocus = new User();
                $userFocus->retrieve_by_string_fields(
                    array('user_name' => $value ));
                if ( !empty($userFocus->id) ) {
                    $value = $locale->getLocaleFormattedName($userFocus->first_name, $userFocus->last_name);
                }
			}

			array_push($new_arr, preg_replace("/\"/","\"\"", $value));
		}
		$line = implode("\"".getDelimiter()."\"", $new_arr);
		$line = "\"" .$line;
		$line .= "\"\r\n";

		$content .= $line;
	}
	return $content;
}

function generateSearchWhere($module, $query) {//this function is similar with function prepareSearchForm() in view.list.php
    $seed = loadBean($module);
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
            $where = implode(" ", $whereArr);
            //rrs bug: 31329 - previously this was just returning $where, but the problem is the caller of this function
            //expects the results in an array, not just a string. So rather than fixing the caller, I felt it would be best for
            //the function to return the results in a standard format.
            $ret_array['where'] = $where;
    		$ret_array['searchFields'] =array();
            return $ret_array;
        }
        else {
            return;
        }
    }
    else{
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
           //for some modules, such as iframe, it has massupdate, but it doesn't have search function, the where sql should be empty.
            return;
        }
        $searchForm = new SearchForm($seed, $module);
        $searchForm->setup($searchdefs, $searchFields, 'include/SearchForm/tpls/SearchFormGeneric.tpl');
    }
    $searchForm->populateFromArray(unserialize(base64_decode($query)));
    $where_clauses = $searchForm->generateSearchWhere(true, $module);
    if (count($where_clauses) > 0 )$where = '('. implode(' ) AND ( ', $where_clauses) . ')';
        $GLOBALS['log']->info("Export Where Clause: {$where}");
    $ret_array['where'] = $where;
    $ret_array['searchFields'] = $searchForm->searchFields;
    return $ret_array;
}


    //call this function to retrurn the desired order to display columns for export in.
    //if you pass in an array, it will reorder the array and send back to you.  It expects the array
    //to have the db names as key values, or as labels
    function get_field_order_mapping($name='',$reorderArr = ''){

        //define the ordering of fields, note that the key value is what is important, and should be the db field name
        $field_order_array = array();
        $field_order_array['accounts'] = array('id'=>'ID', 'name'=>'Name', 'website'=>'Website', 'email_address' =>'Email Address', 'phone_office' =>'Office Phone', 'phone_alternate' => 'Alternate Phone', 'phone_fax' => 'Fax', 'billing_address_street' => 'Billing Street', 'billing_address_city' => 'Billing City', 'billing_address_state' => 'Billing State', 'billing_address_postalcode' => 'Billing Postal Code', 'billing_address_country' => 'Billing Country', 'shipping_address_street' => 'Shipping Street', 'shipping_address_city' => 'Shipping City', 'shipping_address_state' => 'Shipping State', 'shipping_address_postalcode' => 'Shipping Postal Code', 'shipping_address_country' => 'Shipping Country', 'description' => 'Description', 'account_type' => 'Type', 'industry' =>'Industry', 'annual_revenue' => 'Annual Revenue', 'employees' => 'Employees', 'sic_code' => 'SIC Code', 'ticker_symbol' => 'Ticker Symbol', 'parent_id' => 'Parent Account ID', 'ownership' =>'Ownership', 'campaign_id' =>'Campaign ID', 'rating' =>'Rating', 'assigned_user_name' =>'Assigned to', 'team_id' =>'Team Id', 'team_name' =>'Teams', 'team_set_id' =>'Team Set ID', 'date_entered' =>'Date Created', 'date_modified' =>'Date Modified', 'modified_user_id' =>'Modified By', 'created_by' =>'Created By', 'deleted' =>'Deleted');

        //of array is passed in for reordering, process array
        if(!empty($name) && !empty($reorderArr) && is_array($reorderArr)){

            //make sure reorderArr has values as keys, if not then itereate through and assign the value as the key
            $newReorder = array();
            foreach($reorderArr as $rk=> $rv){
                if(is_int($rk)){
                    $newReorder[$rv]=$rv;
                }else{
                    $newReorder[$rk]=$rv;
                }

            }

            if(!isset($field_order_array[strtolower($name)]))
                return $newReorder;

            //lets iterate through and create a reordered temporary array using
            //the  newly formatted copy of passed in array
            $temp_result_arr = array();
            foreach($field_order_array[strtolower($name)] as $fk=> $fv){

                //if the value exists as a key in the passed in array, add to temp array and remove from reorder array.
                //Do not force into the temp array as we don't want to violate acl's
                if(array_key_exists($fk,$newReorder)){
                    $temp_result_arr[$fk] = $newReorder[$fk];
                    unset($newReorder[$fk]);
                }
            }

            //add in all the left over values that were not in our ordered list
            array_splice($temp_result_arr, count($temp_result_arr), 0, $newReorder);
            //return temp ordered list
            return $temp_result_arr;
        }

        //if no array was passed in, pass back either the list of ordered columns by module, or the entireorder array
        if(empty($name)){
            return $field_order_array;
        }else{
            return $field_order_array[strtolower($name)];
        }

    }
?>