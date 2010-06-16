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
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 *$Id: popupdefs.php 23384 2007-06-06 20:54:55Z roger $
 ********************************************************************************/

$popupMeta = array('moduleMain' => 'CustomQuery',
						'varName' => 'CUSTOMQUERY',
						'className' => 'CustomQuery',
						'orderBy' => 'name',
						'whereClauses' => 
							array('name' => 'custom_queries.name', 
								  'query_name' => 'custom_queries.query_type',
								  'description' => 'custom_queries.description'),
						'listviewdefs' => array(
											'NAME' => array(
												'width' => '20', 
												'label' => 'LBL_LIST_NAME',
												'link' => true,
										        'default' => true), 
											'QUERY_TYPE' => array(
												'width' => '20', 
												'label' => 'LBL_QUERY_TYPE', 
												'link' => false,
										        'default' => true), 
											'DESCRIPTION' => array(
												'width' => '20', 
												'label' => 'LBL_LIST_DESCRIPTION', 
												'link' => false,
										        'default' => true),
											),
						'searchdefs'   => array(
										 	'name', 
											'query_type', 
											'description',
										  )
						);
?>
 