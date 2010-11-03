<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/**
 * Layout definition for ITRequests
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

 */



$layout_defs['ITRequests'] = array(
	// list of what Subpanels to show in the DetailView
	'subpanel_setup' => array(
		'users' => array(
			'order' => 10,
			'module' => 'Users',
			'sort_order' => 'asc',
			'sort_by' => 'last_name, first_name',
			'subpanel_name' => 'default',
			'get_subpanel_data' => 'users',
			'add_subpanel_data' => 'id',
			'title_key' => 'LBL_USERS_SUBPANEL_TITLE',
			'top_buttons' => array(
				array(
					'widget_class' => 'SubPanelTopSelectButton',
					'popup_module' => 'ITRequests',
					'mode' => 'MultiSelect',
				),
			),
		),
		'activities' => array(
			'order' => 20,
			'sort_order' => 'desc',
			'sort_by' => 'date_start',
			'title_key' => 'LBL_ACTIVITIES_SUBPANEL_TITLE',
			'type' => 'collection',
			'subpanel_name' => 'activities',   //this values is not associated with a physical file.
			'module'=>'Activities',

			'top_buttons' => array(
				array('widget_class' => 'SubPanelTopCreateTaskButton'),
				array('widget_class' => 'SubPanelTopScheduleMeetingButton'),
			),

			'collection_list' => array(	
				'meetings' => array(
					'module' => 'Meetings',
					'subpanel_name' => 'ForActivities',
					'get_subpanel_data' => 'meetings',
				),
				'tasks' => array(
					'module' => 'Tasks',
					'subpanel_name' => 'ForActivities',
					'get_subpanel_data' => 'tasks',
				),
			)			
		),
		'history' => array(
			'order' => 30,
			'sort_order' => 'desc',
			'sort_by' => 'date_modified',
			'title_key' => 'LBL_HISTORY_SUBPANEL_TITLE',
			'type' => 'collection',
			'subpanel_name' => 'history',   //this values is not associated with a physical file.
			'module'=>'History',

			'top_buttons' => array(
				array('widget_class' => 'SubPanelTopCreateNoteButton'),
            			array('widget_class' => 'SubPanelTopSummaryButton'),
			),

			'collection_list' => array(	
				'meetings' => array(
					'module' => 'Meetings',
					'subpanel_name' => 'ForHistory',
					'get_subpanel_data' => 'meetings',
				),
				'tasks' => array(
					'module' => 'Tasks',
					'subpanel_name' => 'ForHistory',
					'get_subpanel_data' => 'tasks',
				),
				'notes' => array(
					'module' => 'Notes',
					'subpanel_name' => 'ForHistory',
					'get_subpanel_data' => 'notes',
				),	
			)			
		),
		'bugs' => array(
			'order' => 40,
			'module' => 'Bugs',
			'sort_order' => 'desc',
			'sort_by' => 'bug_number',
			'subpanel_name' => 'default',
			'get_subpanel_data' => 'bugs',
			'add_subpanel_data' => 'bug_id',
			'title_key' => 'LBL_BUGS_SUBPANEL_TITLE',
            'top_buttons' => array(
                array('widget_class' => 'SubPanelTopButtonQuickCreate'),
                array('widget_class' => 'SubPanelTopSelectButton'),
            ),			
		),
		'cases' => array(
			'order' => 50,
			'module' => 'Cases',
			'sort_order' => 'desc',
			'sort_by' => 'case_number',
			'subpanel_name' => 'default',
			'get_subpanel_data' => 'cases',
			'add_subpanel_data' => 'case_id',
			'title_key' => 'LBL_CASES_SUBPANEL_TITLE',
            'top_buttons' => array(
                //array('widget_class' => 'SubPanelTopButtonQuickCreate'),
                array('widget_class' => 'SubPanelTopSelectButton'),
            ),			
		),
		'accounts' => array(
			'order' => 60,
			'module' => 'Accounts',
			'sort_order' => 'asc',
			'sort_by' => 'name',
			'subpanel_name' => 'default',
			'get_subpanel_data' => 'accounts',
			'add_subpanel_data' => 'account_id',
			'title_key' => 'LBL_ACCOUNTS_SUBPANEL_TITLE',
            'top_buttons' => array(
                //array('widget_class' => 'SubPanelTopButtonQuickCreate'),
                array('widget_class' => 'SubPanelTopSelectButton'),
            ),			
		),
		'related_itrequests' => array(
			'order' => 70,
			'module' => 'ITRequests',
			'sort_order' => 'asc',
			'sort_by' => 'itrequest_number',
			'subpanel_name' => 'default',
			'get_subpanel_data' => 'related_itrequests',
			'add_subpanel_data' => 'itrequest_two',
			'title_key' => 'LBL_RELATED_ITREQUESTS_SUBPANEL',
            'top_buttons' => array(
				array('widget_class' => 'SubPanelTopCreateButton'),
                array('widget_class' => 'SubPanelTopSelectButton'),
            ),			
		),
	),
);
?>
