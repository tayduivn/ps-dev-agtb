<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/**
 * Layout definition for Contacts
 *
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
 * by SugarCRM are Copyright (C) 2005 SugarCRM, Inc.; All Rights Reserved.
 */

// $Id: ForActivities.php 13782 2006-06-06 17:58:55 +0000 (Tue, 06 Jun 2006) majed $

$subpanel_layout = array(
			'buttons' => array(
                //array('widget_class' => 'SubPanelTopCreateButton'),
				array('widget_class' => 'SubPanelTopSelectButton'),
			    ),
            'where' => "(meetings.status='Planned')",
	        
            
			'list_fields' => array(
					array(
			 		 	'name' => 'nothing',
						'widget_class' => 'SubPanelIcon',
			 		 	'module' => 'Meetings',
			 		 	'width' => '2%',
					),
					array(
			 		 	'name' => 'nothing',
						'widget_class' => 'SubPanelCloseButton',
			 		 	'module' => 'Meetings',
			 		 	'vname' => 'LBL_LIST_CLOSE',
			 		 	'width' => '6%',
					),
					array(
			 		 	'name' => 'name',
			 		 	'vname' => 'LBL_LIST_SUBJECT',
						'widget_class' => 'SubPanelDetailViewLink',
			 		 	'width' => '30%',
					),
					array(
			 		 	'name' => 'status',
			 		 	'vname' => 'LBL_LIST_STATUS',
			 		 	'width' => '15%',
					),
					array(
			 		 	'name' => 'contact_name',
			 		 	'module' => 'Contacts',
						'widget_class' => 'SubPanelDetailViewLink',
			 		 	'vname' => 'LBL_LIST_CONTACT',
			 		 	'width' => '11%',
					),
					array(
			 		 	'name' => 'parent_name',
			 		 	'module' => 'Meetings',
			 		 	'vname' => 'LBL_LIST_RELATED_TO',
			 		 	'width' => '22%',
					),
					array(
			 		 	'name' => 'date_start',
			 		 	//'db_alias_to' => 'the_date',
			 		 	'vname' => 'LBL_LIST_DUE_DATE',
			 		 	'width' => '10%',
					),
					array(
			 		 	'name' => 'nothing',
						'widget_class' => 'SubPanelEditButton',
			 		 	'module' => 'Meetings',
			 		 	'width' => '2%',
					),
					array(
			 		 	'name' => 'nothing',
						'widget_class' => 'SubPanelRemoveButton',
			 		 	'module' => 'Meetings',
			 		 	'width' => '2%',
					),
				),
			);
?>