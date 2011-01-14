<?php
if(!defined('sugarEntry'))define('sugarEntry', true);
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
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 *
 ********************************************************************************/

/**
 * This class is an implemenatation class for all the rest services
 */
require_once('service/v3_1/SugarWebServiceImplv3_1.php');
require_once('SugarWebServiceUtilv4.php');


class SugarWebServiceImplv4 extends SugarWebServiceImplv3_1 {

    public function __construct()
    {
        self::$helperObject = new SugarWebServiceUtilv3_2();
    }
    
    	
	/**
	 * Retrieve a list of SugarBean's based on provided IDs. This API will not wotk with report module
	 *
	 * @param String $session -- Session ID returned by a previous call to login.
	 * @param String $module_name -- The name of the module to return records from.  This name should be the name the module was developed under (changing a tab name is studio does not affect the name that should be passed into this method)..
	 * @param Array $ids -- An array of SugarBean IDs.
	 * @param Array $select_fields -- A list of the fields to be included in the results. This optional parameter allows for only needed fields to be retrieved.
	 * @param Array $link_name_to_fields_array -- A list of link_names and for each link_name, what fields value to be returned. For ex.'link_name_to_fields_array' => array(array('name' =>  'email_addresses', 'value' => array('id', 'email_address', 'opt_out', 'primary_address')))
	 * @return Array
	 *        'entry_list' -- Array - The records name value pair for the simple data types excluding link field data.
	 *	     'relationship_list' -- Array - The records link field data. The example is if asked about accounts email address then return data would look like Array ( [0] => Array ( [name] => email_addresses [records] => Array ( [0] => Array ( [0] => Array ( [name] => id [value] => 3fb16797-8d90-0a94-ac12-490b63a6be67 ) [1] => Array ( [name] => email_address [value] => hr.kid.qa@example.com ) [2] => Array ( [name] => opt_out [value] => 0 ) [3] => Array ( [name] => primary_address [value] => 1 ) ) [1] => Array ( [0] => Array ( [name] => id [value] => 403f8da1-214b-6a88-9cef-490b63d43566 ) [1] => Array ( [name] => email_address [value] => kid.hr@example.name ) [2] => Array ( [name] => opt_out [value] => 0 ) [3] => Array ( [name] => primary_address [value] => 0 ) ) ) ) )
	 * @exception 'SoapFault' -- The SOAP error, if any
	 */
	public function get_entries($session, $module_name, $ids, $select_fields, $link_name_to_fields_array)
	{
	    $result = parent::get_entries($session, $module_name, $ids, $select_fields, $link_name_to_fields_array);
		$relationshipList = $result['relationship_list'];
		$returnRelationshipList = array();
		foreach($relationshipList as $rel){
			$link_output = array();
			foreach($rel as $row){
				$rowArray = array();
				foreach($row['records'] as $record){
					$rowArray[]['link_value'] = $record;
				}
				$link_output[] = array('name' => $row['name'], 'records' => $rowArray);
			}
			$returnRelationshipList[]['link_list'] = $link_output;
		}
		
		$result['relationship_list'] = $returnRelationshipList;
		return $result;
	}
}

SugarWebServiceImplv4::$helperObject = new SugarWebServiceUtilv4();
