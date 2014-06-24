<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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

 // $Id: listviewdefs.php 54005 2010-01-25 20:32:59Z jmertic $

$listViewDefs['SavedSearch'] = array(
	'NAME' => array(
		'width' => '40%', 		
		'label' => 'LBL_LIST_NAME',
		'link' => true,
		'customCode' => '<a  href="index.php?action=index&module=SavedSearch&saved_search_select={$ID}">{$NAME}</a>'),
	'SEARCH_MODULE' => array(
		'width' => '35%', 
		'label' => 'LBL_LIST_MODULE'), 
//BEGIN SUGARCRM flav=pro ONLY
	'TEAM_NAME' => array(
		'width' => '15%', 
		'label' => 'LBL_LIST_TEAM',
		'default' => false),
//END SUGARCRM flav=pro ONLY
	'ASSIGNED_USER_NAME' => array(
		'width' => '10%', 
		'label' => 'LBL_LIST_ASSIGNED_USER')
);
?>
