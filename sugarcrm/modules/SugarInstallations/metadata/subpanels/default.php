<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/**
 * Subpanel Layout definition for Sugar Installations
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

// $Id$
$subpanel_layout = array(
	'top_buttons' => array(
	),

	'where' => '',

	'list_fields' => array(
		'license_key' => array(
			'vname' => 'LBL_LIST_LICENSE_KEY',
			'width' => '20%',
			'widget_class' => 'SubPanelDetailViewLink',
		),
		'latest_tracker_id' => array(
			'vname' => 'LBL_LIST_LATEST_TRACKER_ID',
			'width' => '10%',
		),
		'last_touch' => array(
			'vname' => 'LBL_LIST_LAST_TOUCH',
			'width' => '10%',
		),
		/*
		'date_created' => array(
			'vname' => 'LBL_LIST_FIRST_UPDATE',
			'width' => '10%',
		),
		*/
		'sugar_flavor'=>array(
	 		'vname' => 'LBL_LIST_SUGAR_FLAVOR',
			'width' => '15%',
		),
		'sugar_version'=>array(
	 		'vname' => 'LBL_LIST_SUGAR_VERSION',
			'width' => '15%',
		),
		'status' => array(
			'vname' => 'LBL_STATUS',
			'width' => '20%',
		),
		'users'=>array(
	 		'vname' => 'LBL_USERS',
		 	'width' => '20%',
		),
	),
);

?>
