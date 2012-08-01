<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
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
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

$viewdefs ['Prospects']['base']['view']['detail'] = array(
	'buttons' => array(
		array(
			'name'    => 'edit_button',
			'type'    => 'button',
			'label'   => 'Edit',
			'primary' => true,
			'events'  => array(
				'click' => 'function(e){this.app.navigate(this.context, this.model, "edit", {trigger:true} );}'
			),
		),
	),
	'panels'  => array(
		array(
			'fields' => array(
				'name',
				'title',
				'department',
				'account_name',
				'primary_address_street',
				'primary_address_city',
				'primary_address_state',
				'primary_address_postalcode',
				'primary_address_country',
				'email',
				'phone_work',
				'phone_mobile',
				'phone_fax',
				'alt_address_street',
				'alt_address_city',
				'alt_address_state',
				'alt_address_postalcode',
				'alt_address_country',
				'description',
				'email_opt_out',
				'do_not_call',
				'assigned_user_name',
				'created_by_name',
				'date_entered',
				'modified_by_name',
				'date_modified',
				//BEGIN SUGARCRM flav=pro ONLY
				'team_name',
				//END SUGARCRM flav=pro ONLY
			),
		),
	),
);
