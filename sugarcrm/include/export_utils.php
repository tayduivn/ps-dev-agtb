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
function export($type, $records = null, $members = false, $sample=false) {
	global $beanList;
	global $beanFiles;
	global $current_user;
	global $app_strings;
	global $app_list_strings;
	global $timedate;
    $sampleRecordNum = 5;
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


    $result = '';
    $populate = false;
    if($sample) {
       $result = $db->limitQuery($query, 0, $sampleRecordNum, true, $app_strings['ERR_EXPORT_TYPE'].$type.": <BR>.".$query);
        if( $focus->_get_num_rows_in_query($query)<1 ){
            $populate = true;
        }
	}else{
        $result = $db->query($query, true, $app_strings['ERR_EXPORT_TYPE'].$type.": <BR>.".$query);
    }

	
	$fields_array = $db->getFieldsArray($result,true);

	// setup the "header" line with proper delimiters
	$header = implode("\"".getDelimiter()."\"", array_values($fields_array));
	if($members){
		$header = str_replace('"ea_deleted"'.getDelimiter().'"ear_deleted"'.getDelimiter().'"primary_address"'.getDelimiter().'','',$header);
	}
	$header = "\"" .$header;
	$header .= "\"\r\n";
	$content .= $header;
	$pre_id = '';


    if($populate){
        //this is a sample request with no data, so create fake datarows

            $content .= returnFakeDataRow($focus,$fields_array,$sampleRecordNum);


    }else{
        //process retrieved record
    	while($val = $db->fetchByAssoc($result, -1, false)) {
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
                array_push($new_arr, preg_replace("/\"/","\"\"", $value));
            }
            $line = implode("\"".getDelimiter()."\"", $new_arr);
            $line = "\"" .$line;
            $line .= "\"\r\n";

            $content .= $line;
        }
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
/**
  * calls export method to build up a delimited string and some sample instructional text on how to use this file
  * @param string type the bean-type to export
  * @return string delimited string for export with some tutorial text
  */
     function exportSample($type) {
         global $app_strings;

         //first grab the
         $_REQUEST['all']=true;

         //retrieve the export content
         $content = export($type, null, false, true);

         //add details on removing the sample data
         return $content . $app_strings['LBL_IMPORT_SAMPLE_FILE_TEXT'];

     }
 //this function will take in the bean and field mapping and return a proper value
 function returnFakeDataRow($focus,$field_array,$rowsToReturn = 5){

    if(empty($focus) || empty($field_array))
     return ;

     //include the file that defines $sugar_demodata
     include('install/demoData.en_us.php');

    $person_bean = false;
    if( isset($focus->first_name)){
        $person_bean = true;
    }

     $returnContent = '';
     $counter = 0;
     $new_arr = array();

     //iterate through the record creation process as many times as defined.  Each iteration will create a new row
     while($counter < $rowsToReturn){
         $counter++;
         //go through each field and populate with dummy data if possible
         foreach($field_array as $field_name){

            if(empty($focus->field_name_map[$field_name]) || empty($focus->field_name_map[$field_name]['type'])){
                //type is not set, fill in with empty string and continue;
                $returnContent .= '"",';
                continue;
            }
            $field = $focus->field_name_map[$field_name];
                         //fill in value according to type
            $type = $field['type'];

             switch ($type) {

                 case "id":
                 case "assigned_user_name":
                     //return new guid string
                    $returnContent .= '"'.create_guid().'",';
                     break;
                 case "int":
                     //return random number`
                    $returnContent .= '"'.mt_rand(0,4).'",';
                     break;
                 case "name":
                     //return first, last, user name, or random name string
                     if($field['name'] == 'first_name'){
                         $count = count($sugar_demodata['first_name_array']) - 1;
                        $returnContent .= '"'.$sugar_demodata['last_name_array'][mt_rand(0,$count)].'",';
                     }elseif($field['name'] == 'last_name'){
                         $count = count($sugar_demodata['last_name_array']) - 1;
                         $returnContent .= '"'.$sugar_demodata['last_name_array'][mt_rand(0,$count)].'",';
                     }elseif($field['name'] == 'user_name'){
                       $count = count($sugar_demodata['first_name_array']) - 1;
                        $returnContent .= '"'.$sugar_demodata['last_name_array'][mt_rand(0,$count)].'_'.mt_rand(1,111).'",';
                     }else{
                         //return based on bean
                         if($focus->module_dir =='Accounts'){
                             $count = count($sugar_demodata['company_name_array']) - 1;
                            $returnContent .= '"'.$sugar_demodata['company_name_array'][mt_rand(0,$count)].'",';

                         }elseif($focus->module_dir =='Bugs'){
                             $count = count($sugar_demodata['bug_seed_names']) - 1;
                            $returnContent .= '"'.$sugar_demodata['bug_seed_names'][mt_rand(0,$count)].'",';
                         }elseif($focus->module_dir =='Notes'){
                             $count = count($sugar_demodata['note_seed_names_and_Descriptions']) - 1;
                            $returnContent .= '"'.$sugar_demodata['note_seed_names_and_Descriptions'][mt_rand(0,$count)].'",';

                         }elseif($focus->module_dir =='Calls'){
                              $count = count($sugar_demodata['call_seed_data_names']) - 1;
                            $returnContent .= '"'.$sugar_demodata['call_seed_data_names'][mt_rand(0,$count)].'",';

                         }elseif($focus->module_dir =='Tasks'){
                             $count = count($sugar_demodata['task_seed_data_names']) - 1;
                           $returnContent .= '"'.$sugar_demodata['task_seed_data_names'][mt_rand(0,$count)].'",';

                         }elseif($focus->module_dir =='Meetings'){
                             $count = count($sugar_demodata['meeting_seed_data_names']) - 1;
                           $returnContent .= '"'.$sugar_demodata['meeting_seed_data_names'][mt_rand(0,$count)].'",';

                         }elseif($focus->module_dir =='ProductCategories'){
                             $count = count($sugar_demodata['productcategory_seed_data_names']) - 1;
                           $returnContent .= '"'.$sugar_demodata['productcategory_seed_data_names'][mt_rand(0,$count)].'",';


                         }elseif($focus->module_dir =='ProductTypes'){
                             $count = count($sugar_demodata['producttype_seed_data_names']) - 1;
                           $returnContent .= '"'.$sugar_demodata['producttype_seed_data_names'][mt_rand(0,$count)].'",';


                         }elseif($focus->module_dir =='ProductTemplates'){
                             $count = count($sugar_demodata['producttemplate_seed_data']) - 1;
                           $returnContent .= '"'.$sugar_demodata['producttemplate_seed_data'][mt_rand(0,$count)].'",';

                         }else{
                           $returnContent .= '"Default Name for '.$focus->module_dir.'",';

                         }

                     }

                    break;
                 case "relate":
                     if($field['name'] == 'team_name'){
                         //apply team names and user_name
                         $teams_count = count($sugar_demodata['teams']) - 1;
                         $users_count = count($sugar_demodata['users']) - 1;

                     $returnContent .= '"'.$sugar_demodata['teams'][mt_rand(0,$teams_count)]['name'].','.$sugar_demodata['users'][mt_rand(0,$users_count)]['user_name'].'",';

                     }else{
                         //apply GUID
                         $returnContent .= '"'.create_guid().'",';
                     }
                     break;
                 case "bool":
                     //return 0 or 1
                     $returnContent .= '"'.mt_rand(0,1).'",';
                     break;

                 case "text":
                     //return random text
                     $returnContent .= '"Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Maecenas porttitor congue massa. Fusce posuere, magna sed pulvinar ultricies, purus lectus malesuada libero, sit amet commodo magna eros quis urna",';
                     break;

                 case "team_list":
                     $teams_count = count($sugar_demodata['teams']) - 1;
                     //give fake team names (East,West,North,South)
                     $returnContent .= '"'.$sugar_demodata['teams'][mt_rand(0,$teams_count)]['name'].'",';
                     break;

                 case "date":
                     //return formatted date
                     $timeStamp = strtotime('now');
                     $value =    date($timedate->dbDayFormat, $timeStamp);
                     $returnContent .= '"'.$timedate->to_display_date_time($value).'",';
                     break;

                 case "datetime":
                 case "datetimecombo":
                    global $timedate;
                     //return formatted date time
                     $timeStamp = strtotime('now');
                     //Start with db date
                     $value =    date($timedate->dbDayFormat.' '.$timedate->dbTimeFormat, $timeStamp);
                     //use timedate to convert to user display format
                     $value = $timedate->to_display_date_time($value);
                     //finally forma the am/pm to have a space so it can be recognized as a date field in excel
                     $value = preg_replace('/([pm|PM|am|AM]+)/', ' \1', $value);
                     $returnContent .= '"'.$value.'",';

                     break;
                case "phone":
                    $value = '('.mt_rand(300,999).') '.mt_rand(300,999).'-'.mt_rand(1000,9999);
                      $returnContent .= '"'.$value.'",';
                     break;
                 case "varchar":
                                     //process varchar for possible values
                                     if($field['name'] == 'first_name'){
                                         $count = count($sugar_demodata['first_name_array']) - 1;
                                        $returnContent .= '"'.$sugar_demodata['last_name_array'][mt_rand(0,$count)].'",';
                                     }elseif($field['name'] == 'last_name'){
                                         $count = count($sugar_demodata['last_name_array']) - 1;
                                         $returnContent .= '"'.$sugar_demodata['last_name_array'][mt_rand(0,$count)].'",';
                                     }elseif($field['name'] == 'user_name'){
                                       $count = count($sugar_demodata['first_name_array']) - 1;
                                        $returnContent .= '"'.$sugar_demodata['last_name_array'][mt_rand(0,$count)].'_'.mt_rand(1,111).'",';
                                     }elseif($field['name'] == 'title'){
                                         $count = count($sugar_demodata['titles']) - 1;
                                         $returnContent .= '"'.$sugar_demodata['titles'][mt_rand(0,$count)].'",';
                                     }elseif(strpos($field['name'],'address_street')>0){
                                       $count = count($sugar_demodata['street_address_array']) - 1;
                                        $returnContent .= '"'.$sugar_demodata['street_address_array'][mt_rand(0,$count)].'",';
                                     }elseif(strpos($field['name'],'address_city')>0){
                                       $count = count($sugar_demodata['city_array']) - 1;
                                        $returnContent .= '"'.$sugar_demodata['city_array'][mt_rand(0,$count)].'",';
                                     }elseif(strpos($field['name'],'address_state')>0){
                                         $state_arr = array('CA','NY','CO','TX','NV');
                                       $count = count($state_arr) - 1;
                                        $returnContent .= '"'.$state_arr[mt_rand(0,$count)].'",';
                                     }elseif(strpos($field['name'],'address_postalcode')>0){
                                        $returnContent .= '"'.mt_rand(12345,99999).'",';
                                     }else{
                                         $returnContent .= '"",';

                                     }
                     break;
                case "url":
                     $returnContent .= '"https://www.sugarcrm.com",';
                     break;

                case "enum":
                     //enum?
                default:
                    //type is not matched, fill in with empty string and continue;
                    $returnContent .= '"",';

             }
         }
         $returnContent .= "\r\n";
     }
     return $returnContent;
 }




 ?>

