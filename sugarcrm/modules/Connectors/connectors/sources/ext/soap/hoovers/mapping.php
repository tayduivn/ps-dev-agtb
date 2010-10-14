<?php
//FILE SUGARCRM flav=pro || flav=sales ONLY
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
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.  
 ********************************************************************************/
$mapping = array (
    'beans' => array (
      //BEGIN SUGARCRM flav!=sales ONLY
      'Leads' => array (
            'id' => 'id',
		  	'companyname' => 'account_name',
            'address1' => 'primary_address_street', 
            'address2' => 'primary_address_street_2',
		    'stateorprovince' => 'primary_address_state',
		    'country' => 'primary_address_country',
		    'city' => 'primary_address_city',
		    'addrzip' => 'primary_address_postalcode',
		    'hqphone' => 'phone_work',	    
      ),
      //END SUGARCRM flav!=sales ONLY
      'Accounts' => array (
            'id' => 'id',
		  	'companyname' => 'name',
            'address1' => 'billing_address_street', 
            'address2' => 'billing_address_street_2',      
		    'city' => 'billing_address_city',
		    'stateorprovince' => 'billing_address_state',
		    'country' => 'billing_address_country',
		    'city' => 'billing_address_city',
		    'addrzip' => 'billing_address_postalcode',
            'sales' => 'annual_revenue',
            'employees' => 'employees',
            'hqphone' => 'phone_office',
      		'description' => 'description',
      ),
      'Contacts' => array(
            'id' => 'id',
            'companyname' => 'company_name',
            'address1' => 'primary_address_street', 
            'address2' => 'primary_address_street_2',      
		    'city' => 'primary_address_city',
		    'stateorprovince' => 'primary_address_state',
		    'country' => 'primary_address_country',
		    'city' => 'primary_address_city',
		    'addrzip' => 'primary_address_postalcode',
            'hqphone' => 'phone_work',
      ),
    ),
);
?>
