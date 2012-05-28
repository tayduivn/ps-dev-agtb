<?php
//FILE SUGARCRM flav=ent ONLY
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/**
 * Subpanel Layout definition for Contacts
 *
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-professional-eula.html
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
 * by SugarCRM are Copyright (C) 2004-2005 SugarCRM, Inc.; All Rights Reserved.
 */

// $Id: ForAccounts.php 13782 2006-06-06 17:58:55Z majed $
$subpanel_layout = array(
	'top_buttons' => array(
		array('widget_class' => 'SubPanelTopCreateButton'),
	),

	'list_fields' => array(
        'name'=>array(
            'vname' => 'LBL_LIST_OPPORTUNITY_LINE_NAME',
            'widget_class' => 'SubPanelDetailViewLink',
            'width' => '40%',
        ),
		'product_name'=>array(
            'vname' => 'LBL_PRODUCT_NAME',
         	'widget_class' => 'SubPanelDetailViewLink',
         	'module' => 'Products',
         	'target_record_key' => 'product_id',
         	'target_module' => 'Products',
            'sortable' => false
		),
        'best_case'=>array(
            'vname' => 'LBL_BEST_CASE',
            'name'=>'best_case',
        ),
        'likely_case'=>array(
            'vname' => 'LBL_LIKELY_CASE',
            'name'=>'likely_case',
        ),
        'worst_case'=>array(
            'vname' => 'LBL_WORST_CASE',
            'name' => 'worst_case',
        ),
		'edit_button'=>array(
			'vname' => 'LBL_EDIT_BUTTON',
			'widget_class' => 'SubPanelEditButton',
		 	'module' => 'OpportunityLines',
			'width' => '5%',
		),
		'remove_button'=>array(
			'vname' => 'LBL_REMOVE',
			'widget_class' => 'SubPanelRemoveButton',
		 	'module' => 'OpportunityLines',
			'width' => '5%',
		),
	),
);