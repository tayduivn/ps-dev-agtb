<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/**
 * Subpanel Layout definition for Contacts
 *
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
 * by SugarCRM are Copyright (C) 2004-2007 SugarCRM, Inc.; All Rights Reserved.
 */


$subpanel_layout = array(
	'top_buttons' => array(
		array('widget_class' => 'SubPanelTopCreateButton'),
		array('widget_class' => 'SubPanelTopSelectButton', 'popup_module' => 'LeadContacts'),
	),

	'where' => '',
	
	

	'list_fields' => array(
		'first_name'=>array(
			'name'=>'first_name',
			'usage' => 'query_only',
		),
		'last_name'=>array(
			'name'=>'last_name',
		 	'usage' => 'query_only',
		),
		'name'=>array(
			'name'=>'name',		
			'vname' => 'LBL_LIST_NAME',
			'widget_class' => 'SubPanelDetailViewLink',
		 	'module' => 'LeadContacts',
            'sort_by' => 'last_name',
            'sort_order' => 'asc',
			'width' => '43%',
		),
		'title'=>array(
			'name'=>'title',		
			'vname' => 'LBL_LIST_TITLE',
			'width' => '20%',
		),
		'campaign_id'=>array(
			'name'=>'campaign_id',
			'usage' => 'query_only',
		),
		'campaign_name'=>array (
			'name'=>'campaign_name',		
			'vname' => 'LBL_LIST_CAMPAIGN_NAME',
			'widget_class' => 'SubPanelDetailViewLink',
			'target_record_key' => 'campaign_id',
			'target_module' => 'Campaigns',			
		 	'module' => 'Campagins',
			'width' => '15%',
		),
		'email1'=>array(
			'name'=>'email1',		
			'vname' => 'LBL_LIST_EMAIL',
			'widget_class' => 'SubPanelEmailLink',
			'width' => '30%',
			'sortable' => false,
		),
		'primary_address_country'=>array(
			'name'=>'primary_address_country',		
			'vname' => 'LBL_COUNTRY',
			'width' => '10%',
		),
	),
);		
