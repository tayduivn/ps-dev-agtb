<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/**
 * Layout definition for Accounts
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
 * by SugarCRM are Copyright (C) 2004-2008 SugarCRM, Inc.; All Rights Reserved.
 */


$layout_defs['LeadAccounts'] = array(
	// list of what Subpanels to show in the DetailView 
	'subpanel_setup' => array(

		'activities' => array(
			'order' => 10,
			'sort_order' => 'desc',
			'sort_by' => 'date_start',
			'title_key' => 'LBL_ACTIVITIES_SUBPANEL_TITLE',
			'type' => 'collection',
			'subpanel_name' => 'activities',   //this values is not associated with a physical file.

			'header_definition_from_subpanel'=> 'meetings',

			'module'=>'Activities',
			
			'top_buttons' => array(
				array('widget_class' => 'SubPanelTopCreateTaskButton'),

				array('widget_class' => 'SubPanelTopScheduleMeetingButton'),
				array('widget_class' => 'SubPanelTopScheduleCallButton'),

				array('widget_class' => 'SubPanelTopComposeEmailButton'),
			),	
					
			'collection_list' => array(	
				'tasks' => array(
					'module' => 'Tasks',
					'subpanel_name' => 'ForActivities',
					'get_subpanel_data' => 'tasks',
				),

                'meetings' => array(
                    'module' => 'Meetings',
                    'subpanel_name' => 'ForActivities',
                    'get_subpanel_data' => 'meetings',
                ),
				'calls' => array(
					'module' => 'Calls',
					'subpanel_name' => 'ForActivities',
					'get_subpanel_data' => 'calls',
				),

			)			
		),
		'history' => array(
			'order' => 20,
			'sort_order' => 'desc',
			'sort_by' => 'date_modified',
			'title_key' => 'LBL_HISTORY_SUBPANEL_TITLE',
			'type' => 'collection',
			'subpanel_name' => 'history',   //this values is not associated with a physical file.

			'header_definition_from_subpanel'=> 'meetings',

			'module'=>'History',
			
			'top_buttons' => array(
				array('widget_class' => 'SubPanelTopCreateNoteButton'),
				array('widget_class' => 'SubPanelTopArchiveEmailButton'),
            	array('widget_class' => 'SubPanelTopSummaryButton'),
			),	
					
			'collection_list' => array(	
				'tasks' => array(
					'module' => 'Tasks',
					'subpanel_name' => 'ForHistory',
					'get_subpanel_data' => 'tasks',
				),

                'meetings' => array(
                    'module' => 'Meetings',
                    'subpanel_name' => 'ForHistory',
                    'get_subpanel_data' => 'meetings',
                ),
				'calls' => array(
					'module' => 'Calls',
					'subpanel_name' => 'ForHistory',
					'get_subpanel_data' => 'calls',
				),

				'notes' => array(
					'module' => 'Notes',
					'subpanel_name' => 'ForHistory',
					'get_subpanel_data' => 'notes',
				),	
				'emails' => array(
					'module' => 'Emails',
					'subpanel_name' => 'ForHistory',
					'get_subpanel_data' => 'emails',
				),	
				'linkedemails' => array(
	                'module' => 'Emails',
	                'subpanel_name' => 'ForUnlinkedEmailHistory',
	                'get_subpanel_data' => 'function:get_unlinked_email_query',
	                'generate_select'=>true,
	                'function_parameters' => array('return_as_array'=>'true'),
	    		),          
			)			
		),
		'leadcontacts' => array(
			'order' => 30,
			'module' => 'LeadContacts',
			'sort_order' => 'asc',
			'sort_by' => 'last_name, first_name',
			'subpanel_name' => 'default',
			'get_subpanel_data' => 'leadcontacts',
			'title_key' => 'LBL_LEADCONTACTS_SUBPANEL_TITLE',
		),
        'leadaccounts' => array(
			'order' => 40,
			'module' => 'LeadAccounts',
			'sort_order' => 'asc',
			'sort_by' => 'name',
			'subpanel_name' => 'default',
			'get_subpanel_data' => 'leadaccounts',
			'title_key' => 'LBL_MEMBER_ORG_SUBPANEL_TITLE',
		),
        'interactions' => array(
			'order' => 50,
			'module' => 'Interactions',
			'sort_order' => 'desc',
			'sort_by' => 'start_date',
			'get_subpanel_data'=>'function:getInteractionsQuery',
			'generate_select' => true,
			'function_parameters' => array('return_as_array' => 'true'),
			'subpanel_name' => 'default',
			'title_key' => 'LBL_INTERACTIONS_SUBPANEL_TITLE',
		),
	),
);
?>
