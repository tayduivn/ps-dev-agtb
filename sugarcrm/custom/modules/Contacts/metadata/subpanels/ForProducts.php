<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/**
 * Layout definition for Contacts
 *
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 */



$subpanel_layout = array(
			'buttons' => array(
                array('widget_class' => 'SubPanelTopCreateButton'),
				array('widget_class' => 'SubPanelTopSelectButton', 'popup_module' => 'Products'),
			),
	
	
            'list_fields' => array(
				'status' => array(
		 		 		'name' => 'status',
		 		 		'vname' => 'LBL_LIST_STATUS',
						'width' => '8%',
					),
				'name' => array(
		 		 		'name' => 'name',
		 		 		'vname' => 'LBL_LIST_NAME',
						'widget_class' => 'SubPanelDetailViewLink',
						'width' => '28%',
					),
				'account_name' => array(
		 		 		'name' => 'account_name',
		 		 		'vname' => 'LBL_LIST_ACCOUNT_NAME',
						'widget_class' => 'SubPanelDetailViewLink',
		 		 		'module' => 'Accounts',
						'width' => '15%',
						'sortable'=>false,
					),
				'contact_name' => array(
		 		 		'name' => 'contact_name',
		 		 		'vname' => 'LBL_LIST_CONTACT_NAME',
						'widget_class' => 'SubPanelDetailViewLink',
		 		 		'module' => 'Contacts',
						'width' => '15%',
					),
				'date_purchased' =>	array(
		 		 		'name' => 'date_purchased',
		 		 		'vname' => 'LBL_LIST_DATE_PURCHASED',
						'width' => '10%',
					),
				'discount_price' =>	array(
		 		 		'name' => 'discount_price',
		 		 		'vname' => 'LBL_LIST_DISCOUNT_PRICE',
						'width' => '10%',
					),
				'date_support_expires' => array(
		 		 		'name' => 'date_support_expires',
		 		 		'vname' => 'LBL_LIST_SUPPORT_EXPIRES',
						'width' => '10%',
					),
				'nothing' => array(
			 		 	'name' => 'nothing',
						'widget_class' => 'SubPanelEditButton',
			 		 	'module' => 'Products',
		 		 		'width' => '4%',
					),
				),
);
?>
