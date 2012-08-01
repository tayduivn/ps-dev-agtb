<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

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

$viewdefs['Prospects']['base']['view']['list'] = array(
	'panels' => array(
		array(
			'fields' => array(
				array(
					'name'    => 'name',
					'label'   => 'LBL_LIST_NAME',
					'orderBy' => 'last_name',
				),
				array(
					'name'  => 'title',
					'label' => 'LBL_LIST_TITLE',
				),
				array(
					'name'     => 'email1',
					'label'    => 'LBL_LIST_EMAIL_ADDRESS',
					'sortable' => false,
                    'type'     => 'email',
				),
				array(
					'name'  => 'phone_work',
					'label' => 'LBL_LIST_PHONE',
				),
				array(
					'name'  => 'date_entered',
					'label' => 'LBL_DATE_ENTERED',
				),
			),
		),
	),
);
