<?php
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
 *Portions created by SugarCRM are Copyright (C) 2004-2007 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

/**
 * Create merge query depending on the modules being merged
 * @param SugarBean $seed Object being queried
 * @param string $merge_module Module being merged
 * @param string $key ID of the record in module being merged
 */
function get_merge_query($seed, $merge_module, $key)
{
    $selQuery = array (
'Contacts'=>array(
	'Accounts' => 'SELECT contacts.first_name, contacts.last_name, contacts.id, contacts.date_entered FROM contacts LEFT JOIN accounts_contacts ON contacts.id=accounts_contacts.contact_id AND (accounts_contacts.deleted is NULL or accounts_contacts.deleted=0)',
	'Opportunities' => 'SELECT contacts.first_name, contacts.last_name, contacts.id, contacts.date_entered FROM contacts LEFT JOIN opportunities_contacts ON contacts.id=opportunities_contacts.contact_id AND (opportunities_contacts.deleted is NULL or opportunities_contacts.deleted=0)',
	'Cases' => 'SELECT contacts.first_name, contacts.last_name, contacts.id, contacts.date_entered FROM contacts LEFT JOIN contacts_cases ON contacts.id=contacts_cases.contact_id AND (contacts_cases.deleted is NULL or contacts_cases.deleted=0)',
    //BEGIN SUGARCRM flav!=sales ONLY
	'Bugs' => 'SELECT contacts.first_name, contacts.last_name, contacts.id, contacts.date_entered FROM contacts LEFT JOIN contacts_bugs ON contacts.id=contacts_bugs.contact_id AND (contacts_bugs.deleted is NULL or contacts_bugs.deleted=0)',
    //END SUGARCRM flav!=sales ONLY
	'Quotes' => 'SELECT contacts.first_name, contacts.last_name, contacts.id, contacts.date_entered FROM contacts LEFT JOIN quotes_contacts ON contacts.id=quotes_contacts.contact_id AND (quotes_contacts.deleted is NULL or quotes_contacts.deleted=0)'
),
'Opportunities'=>array(
	"Accounts"=>'SELECT opportunities.id, opportunities.name FROM opportunities LEFT JOIN accounts_opportunities ON opportunities.id = accounts_opportunities.opportunity_id AND (accounts_opportunities.deleted is NULL or accounts_opportunities.deleted=0)'
),
'Accounts'=>array(
	"Opportunities"=>'SELECT accounts.id, accounts.name FROM accounts LEFT JOIN accounts_opportunities ON accounts.id = accounts_opportunities.account_id AND (accounts_opportunities.deleted is NULL or accounts_opportunities.deleted=0)'
),
);

    $whereQuery = array(
'Contacts' =>
    array('Accounts' => 'accounts_contacts.contact_id = contacts.id AND accounts_contacts.account_id = ',
	'Opportunities' => 'opportunities_contacts.contact_id = contacts.id AND opportunities_contacts.opportunity_id = ',
	'Cases' => 'contacts_cases.contact_id = contacts.id AND contacts_cases.case_id = ',
//BEGIN SUGARCRM flav!=sales ONLY
	'Bugs' => 'contacts_bugs.contact_id = contacts.id AND contacts_bugs.bug_id = ',
//END SUGARCRM flav!=sales ONLY
	'Quotes' => 'quotes_contacts.contact_id = contacts.id AND quotes_contacts.quote_id = '
    ),
'Opportunities'=>array('Accounts'=>'accounts_opportunities.opportunity_id = opportunities.id AND accounts_opportunities.account_id = '),
'Accounts'=>array('Opportunities'=>'accounts_opportunities.account_id = accounts.id  AND accounts_opportunities.opportunity_id = '),
);

    $relModule = $seed->module_dir;

	$select = "";
    if(!empty($selQuery[$relModule][$merge_module])){
        $select = $selQuery[$relModule][$merge_module];
	} else {
	    $lowerRelModule = strtolower($relModule);
	    if($seed->load_relationship($lowerRelModule)) {
    		$params = array('join_table_alias' => 'r1', 'join_table_link_alias' => 'r2', 'join_type' => 'LEFT JOIN');
	    	$join = $seed->$lowerRelModule->getJoin($params);
		    $select = "SELECT {$seed->table_name}.* FROM {$seed->table_name} $join";
	    }
	}

	if(empty($select)) {
	    $select = "SELECT contacts.first_name, contacts.last_name, contacts.id, contacts.date_entered FROM contacts";
	}

	if(empty($whereQuery[$relModule][$merge_module])){
		$select .= " WHERE {$seed->table_name}.id = '{$seed->db->quote($key)}'";
	}else{
		$select .= " WHERE ". $whereQuery[$relModule][$merge_module] . "'{$seed->db->quote($key)}'";
	}
    $select .=  " ORDER BY {$seed->table_name}.date_entered";
    return $select;
}
