<?php 

$layout_defs['Emails']['subpanel_setup']['projecttask'] =  array(
	'order' => 90,
	'module' => 'ProjectTask',
	'sort_order' => 'desc',
	'sort_by' => 'name',
	'subpanel_name' => 'ForEmails',
    'refresh_page' => 1,	
	'get_subpanel_data' => 'projecttask',
	'add_subpanel_data' => 'project_task_id',
	'title_key' => 'LBL_PROJECT_TASK_SUBPANEL_TITLE',
	'top_buttons' => array(
		array('widget_class' => 'SubPanelTopSelectButton', 'mode'=>'MultiSelect')
	),
); 

?>