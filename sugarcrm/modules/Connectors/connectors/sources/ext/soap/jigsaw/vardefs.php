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
$dictionary['ext_soap_jigsaw'] = array(
  'comment' => 'vardefs for jigsaw connector',
  'fields' => array (
   'id' =>
   array (
	    'name' => 'id',
	    'input' => 'companyId',	    
	    'vname' => 'LBL_ID',
	    'type' => 'id',
	    'hidden' => true,
	    'comment' => 'Unique identifier for jigsaw records'
   ),
   'name'=> array(
	    'name' => 'name',
	    'vname' => 'LBL_COMPANY_NAME',
	    'type' => 'varchar',
	    'search' => true,
	    'comment' => 'The name of the company',
   ),
   'address' => array(
        'name' => 'address',
        'vname' => 'LBL_ADDRESS',
        'type' => 'varchar',
        'comment' => 'The address',
   ),    
   'city' => array (
	    'name' => 'city',
	    'vname' => 'LBL_CITY',
	    'type' => 'varchar',
	    'comment' => 'The city address for the company', 
   ),     
   'state' => array(
        'name' => 'state',
        'vname' => 'LBL_STATE',
        'type' => 'varchar',
        'comment' => 'The state address for the company',
   ),
   'zip' => array(
        'name' => 'zip',
        'vname' => 'LBL_ZIP',
        'type' => 'varchar',
        'comment' => 'The postal code address for the company',   
   ),
   'country' => array(
        'name' => 'country',
        'vname' => 'LBL_COUNTRY',
        'type' => 'varchar',
        'comment' => 'The country address for the company',
   ),
   'phone' => array(
        'name' => 'phone',
        'vname' => 'LBL_PHONE',
        'type' => 'varchar',
        'comment' => 'The phone number',
   ),   
   'sicCode' => array(
        'name' => 'sicCode',
        'vname' => 'LBL_SIC_CODE',
        'type' => 'varchar',
        'comment' => 'The sic code',
   ),  
   'revenue' => array(
        'name' => 'revenue',
        'vname' => 'LBL_REVENUE',
        'type' => 'double',
        'comment' => 'Annual revenue estimate',
   ),  
   'revenueRange' => array(
        'name' => 'revenueRange',
        'vname' => 'LBL_REVENUE_RANGE',
        'type' => 'varchar',
        'comment' => 'Annual revenue range estimate',
   ), 
   'ownership' => array(
        'name' => 'ownership',
        'vname' => 'LBL_OWNERSHIP',
        'type' => 'varchar',
        'comment' => 'public, private, etc.',
   ), 
   'website' => array(
        'name' => 'website',
        'vname' => 'LBL_WEBSITE',
        'type' => 'varchar',
        'search' => true,
        'comment' => 'company website',
   ), 
   'linkedInJigsaw' => array(
        'name' => 'linkedInJigsaw',
        'vname' => 'LBL_LINKED_IN_JIGSAW',
        'type' => 'varchar',
        'comment' => 'jigsaw website',
   ), 
   'industry1' => array(
        'name' => 'industry1',
        'vname' => 'LBL_INDUSTRY1',
        'type' => 'varchar',
        'comment' => 'primary industry',
   ),
   'stockSymbol' => array(
        'name' => 'stockSymbol',
        'vname' => 'LBL_STOCK_SYMBOL',
        'type' => 'varchar',
        'comment' => 'stock symbol',
   ),   
   'stockExchange' => array(
        'name' => 'stockExchange',
        'vname' => 'LBL_STOCK_EXCHANGE',
        'type' => 'varchar',
        'comment' => 'stock exchange',
   ),
   'createdOn' => array(
        'name' => 'createdOn',
        'vname' => 'LBL_CREATED_ON',
        'type' => 'varchar',
        'comment' => 'date profile was created',
   ),      
   'employeeCount' => array(
        'name' => 'employeeCount',
        'vname' => 'LBL_EMPLOYEE_COUNT',
        'type' => 'integer',
        'comment' => 'employee headcount estimate',
   ), 
   'employeeRange' => array(
        'name' => 'employeeRange',
        'vname' => 'LBL_EMPLOYEE_RANGE',
        'type' => 'varchar',
        'comment' => 'employee headcount range estimate',
   ), 
  )
);
?>