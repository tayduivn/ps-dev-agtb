<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/**
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights Reserved.
 */

// $Id: listviewdefs.php 56123 2010-04-26 21:48:19Z asandberg $


$listViewDefs['Cases'] = array(
	'CASE_NUMBER' => array(
		'width' => '5', 
		'label' => 'LBL_LIST_NUMBER',
        'default' => true), 
	'NAME' => array(
		'width' => '25', 
		'label' => 'LBL_LIST_SUBJECT', 
		'link' => true,
        'default' => true), 
	'ACCOUNT_NAME' => array(
		'width' => '20', 
		'label' => 'LBL_LIST_ACCOUNT_NAME', 
		'module' => 'Accounts',
		'id' => 'ACCOUNT_ID',
		'link' => true,
        'default' => true,
        'ACLTag' => 'ACCOUNT',
        'related_fields' => array('account_id')),
	'PRIORITY' => array(
		'width' => '10', 
		'label' => 'LBL_LIST_PRIORITY',
        'default' => true),  
	'STATUS' => array(
		'width' => '10', 
		'label' => 'LBL_LIST_STATUS',
        'default' => true),
//BEGIN SUGARCRM flav=pro ONLY
	'TEAM_NAME' => array(
		'width' => '10', 
		'label' => 'LBL_LIST_TEAM',
        'default' => false),
//END SUGARCRM flav=pro ONLY
	'ASSIGNED_USER_NAME' => array(
		'width' => '10', 
		'label' => 'LBL_ASSIGNED_TO_NAME',
		'module' => 'Employees',
        'id' => 'ASSIGNED_USER_ID',
        'default' => true),
	'DATE_ENTERED' => array (
	    'width' => '10',
	    'label' => 'LBL_DATE_ENTERED',
	    'default' => true),
);
?>
