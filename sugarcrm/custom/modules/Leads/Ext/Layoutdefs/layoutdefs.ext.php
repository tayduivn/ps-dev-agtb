<?php 
 //WARNING: The contents of this file are auto-generated




$layout_defs['Leads']['subpanel_setup']['children'] = 
 array(
			'order' => 1,
			'module' => 'Leads',
			'subpanel_name' => 'ForLeads',
			'get_subpanel_data' => 'members',
			'add_subpanel_data' => 'parent_lead_id',
			'title_key' => 'LBL_CHILDREN_LEADS_SUBPANEL_TITLE',
			'top_buttons' => array(
				//array('widget_class' => 'SubPanelTopCreateButton'),
				//array('widget_class' => 'SubPanelTopSelectButton', 'mode'=>'MultiSelect')
			),
			);
			
$layout_defs['Leads']['subpanel_setup']['related_leads'] = 
 array(
 			'top_buttons' => array(
				array('widget_class' => 'SubPanelTopSelectButtonLeads', 'popup_module' => 'Leads', 'mode'=>'MultiSelect'),
				),
			'order' => 2,
			'module' => 'Leads',
			'subpanel_name' => 'ForLeadsRelated',
			'get_subpanel_data' => 'related_leads',
			'add_subpanel_data' => 'lead_id',
			'title_key' => 'LBL_RELATED_LEADS_SUBPANEL_TITLE',
			'get_distinct_data'=> true,
			);			
			


$layout_defs['Leads']['subpanel_setup']['activities_child'] = 
 array(
			'order' => 20,
			'sort_order' => 'desc',
			'sort_by' => 'date_start',
			'title_key' => 'LBL_ACTIVITIES_SUBPANEL_TITLE',
			'type' => 'collection',
			'subpanel_name' => 'activities',   //this values is not associated with a physical file.
			'module'=>'Activities',
			
			'top_buttons' => array(
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
				'calls' => array(
					'module' => 'Calls',
					'subpanel_name' => 'ForActivities',
					'get_subpanel_data' => 'calls',
				),	
			)

	
	
			);
			
$layout_defs['Leads']['subpanel_setup']['history_child'] = 
 array(
	'order' => 30,
			'sort_order' => 'desc',
			'sort_by' => 'date_modified',
			'title_key' => 'LBL_HISTORY_SUBPANEL_TITLE',
			'type' => 'collection',
			'subpanel_name' => 'history',   //this values is not associated with a physical file.
			'module'=>'History',

			'top_buttons' => array(
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
			)		

	
	
			);			


if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

$layout_defs['Leads']['subpanel_setup']['history']['collection_list']['calls']['override_subpanel_name'] = "ForHistory";
?>