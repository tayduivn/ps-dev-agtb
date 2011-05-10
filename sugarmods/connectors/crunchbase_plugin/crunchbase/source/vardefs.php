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
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.as
 ********************************************************************************/
$dictionary['ext_rest_crunchbase'] = array(

  'comment' => 'vardefs for crunchbase datasource',
  'fields' => array (
    'id'=> array(
	    'name' => 'id',
	    'vname' => 'LBL_ID',
	    'type' => 'varchar',
	    'comment' => 'The id of the company',
	    'hidden' => true,
    ),
   'name'=> array(
	    'name' => 'name',
	    'vname' => 'LBL_NAME',
	    'type' => 'varchar',
	    'comment' => 'The name of the company',
	    'hover' => true,
	    'search' => true,
    ),
   'overview' => array (
	    'name' => 'overview',
	    'vname' => 'LBL_DESCRIPTION',
	    'type' => 'varchar',
	    'comment' => 'Company description'
    ),
   'crunchbase_url' => array (
	    'name' => 'crunchbase_url',
	    'vname' => 'LBL_CRUNCHBASE_URL',
	    'type' => 'varchar',
	    'comment' => 'The url of the crunchbase website for company'   
   ),
   'homepage_url' => array (
        'name' => 'homepage_url',
        'vname' => 'LBL_HOMEPAGE_URL',
        'type' => 'varchar',
        'comment' => 'The url of the company\'s website as recorded in crunchbase',
   ),     
   'blog_url' => array (
        'name' => 'blog_url',
        'vname' => 'LBL_BLOG_URL',
        'type' => 'varchar',
        'comment' => 'The blog url of the company\'s website as recorded in crunchbase',   
   ),
   'blog_feed_url' => array (
        'name' => 'blog_feed_url',
        'output' => 'blog_feed_url',
        'vname' => 'LBL_BLOG_FEED_URL',
        'type' => 'varchar',
        'comment' => 'The blog feed url of the company\'s website as recorded in crunchbase',   
   ),
   'category_code' => array (
        'name' => 'category_code',
        'vname' => 'LBL_CATEGORY_CODE',
        'type' => 'varchar',
        'comment' => 'The category of the company as recorded in crunchbase',   
   ),      
   'number_of_employees' => array (
        'name' => 'number_of_employees',
        'vname' => 'LBL_NUMBER_OF_EMPLOYEES',
        'type' => 'integer',
        'comment' => 'The approximate number of employees as recorded in crunchbase',   
   ), 
   'founded_year' => array (
        'name' => 'founded_year',
        'vname' => 'LBL_FOUNDED_YEAR',
        'type' => 'integer',
        'comment' => 'The year the company was founded as recorded in crunchbase',   
   ), 
   'tag_list' => array (
        'name' => 'tag_list',
        'vname' => 'LBL_TAG_LIST',
        'type' => 'varchar',
        'comment' => 'A list of tags for the company as recorded in crunchbase',   
   ),    
   'email_address' => array(
        'name' => 'email_address',
        'vname' => 'LBL_EMAIL',
        'type' => 'varchar',
        'comment' => 'The company\s email address as recorded in crunchbase',    
   ),       
   'phone_number' => array (
        'name' => 'phone_number',
        'vname' => 'LBL_PHONE_NUMBER',
        'type' => 'varchar',
        'comment' => 'The company\s phone number as recorded in crunchbase',   
   ),   
  )
);

?>